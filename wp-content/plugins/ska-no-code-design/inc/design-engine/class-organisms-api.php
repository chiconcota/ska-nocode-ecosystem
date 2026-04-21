<?php
namespace Ska\Design\Api;

defined( 'ABSPATH' ) || exit;

class Organisms_API {
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
        register_rest_route( 'ska-design/v1', '/organisms', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_organism' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ]
        ] );
    }

    public function check_permission() {
        return current_user_can( 'edit_posts' );
    }

    public function save_organism( \WP_REST_Request $request ) {
        if ( ! class_exists( 'Ska\Data\Core\Data_Fetcher' ) ) {
            return new \WP_Error( 'missing_dependency', 'Ska Data Pro is not active', [ 'status' => 500 ] );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) || empty( $body['name'] ) || empty( $body['block_json'] ) ) {
            return new \WP_Error( 'invalid_data', 'Thiếu dữ liệu (name hoặc block_json)', [ 'status' => 400 ] );
        }

        $table_name = 'ska_data_sys_organisms';
        $organism_id = 'org_' . uniqid();
        
        // Transform the incoming block_json into a string safely
        $json_content = wp_json_encode( $body['block_json'] );
        $html_content = isset( $body['html_content'] ) ? $body['html_content'] : '';

        $record_data = [
            'id'           => $organism_id,
            'type'         => 'organism',
            'name'         => sanitize_text_field( $body['name'] ),
            'json_content' => $json_content,
            'html_content' => $html_content
        ];

        // Insert using Ska Data Pro Filter Pipeline
        $result = apply_filters( 'ska_data_insert_record', false, $record_data, $table_name );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Lưu Organism thành công.',
            'data'    => $record_data
        ] );
    }

    private function export_physical_cache() {
        $table_name = 'ska_data_sys_organisms';
        
        $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, [], -1 );
        
        $cache_data = [];
        if ( ! empty( $rows ) ) {
            foreach ( $rows as $row ) {
                $cache_data[ $row['id'] ] = [
                    'id'   => $row['id'],
                    'name' => $row['name'],
                    'json' => json_decode( $row['json_content'], true ),
                    'html_content' => $row['html_content']
                ];
            }
        }

        $upload_dir = wp_upload_dir();
        $ska_dir = trailingslashit( $upload_dir['basedir'] ) . 'ska-data';
        
        if ( ! file_exists( $ska_dir ) ) {
            wp_mkdir_p( $ska_dir );
        }

        $file_path = $ska_dir . '/organisms.json';
        file_put_contents( $file_path, wp_json_encode( $cache_data ) );
    }
}
