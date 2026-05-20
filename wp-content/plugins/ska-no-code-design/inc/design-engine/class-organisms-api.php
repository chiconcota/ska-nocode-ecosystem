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
        // GET: List organisms
        register_rest_route( 'ska-design/v1', '/organisms', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_organisms' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_organism' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ]
        ] );

        // DELETE: Delete organism
        register_rest_route( 'ska-design/v1', '/organisms/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_organism' ],
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
        if ( empty( $body ) || empty( $body['name'] ) || ! isset( $body['block_json'] ) ) {
            return new \WP_Error( 'invalid_data', 'Thiếu dữ liệu (name hoặc block_json)', [ 'status' => 400 ] );
        }

        $table_name = 'ska_data_sys_organisms';
        
        // Transform the incoming block_json into a string safely
        $json_content = wp_json_encode( $body['block_json'] );
        $html_content = isset( $body['html_content'] ) ? $body['html_content'] : '';

        $record_data = [
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
        
        // Grab the auto-increment DB ID
        if ( $result ) {
            $record_data['id'] = $result;
            $date_format = get_option( 'date_format' );
            $record_data['updated_at'] = date_i18n( $date_format );
        } else {
            return new \WP_Error( 'db_error', 'Không thể chèn bản ghi vào database.', [ 'status' => 500 ] );
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Lưu Organism thành công.',
            'data'    => $record_data
        ] );
    }

    public function get_organisms( \WP_REST_Request $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_organisms';

        // Phục vụ tương thích ngược: Các organism cũ được tạo trước khi có cột `type` sẽ có type rỗng.
        $results = $wpdb->get_results( "SELECT id, name, created_at FROM {$table_name} WHERE type = 'organism' OR type = '' OR type IS NULL ORDER BY id DESC", ARRAY_A );

        if ( $wpdb->last_error ) {
            return new \WP_Error( 'db_error', 'Lỗi truy vấn cơ sở dữ liệu.', [ 'status' => 500 ] );
        }

        $formatted_results = [];
        if ( ! empty( $results ) ) {
            $date_format = get_option( 'date_format' );
            foreach ( $results as $row ) {
                $row['updated_at'] = date_i18n( $date_format, strtotime( $row['created_at'] ?? 'now' ) );
                $formatted_results[] = $row;
            }
        }

        return rest_ensure_response( [
            'success' => true,
            'data'    => $formatted_results
        ] );
    }

    public function delete_organism( \WP_REST_Request $request ) {
        global $wpdb;
        $id = $request->get_param( 'id' );

        if ( empty( $id ) ) {
            return new \WP_Error( 'invalid_id', 'Thiếu ID Organism.', [ 'status' => 400 ] );
        }

        $table_name = $wpdb->prefix . 'ska_data_sys_organisms';
        $deleted = $wpdb->delete( $table_name, [ 'id' => $id, 'type' => 'organism' ], [ '%d', '%s' ] );

        if ( false === $deleted ) {
            return new \WP_Error( 'delete_failed', 'Không thể xóa Organism.', [ 'status' => 500 ] );
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => 'Đã xóa Organism.'
        ] );
    }

    public function export_physical_cache() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_organisms';
        
        $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, [], 1000 );
        
        $cache_data = [];
        if ( ! empty( $rows ) ) {
            foreach ( $rows as $row ) {
                $cache_data[ $row['id'] ] = [
                    'id'   => $row['id'],
                    'name' => $row['name'],
                    'json' => json_decode( $row['json_content'] ?? '', true ),
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

    /**
     * Tải hàng loạt HTML của các Organisms dựa vào mảng ID
     * (Phục vụ cho Loop Block siêu tốc để tránh N+1 Query)
     * 
     * @param array $ids Danh sách các organism ID
     * @return array Mảng kết hợp [ id => html_content ]
     */
    public static function get_bulk_html( array $ids ) {
        if ( empty( $ids ) ) return [];

        $ids = array_map( 'absint', $ids );
        $ids = array_filter( $ids ); // Loại bỏ 0
        $ids = array_unique( $ids );
        
        if ( empty( $ids ) ) return [];

        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_organisms';
        $in_clause = implode( ',', $ids );
        
        $results = $wpdb->get_results( "SELECT id, html_content FROM {$table_name} WHERE id IN ({$in_clause})", ARRAY_A );
        
        $bulk = [];
        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                $bulk[ $row['id'] ] = $row['html_content'];
            }
        }
        
        return $bulk;
    }
}
