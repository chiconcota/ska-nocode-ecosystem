<?php
namespace Skaaa\Design\Api;

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
        // POST: Create organism
        register_rest_route( 'skaaa-design/v1', '/organisms', [
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

        // Item level REST operations (Update/Delete)
        register_rest_route( 'skaaa-design/v1', '/organisms/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback' => [ $this, 'delete_organism' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'update_organism' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ]
        ] );

        // Categories list
        register_rest_route( 'skaaa-design/v1', '/categories', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_categories' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_categories' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ]
        ] );
    }

    public function check_permission() {
        return current_user_can( 'edit_posts' );
    }

    public function save_organism( \WP_REST_Request $request ) {
        if ( ! class_exists( 'Skaaa\Data\Core\Data_Fetcher' ) ) {
            return new \WP_Error( 'missing_dependency', 'Skaaa Data Pro is not active', [ 'status' => 500 ] );
        }

        $body = $request->get_json_params();
        if ( empty( $body ) || empty( $body['name'] ) || ! isset( $body['block_json'] ) ) {
            return new \WP_Error( 'invalid_data', __( 'Missing data (name or block_json)', 'skaaa-no-code-design' ), [ 'status' => 400 ] );
        }

        $table_name = 'skaaa_data_sys_organisms';
        
        // Transform the incoming block_json into a string safely
        $json_content = wp_json_encode( $body['block_json'] );
        $html_content = isset( $body['html_content'] ) ? $body['html_content'] : '';

        $record_data = [
            'type'         => 'organism',
            'name'         => sanitize_text_field( $body['name'] ),
            'category'     => sanitize_text_field( $body['category'] ?? '' ),
            'json_content' => $json_content,
            'html_content' => $html_content
        ];

        // Insert using Skaaa Data Pro Filter Pipeline
        $result = apply_filters( 'skaaa_data_insert_record', false, $record_data, $table_name );

        if ( is_wp_error( $result ) ) {
            return $result;
        }
        
        // Grab the auto-increment DB ID
        if ( $result ) {
            $record_data['id'] = $result;
            $date_format = get_option( 'date_format' );
            $record_data['updated_at'] = date_i18n( $date_format );
        } else {
            return new \WP_Error( 'db_error', __( 'Unable to insert record into database.', 'skaaa-no-code-design' ), [ 'status' => 500 ] );
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Saved Organism successfully.', 'skaaa-no-code-design' ),
            'data'    => $record_data
        ] );
    }

    public function update_organism( \WP_REST_Request $request ) {
        $id = intval( $request->get_param( 'id' ) );
        $body = $request->get_json_params();

        if ( empty( $id ) ) {
            return new \WP_Error( 'invalid_id', __( 'Missing Organism ID.', 'skaaa-no-code-design' ), [ 'status' => 400 ] );
        }

        $table_name = 'skaaa_data_sys_organisms';
        $record_data = [];

        if ( isset( $body['name'] ) ) {
            $record_data['name'] = sanitize_text_field( $body['name'] );
        }
        if ( isset( $body['category'] ) ) {
            $record_data['category'] = sanitize_text_field( $body['category'] );
        }

        if ( empty( $record_data ) ) {
            return new \WP_Error( 'empty_data', __( 'No data to update.', 'skaaa-no-code-design' ), [ 'status' => 400 ] );
        }

        $result = apply_filters( 'skaaa_data_update_record', false, $record_data, $table_name, [ 'id' => $id ] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Organism updated successfully.', 'skaaa-no-code-design' )
        ] );
    }

    public function get_organisms( \WP_REST_Request $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'skaaa_data_sys_organisms';

        // Phục vụ tương thích ngược: Các organism cũ được tạo trước khi có cột `type` sẽ có type rỗng.
        $results = $wpdb->get_results( "SELECT id, name, category, created_at FROM {$table_name} WHERE type = 'organism' OR type = '' OR type IS NULL ORDER BY id DESC", ARRAY_A );

        if ( $wpdb->last_error ) {
            return new \WP_Error( 'db_error', __( 'Database query error.', 'skaaa-no-code-design' ), [ 'status' => 500 ] );
        }

        $formatted_results = [];
        if ( ! empty( $results ) ) {
            $date_format = get_option( 'date_format' );
            foreach ( $results as $row ) {
                $row['updated_at'] = date_i18n( $date_format, strtotime( $row['created_at'] ?? 'now' ) );
                $row['category'] = $row['category'] ?? '';
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
            return new \WP_Error( 'invalid_id', __( 'Missing Organism ID.', 'skaaa-no-code-design' ), [ 'status' => 400 ] );
        }

        $table_name = $wpdb->prefix . 'skaaa_data_sys_organisms';
        $deleted = $wpdb->delete( $table_name, [ 'id' => $id, 'type' => 'organism' ], [ '%d', '%s' ] );

        if ( false === $deleted ) {
            return new \WP_Error( 'delete_failed', __( 'Organism cannot be deleted.', 'skaaa-no-code-design' ), [ 'status' => 500 ] );
        }

        // Export physical cache
        $this->export_physical_cache();

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Organism removed.', 'skaaa-no-code-design' )
        ] );
    }

    public function export_physical_cache() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'skaaa_data_sys_organisms';
        
        $rows = \Skaaa\Data\Core\Data_Fetcher::get_table_rows( $table_name, [], 1000 );
        
        $cache_data = [];
        if ( ! empty( $rows ) ) {
            foreach ( $rows as $row ) {
                $cache_data[ $row['id'] ] = [
                    'id'   => $row['id'],
                    'name' => $row['name'],
                    'category' => $row['category'] ?? '',
                    'json' => json_decode( $row['json_content'] ?? '', true ),
                    'html_content' => $row['html_content']
                ];
            }
        }

        $upload_dir = wp_upload_dir();
        $skaaa_dir = trailingslashit( $upload_dir['basedir'] ) . 'skaaa-data';
        
        if ( ! file_exists( $skaaa_dir ) ) {
            wp_mkdir_p( $skaaa_dir );
        }

        $file_path = $skaaa_dir . '/organisms.json';
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
        $table_name = $wpdb->prefix . 'skaaa_data_sys_organisms';
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

    public function get_categories( \WP_REST_Request $request ) {
        $categories = get_option( 'skaaa_organism_categories', [] );
        if ( ! is_array( $categories ) ) {
            $categories = [];
        }
        return rest_ensure_response( [
            'success' => true,
            'data'    => array_values( array_filter( $categories ) )
        ] );
    }

    public function save_categories( \WP_REST_Request $request ) {
        $body = $request->get_json_params();
        if ( ! isset( $body['categories'] ) || ! is_array( $body['categories'] ) ) {
            return new \WP_Error( 'invalid_data', __( 'Missing categories array.', 'skaaa-no-code-design' ), [ 'status' => 400 ] );
        }

        $categories = array_map( 'sanitize_text_field', $body['categories'] );
        $categories = array_values( array_unique( array_filter( $categories ) ) );

        update_option( 'skaaa_organism_categories', $categories );

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Categories updated successfully.', 'skaaa-no-code-design' ),
            'data'    => $categories
        ] );
    }
}
