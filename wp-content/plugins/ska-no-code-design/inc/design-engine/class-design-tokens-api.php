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
        if ( ! class_exists( 'Ska\Data\Core\Data_Fetcher' ) ) {
            return new \WP_Error( 'missing_dependency', 'Ska Data Pro is not active', [ 'status' => 500 ] );
        }

        $table_name = 'ska_data_sys_presets';
        $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, [
            'filter_field' => 'id',
            'filter_val'   => 'design_tokens',
            'filter_op'    => 'eq'
        ], 1 );
        
        $tokens = ! empty( $rows ) ? $rows[0] : null;

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
                        'headingFont' => 'Inter, sans-serif',
                        'bodyFont'    => 'Inter, sans-serif',
                        'baseSize'    => '16px',
                    ],
                    'tokens' => [
                        'borderRadius'       => '8px',
                        'boxShadow'          => 'none',
                        'containerWidth'     => '1280px',
                        'transitionDuration' => '150ms',
                    ],
                    'components' => new \stdClass()
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
        if ( ! class_exists( 'Ska\Data\Core\Data_Fetcher' ) ) {
            return new \WP_Error( 'missing_dependency', 'Ska Data Pro is not active', [ 'status' => 500 ] );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            return new \WP_Error( 'invalid_data', 'No data provided', [ 'status' => 400 ] );
        }

        // Validate nonce or let WP REST API handle it if using X-WP-Nonce
        
        $table_name = 'ska_data_sys_presets';
        $json_string = wp_json_encode( $body );

        // Check if exists
        $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, [
            'filter_field' => 'id',
            'filter_val'   => 'design_tokens',
            'filter_op'    => 'eq'
        ], 1 );
        
        $existing = ! empty( $rows ) ? $rows[0] : null;

        // We use apply_filters to map data safely using Ska Data Pro's pipeline
        $record_data = [
            'id' => 'design_tokens',
            'type' => 'design_tokens',
            'name' => 'Global Design Tokens',
            'json_content' => $json_string // Assuming 'json_content' column is a JSON or LONGTEXT field
        ];

        if ( $existing ) {
            $result = apply_filters( 'ska_data_update_record', false, $record_data, $table_name, [ 'id' => $existing['id'] ] );
        } else {
            $result = apply_filters( 'ska_data_insert_record', false, $record_data, $table_name );
        }

        if ( is_wp_error( $result ) ) {
            return $result;
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
