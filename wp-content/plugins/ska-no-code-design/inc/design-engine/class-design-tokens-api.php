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
            // Default structure (Expanded for Metric Flow & Vanilla CSS Support)
            return rest_ensure_response( [
                'success' => true,
                'data' => [
                    'colors' => [
                        'primary'   => '#3b82f6',
                        'secondary' => '#10b981',
                        'tertiary'  => '#f59e0b',
                        'surface'   => '#ffffff',
                        'background'=> '#f9fafb',
                        'text'      => '#111827',
                        'border'    => '#e5e7eb',
                        'success'   => '#10b981',
                        'warning'   => '#f59e0b',
                        'error'     => '#ef4444',
                        'info'      => '#3b82f6',
                    ],
                    'typography' => [
                        'primary' => 'Inter, sans-serif',
                        'secondary' => 'Outfit, sans-serif',
                        'mono' => 'IBM Plex Mono, monospace',
                        'customFontUrl' => ''
                    ],
                    'typography_scale' => [
                        'h1'    => 'text-5xl font-bold tracking-tight leading-tight',
                        'h2'    => 'text-4xl font-bold tracking-tight leading-tight',
                        'h3'    => 'text-2xl font-semibold tracking-tight leading-snug',
                        'h4'    => 'text-lg font-bold leading-relaxed',
                        'p'     => 'text-base font-normal leading-relaxed',
                        'small' => 'text-sm font-normal leading-relaxed',
                    ],
                    'tokens' => [
                        'borderRadius'       => '6px',
                        'boxShadow'          => 'none',
                        'containerWidth'     => '1280px',
                        'transitionDuration' => '150ms',
                    ],
                    'components' => [
                        'button' => [
                            'primary' => 'bg-primary text-white hover:bg-blue-700 px-4 py-2 rounded-md font-semibold transition',
                            'secondary' => 'bg-transparent border border-primary text-primary hover:bg-blue-50 px-4 py-2 rounded-md font-semibold transition',
                            'outline' => 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md font-semibold transition'
                        ],
                        'card' => [
                            'default' => 'bg-surface border border-gray-200 rounded-md p-4',
                            'elevated' => 'bg-surface shadow-md rounded-md p-4',
                        ],
                        'input' => [
                            'text' => 'bg-surface border border-gray-200 text-gray-900 text-sm rounded-md focus:ring-primary focus:border-primary block w-full p-2.5',
                            'label' => 'block mb-2 text-sm font-medium text-gray-900',
                        ],
                        'badge' => [
                            'status' => 'bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded border border-green-400',
                            'filter' => 'bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded border border-gray-400 hover:bg-gray-200 cursor-pointer',
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

        // Xuất file Physical Cache (Export Tokens JSON & CSS)
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

        // Save tokens.json
        $json_file_path = $ska_dir . '/tokens.json';
        file_put_contents( $json_file_path, $json_string );

        // Generate and save tokens.css for Vanilla CSS support
        $tokens = json_decode( $json_string, true );
        $css_content = "/* Ska Design System - Auto Generated Custom Properties */\n:root {\n";

        if ( isset( $tokens['colors'] ) && is_array( $tokens['colors'] ) ) {
            foreach ( $tokens['colors'] as $key => $value ) {
                if ( ! empty( $value ) ) {
                    $css_content .= "  --ska-color-{$key}: {$value};\n";
                }
            }
        }

        if ( isset( $tokens['typography'] ) && is_array( $tokens['typography'] ) ) {
            foreach ( $tokens['typography'] as $key => $value ) {
                if ( ! empty( $value ) && $key !== 'customFontUrl' ) {
                    $css_content .= "  --ska-font-{$key}: {$value};\n";
                }
            }
        }

        if ( isset( $tokens['tokens'] ) && is_array( $tokens['tokens'] ) ) {
            foreach ( $tokens['tokens'] as $key => $value ) {
                if ( ! empty( $value ) ) {
                    // Convert camelCase to kebab-case
                    $kebab_key = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $key ) );
                    $css_content .= "  --ska-sys-{$kebab_key}: {$value};\n";
                }
            }
        }

        $css_content .= "}\n";

        $css_file_path = $ska_dir . '/tokens.css';
        file_put_contents( $css_file_path, $css_content );
    }
}
