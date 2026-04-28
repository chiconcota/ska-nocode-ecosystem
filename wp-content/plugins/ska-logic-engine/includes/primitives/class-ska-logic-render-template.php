<?php
defined( 'ABSPATH' ) || exit;

/**
 * Node: Render Template
 * Extract HTML from ska_data_sys_organisms and inject data via SkaFX.
 */
class Ska_Logic_Render_Template implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        $source_type = isset( $config['source_type'] ) ? $config['source_type'] : 'system';
        $html_content = '';

        if ( $source_type === 'raw' ) {
            // Lấy template trực tiếp từ cấu hình (thường là một biến như {{payload.db_template}})
            $raw_template = isset( $config['raw_template'] ) ? $config['raw_template'] : '';
            // Nội suy biến để lấy ra đoạn HTML thô thực sự nếu nó được truyền vào dạng biến
            $html_content = $this->evaluate_template( $raw_template, $payload );
        } else {
            // Chế độ cũ: Lấy từ system_organisms
            $organism_id = isset( $config['organism_id'] ) ? $config['organism_id'] : '';
            $organism_id = $this->evaluate_template( $organism_id, $payload );

            if ( empty( $organism_id ) ) {
                return [ 'payload' => $payload, 'port' => 'main' ];
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'ska_data_sys_organisms';
            
            // Lấy html_content dựa vào ID hoặc Name
            $html_content = $wpdb->get_var( $wpdb->prepare( "SELECT html_content FROM {$table_name} WHERE id = %s OR name = %s", $organism_id, $organism_id ) );

            if ( is_null( $html_content ) ) {
                error_log( "Ska_Logic_Render_Template Error: Template not found for ID: " . $organism_id );
                return [ 'payload' => $payload, 'port' => 'main' ];
            }
        }

        // 3. Nội suy dữ liệu vào template
        $rendered_html = $this->evaluate_template( $html_content, $payload );

        // 4. Lưu kết quả vào biến
        $result_var = isset( $config['result_var'] ) && !empty($config['result_var']) ? trim($config['result_var']) : 'payload.rendered_template';
        
        // Ghi dữ liệu vào đường dẫn của $result_var trong $payload
        $this->set_nested_value( $payload, $result_var, $rendered_html );

        return [
            'payload' => $payload,
            'port'    => 'main'
        ];
    }

    /**
     * Nội suy template string có chứa {{ ... }} thông qua SkaFX_Engine
     */
    private function evaluate_template( $template_string, $payload ) {
        if ( empty( $template_string ) ) {
            return '';
        }

        // Nếu chuỗi không chứa {{, trả về nguyên bản
        if ( strpos( $template_string, '{{' ) === false ) {
            return $template_string;
        }

        return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) use ($payload) {
            $expression = trim($matches[1]);
            try {
                $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expression, $payload );
                if ( isset($eval_result['last_val']) ) {
                    $val = $eval_result['last_val'];
                    if ( is_array( $val ) || is_object( $val ) ) {
                        return wp_json_encode( $val );
                    }
                    return (string) $val;
                }
            } catch ( Exception $e ) {
                error_log('SkaFX Eval Error in Render Template Node: ' . $e->getMessage());
                return '';
            }
            return '';
        }, $template_string);
    }

    /**
     * Hàm set value vào payload dựa trên dot notation (vd: payload.html.content)
     */
    private function set_nested_value( &$array, $path, $value ) {
        $keys = explode('.', $path);
        
        // Nếu bắt đầu bằng 'payload', loại bỏ nó vì ta đang xét bên trong $payload
        if ( $keys[0] === 'payload' ) {
            array_shift($keys);
        }

        $current = &$array;
        foreach ( $keys as $i => $key ) {
            if ( $i === count($keys) - 1 ) {
                $current[$key] = $value;
            } else {
                if ( !isset($current[$key]) || !is_array($current[$key]) ) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }
}
