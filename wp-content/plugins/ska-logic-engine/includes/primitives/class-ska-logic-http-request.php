<?php
defined( 'ABSPATH' ) || exit;

class Ska_Logic_Http_Request implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        $method = isset($config['method']) ? strtoupper($config['method']) : 'GET';
        $url    = isset($config['url']) ? $config['url'] : '';
        $body   = isset($config['body']) ? $config['body'] : '';
        
        // Helper function to evaluate SkaFX expressions in strings
        $evaluate_string = function( $str ) use ( $payload ) {
            if ( strpos( $str, '{{' ) !== false ) {
                return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($payload) {
                    $expression = trim($matches[1]);
                    try {
                        $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expression, $payload );
                        $val = $eval_result['last_val'] ?? '';
                        if ( is_array($val) || is_object($val) ) {
                            return wp_json_encode($val);
                        }
                        return $val;
                    } catch (Exception $e) {
                        return ''; // Fallback
                    }
                }, $str);
            }
            return $str;
        };

        $url = $evaluate_string($url);

        if ( empty($url) ) {
            $payload['http_response'] = ['error' => 'URL is empty or invalid'];
            return [
                'payload' => $payload,
                'port'    => 'error'
            ];
        }

        // Setup Request Args
        $args = array(
            'method'    => $method,
            'timeout'   => 15,
            'sslverify' => ( wp_get_environment_type() === 'local' || strpos( site_url(), '.local' ) !== false ) ? false : true,
            'headers'   => array(),
        );

        // Parse Headers if configured
        if ( isset($config['headers']) && is_array($config['headers']) ) {
            foreach ( $config['headers'] as $header ) {
                if ( !empty($header['key']) ) {
                    $key = sanitize_text_field($header['key']);
                    $val = isset($header['value']) ? $header['value'] : '';
                    $args['headers'][$key] = $evaluate_string($val);
                }
            }
        }

        // Default Content-Type if not set and body exists
        if ( !isset($args['headers']['Content-Type']) && !empty($body) ) {
             $args['headers']['Content-Type'] = 'application/json';
        }

        // Parse Body if method allows it
        if ( in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($body) ) {
            $args['body'] = $evaluate_string($body);
        }

        // Execute Request
        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            $payload['http_response'] = [
                'error'   => $response->get_error_message(),
                'code'    => 500
            ];
            return [
                'payload' => $payload,
                'port'    => 'error'
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body_res    = wp_remote_retrieve_body( $response );
        
        // Try to decode JSON response
        $decoded = json_decode($body_res, true);
        if ( json_last_error() === JSON_ERROR_NONE ) {
            $body_res = $decoded;
        }

        $payload['http_response'] = [
            'code' => $status_code,
            'body' => $body_res
        ];

        // Branching logic: 2xx responses are success
        $port = ($status_code >= 200 && $status_code < 300) ? 'main' : 'error';

        return [
            'payload' => $payload,
            'port'    => $port
        ];
    }
}
