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
        // Read directly from DB for Admin Dashboard to get original Names and IDs
        if ( class_exists( '\Ska\Design\Api\Design_Tokens_Compiler' ) ) {
            global $wpdb;
            $presets_table = $wpdb->prefix . 'ska_data_sys_presets';
            $rows = $wpdb->get_results( "SELECT * FROM {$presets_table}", ARRAY_A );

            $data = [
                'brand' => [],
                'colors' => [],
                'darkColors' => [],
                'typography' => [],
                'typography_scale' => [],
                'tokens' => [],
                'components' => []
            ];

            if ( $rows ) {
                foreach ( $rows as $row ) {
                    $type = $row['type'];
                    $name = $row['name'];
                    $val = $row['value'];

                    if ( empty( $name ) ) continue;

                    $key = sanitize_key( str_replace( '-', '_', sanitize_title( $name ) ) );

                    switch ( $type ) {
                        case 'token_brand':
                            $camel_key = lcfirst( str_replace( ' ', '', ucwords( str_replace( ['-', '_'], ' ', sanitize_title( $name ) ) ) ) );
                            $data['brand'][$camel_key] = $val;
                            break;
                        case 'token_color':
                            $data['colors'][$key] = $val;
                            break;
                        case 'token_dark_color':
                            $data['darkColors'][$key] = $val;
                            break;
                        case 'token_font':
                            $data['typography'][$key] = $val;
                            break;
                        case 'preset_typography':
                            $data['typography_scale'][$key] = $val;
                            break;
                        case 'token_spacing':
                        case 'token_radius':
                        case 'token_shadow':
                            $camel_key = lcfirst( str_replace( ' ', '', ucwords( str_replace( ['-', '_'], ' ', sanitize_title( $name ) ) ) ) );
                            $data['tokens'][$camel_key] = $val;
                            break;
                        case 'preset_component':
                            $data['components'][] = [
                                'id' => $row['id'],
                                'name' => $name,
                                'value' => $val
                            ];
                            break;
                    }
                }
            }
            
            return rest_ensure_response( [
                'success' => true,
                'data'    => $data
            ] );
        }

        return rest_ensure_response( [
            'success' => false,
            'message' => 'Design_Tokens_Compiler not found'
        ] );
    }

    public function save_tokens( \WP_REST_Request $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_presets';

        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            return new \WP_Error( 'invalid_data', 'No data provided', [ 'status' => 400 ] );
        }

        // Mapping sections to types
        $section_type_map = [
            'brand' => 'token_brand',
            'colors' => 'token_color',
            'darkColors' => 'token_dark_color',
            'typography' => 'token_font',
            'typography_scale' => 'preset_typography',
            'components' => 'preset_component'
        ];

        $token_type_map = [
            'borderRadius' => 'token_radius',
            'boxShadow' => 'token_shadow',
            'containerWidth' => 'token_spacing',
            'transitionDuration' => 'token_spacing',
            'blockGap' => 'token_spacing',
            'contentPadding' => 'token_spacing'
        ];

        $wpdb->query( "START TRANSACTION" );

        try {
            // Xóa tất cả token cũ để đồng bộ 1 chiều từ payload
            $wpdb->query( "TRUNCATE TABLE {$table_name}" );

            foreach ( $section_type_map as $section => $db_type ) {
                if ( isset( $body[$section] ) && is_array( $body[$section] ) ) {
                    if ( $section === 'components' ) {
                        foreach ( $body[$section] as $preset ) {
                            if ( isset($preset['name']) && isset($preset['value']) ) {
                                $wpdb->insert(
                                    $table_name,
                                    [
                                        'name' => sanitize_text_field( $preset['name'] ),
                                        'type' => $db_type,
                                        'value' => sanitize_text_field( $preset['value'] )
                                    ],
                                    [ '%s', '%s', '%s' ]
                                );
                            }
                        }
                    } else {
                        foreach ( $body[$section] as $key => $val ) {
                            if ( $section === 'brand' ) {
                                $name = ucwords( preg_replace( '/([a-z])([A-Z])/', '$1 $2', $key ) );
                            } else {
                                $name = ucfirst(str_replace(['_', '-'], ' ', $key));
                            }
                            $wpdb->insert(
                                $table_name,
                                [
                                    'name' => $name,
                                    'type' => $db_type,
                                    'value' => is_string($val) ? $val : wp_json_encode($val)
                                ],
                                [ '%s', '%s', '%s' ]
                            );
                        }
                    }
                }
            }

            // Xử lý section 'tokens'
            if ( isset( $body['tokens'] ) && is_array( $body['tokens'] ) ) {
                foreach ( $body['tokens'] as $key => $val ) {
                    if ( ! isset( $token_type_map[$key] ) ) {
                        continue; // Chỉ lưu các token hợp lệ để tránh rác/trùng lặp (vd: blockgap vs blockGap)
                    }

                    $type = $token_type_map[$key];
                    $name = ucwords( preg_replace( '/([a-z])([A-Z])/', '$1 $2', $key ) ); // Convert camelCase to Space Case
                    
                    // Fix: Chuẩn hóa giá trị, loại bỏ khoảng trắng thừa giữa số và đơn vị (vd: '0 px' -> '0px')
                    $safe_val = is_string($val) ? trim($val) : wp_json_encode($val);
                    $safe_val = preg_replace('/^([\d\.]+)\s+(px|rem|em|%|vh|vw|ms|s)$/i', '$1$2', $safe_val);

                    $wpdb->insert(
                        $table_name,
                        [
                            'name' => $name,
                            'type' => $type,
                            'value' => $safe_val
                        ],
                        [ '%s', '%s', '%s' ]
                    );
                }
            }

            $wpdb->query( "COMMIT" );

            // Gọi compiler để xuất file cache
            if ( class_exists( '\Ska\Design\Api\Design_Tokens_Compiler' ) ) {
                \Ska\Design\Api\Design_Tokens_Compiler::get_instance()->compile_tokens_to_json();
            }

            return rest_ensure_response( [
                'success' => true,
                'message' => 'Lưu Design Tokens thành công.',
            ] );

        } catch ( \Exception $e ) {
            $wpdb->query( "ROLLBACK" );
            return new \WP_Error( 'db_error', 'Không thể lưu vào CSDL', [ 'status' => 500 ] );
        }
    }
}
