<?php
namespace Ska\Design\Api;

defined( 'ABSPATH' ) || exit;

class Design_Tokens_API {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'ska-design/v1', '/tokens', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_tokens' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_tokens' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ]
        ] );
    }

    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    public function get_tokens( \WP_REST_Request $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_presets';
        
        $tokens = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE name = %s LIMIT 1", 'Global Design Tokens' ), ARRAY_A );

        if ( ! $tokens ) {
            // Default structure
            return rest_ensure_response( [
                'success' => true,
                'data' => [
                    'colors' => [
                        'primary'   => '#4f46e5',
                        'secondary' => '#10b981',
                        'surface'   => '#ffffff',
                        'text'      => '#1e293b',
                        'border'    => '#e2e8f0',
                    ],
                    'typography' => [
                        'primary' => 'Inter, sans-serif',
                        'secondary' => 'Outfit, sans-serif',
                        'customFontUrl' => ''
                    ],
                    'tokens' => [
                        'borderRadius'       => '8px',
                        'boxShadow'          => 'none',
                        'containerWidth'     => '1280px',
                        'transitionDuration' => '150ms',
                    ],
                    'components' => [
                        'button' => [
                            'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition',
                            'secondary' => 'bg-slate-800 text-white hover:bg-slate-900 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-slate-800 transition',
                            'outline' => 'bg-transparent border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition'
                        ]
                    ]
                ]
            ] );
        }

        $data_json = isset( $tokens['json_content'] ) ? json_decode( $tokens['json_content'], true ) : [];
        return rest_ensure_response( [
            'success' => true,
            'data'    => $data_json
        ] );
    }

    public function save_tokens( \WP_REST_Request $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_presets';

        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            return new \WP_Error( 'invalid_data', 'No data provided', [ 'status' => 400 ] );
        }

        $json_string = wp_json_encode( $body );

        // Check if exists
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE name = %s LIMIT 1", 'Global Design Tokens' ), ARRAY_A );

        if ( $existing ) {
            $result = $wpdb->update(
                $table_name,
                [ 'json_content' => $json_string ],
                [ 'id' => $existing['id'] ],
                [ '%s' ],
                [ '%d' ]
            );
        } else {
            $result = $wpdb->insert(
                $table_name,
                [
                    'name' => 'Global Design Tokens',
                    'type' => 'colors',
                    'json_content' => $json_string
                ],
                [ '%s', '%s', '%s' ]
            );
        }

        if ( false === $result ) {
            return new \WP_Error( 'db_error', 'Không thể lưu vào CSDL', [ 'status' => 500 ] );
        }

        // Xuất file Physical Cache (Export Tokens)
        $this->export_physical_cache( $json_string );

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Lưu Design Tokens và xuất file Cache thành công.',
        ] );
    }

    private function export_physical_cache( $json_string ) {
        $upload_dir = wp_upload_dir();
        $ska_dir = trailingslashit( $upload_dir['basedir'] ) . 'ska-data';
        
        if ( ! file_exists( $ska_dir ) ) {
            wp_mkdir_p( $ska_dir );
        }

        $file_path = $ska_dir . '/tokens.json';
        file_put_contents( $file_path, $json_string );
    }
}
