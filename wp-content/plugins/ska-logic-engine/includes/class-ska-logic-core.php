<?php

defined( 'ABSPATH' ) || exit;

class Ska_Logic_Core {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->maybe_create_tables();
        $this->init_hooks();
    }

    private function includes() {
        // 1. Interfaces (Khuôn mẫu)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/interface-ska-node.php';
        
        // 2. Các cục Cục Nút (Nodes)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-trigger.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-set-data.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-condition.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-switch.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-db-action.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-db-query.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-http-request.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-client-response.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-render-template.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/primitives/class-ska-logic-iterator.php';
        
        // 3. Đường ray Tàu Hỏa (Pipeline Runner & Async)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/class-workflow-runner.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/class-async-worker.php';
        
        // 4. Họng Đón Dữ liệu (API)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/api/class-form-receiver.php';
        
        // 5. Đánh chặn Giao diện (Frontend Hydration)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/class-dynamic-content.php';
        
        // 6. SkaFX Engine (Parser/Evaluator)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-exceptions.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-lexer.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-parser.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-context-resolver.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-evaluator.php';
    }

    private function init_hooks() {
        // Khởi động REST API Receiver
        add_action( 'rest_api_init', [ 'Ska_Form_Receiver', 'register_routes' ] );
        
        // Khởi động Frontend Data Hydration Pipeline
        Ska_Dynamic_Content::instance();
        
        // Đón nhận Lệnh "Chạy Workflow" và Trả Pipeline Data về
        add_filter( 'ska_logic_run_pipeline', [ 'Ska_Workflow_Runner', 'execute' ], 10, 2 );

        // Đăng ký WP Admin Menu
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 20 );
        
        // Đăng ký Module Card vào System Dashboard
        add_action( 'ska_system_dashboard_modules', [ $this, 'render_dashboard_card' ] );

        // Bơm ống Cáp JS cho Lớp Vỏ ở Frontend kết nối với Endpoint
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        // Nạp UI Component Universal Dynamic Binding vào Editor Gutenberg
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_scripts']);

        // Đăng ký bảo vệ bảng phẳng hệ thống của Logic Engine
        add_filter( 'ska_data_protected_tables', [ $this, 'protect_system_tables' ] );
    }

    public function enqueue_editor_scripts() {
        // Sử dụng asset.php từ wp-scripts để tự động lấy đúng Version và Dependencies (React, Components, v.v.)
        $asset_path = SKA_LOGIC_ENGINE_DIR . 'assets/js/dist/index.jsx.asset.php';
        
        if ( file_exists( $asset_path ) ) {
            $asset = require $asset_path;

            // Bổ sung wp-api-fetch vì Schema API dùng nó
            if (!in_array('wp-api-fetch', $asset['dependencies'])) {
                $asset['dependencies'][] = 'wp-api-fetch';
            }

            wp_enqueue_script(
                'ska-editor-logic-js',
                SKA_LOGIC_ENGINE_URL . 'assets/js/dist/index.jsx.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );
        }

        // Truyền đường dẫn API Schema (Fallback cho trường hợp không dùng wp.apiFetch)
        wp_localize_script('ska-editor-logic-js', 'skaLogicEditorEnv', [
            'schema_api_url' => esc_url_raw( rest_url( 'ska-logic/v1/schema' ) )
        ]);
    }

    public function enqueue_frontend_scripts() {
        $version = file_exists( SKA_LOGIC_ENGINE_DIR . 'assets/js/ska-core.js' ) 
            ? filemtime( SKA_LOGIC_ENGINE_DIR . 'assets/js/ska-core.js' ) 
            : SKA_LOGIC_ENGINE_VERSION;

        wp_enqueue_script(
            'ska-core-js',
            SKA_LOGIC_ENGINE_URL . 'assets/js/ska-core.js',
            [],
            $version,
            true
        );

        // Trao quyền Bản đồ Đường Đi (Route API) & Thông tin cho thư viện $ska
        wp_localize_script('ska-core-js', 'skaAppEnv', [
            'ajaxUrl'  => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
            'restUrl'  => esc_url_raw( rest_url( 'ska-logic/v1/submit' ) ),
            'nonce'    => wp_create_nonce('wp_rest') // BẮT BUỘC dùng 'wp_rest' cho WP REST API Header
        ]);
    }

    public function register_admin_menu() {
        // Gắn vào under Ska System Framework thay vì tạo Menu Độc Lập
        add_submenu_page(
            'ska-system-dashboard',
            'Ska Logic Automation', 
            'Ska Logic Engine', 
            'manage_options', 
            'ska-logic-engine', 
            [ $this, 'render_admin_page' ]
        );

        // Đọc danh sách workflow_id dựa trên Dev Mode
        $dev_mode = get_option( 'ska_system_dev_mode', '1' ) === '1';
        $workflow_ids = [];
        if ( $dev_mode ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ska_data_sys_workflows';
            $wpdb->suppress_errors( true );
            $workflow_ids = $wpdb->get_col( "SELECT workflow_id FROM `{$table_name}`" );
            $wpdb->suppress_errors( false );
        } else {
            $workflow_ids = get_option( 'ska_logic_workflow_ids', [] );
        }

        if ( is_array( $workflow_ids ) ) {
            foreach ( $workflow_ids as $id ) {
                add_submenu_page(
                    null,
                    $id . ' Workflow', 
                    '— ' . $id, 
                    'manage_options', 
                    'ska-logic-engine&view=builder&workflow_id=' . $id, 
                    [ $this, 'render_admin_page' ]
                );
            }
        }
    }

    public function render_dashboard_card() {
        ?>
        <!-- Module: Logic Engine -->
        <div class="module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-amber-400 to-orange-600 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-16 h-16 bg-gradient-to-br from-amber-50 to-orange-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0 border border-amber-100 shadow-inner group-hover:scale-105 transition-transform duration-300">
                <span class="material-symbols-outlined text-[32px]">account_tree</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg m-0 p-0">Ska Logic Engine</h3>
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed"><?php esc_html_e( 'Cement binds the ecosystem. ', 'ska-logic-engine' ); ?></p>
                    </div>
                </div>
                <div class="mt-5 flex gap-4 text-sm">
                    <a href="?page=ska-logic-engine" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:text-indigo-800 transition-colors bg-indigo-50 px-3 py-1.5 rounded-lg no-underline">
                        <span class="material-symbols-outlined text-[18px]">account_tree</span> <?php esc_html_e( 'Open Workflows', 'ska-logic-engine' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_admin_page() {
        // XỬ LÝ ACTIONS QUẢN LÝ TỪ MANAGER UI
        if ( isset($_POST['ska_logic_action']) && check_admin_referer('ska_logic_nonce') ) {
            $action = sanitize_text_field($_POST['ska_logic_action']);
            global $wpdb;
            $table_name = $wpdb->prefix . 'ska_data_sys_workflows';
            
            if ($action === 'create') {
                $new_id = sanitize_title($_POST['new_workflow_id']);
                if (!empty($new_id)) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_name}` WHERE workflow_id = %s", $new_id));
                    if (!$exists) {
                        $wpdb->insert(
                            $table_name,
                            [
                                'workflow_id' => $new_id,
                                'name'        => $new_id,
                                'app_id'      => 'ska_system',
                                'graph'       => wp_json_encode(['nodes' => [], 'edges' => []]),
                                'node_count'  => 0,
                                'status'      => 'active'
                            ]
                        );
                        self::sync_workflow_ids_cache();
                        // Chuyển thẳng tới Builder
                        echo "<script>window.location.href='?page=ska-logic-engine&view=builder&workflow_id={$new_id}';</script>";
                        exit;
                    }
                }
            } elseif ($action === 'delete') {
                $del_id = sanitize_text_field($_POST['workflow_id']);
                $wpdb->delete($table_name, ['workflow_id' => $del_id]);
                self::sync_workflow_ids_cache();
                echo __( '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #ef4444;"><p><strong>Target Carousel shredded!</strong></p></div>', 'ska-logic-engine' );
            } elseif ($action === 'rename') {
                $old_id = sanitize_text_field($_POST['old_id']);
                $new_id = sanitize_title($_POST['new_id']);
                if (!empty($new_id)) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_name}` WHERE workflow_id = %s", $new_id));
                    if (!$exists) {
                        $wpdb->update(
                            $table_name,
                            [
                                'workflow_id' => $new_id,
                                'name'        => $new_id
                            ],
                            ['workflow_id' => $old_id]
                        );
                        self::sync_workflow_ids_cache();
                        echo __( '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #3b82f6;"><p><strong>Nameplate has been updated!</strong></p></div>', 'ska-logic-engine' );
                    }
                }
            }
        }

        // Xử lý Lưu Form Line Builder
        if ( isset($_POST['ska_logic_save']) && check_admin_referer('ska_logic_nonce') ) {
            $form_id    = isset($_POST['ska_form_id']) ? sanitize_text_field( $_POST['ska_form_id'] ) : '';
            $raw_json   = wp_unslash( $_POST['ska_linear_graph'] );
            $graph      = json_decode($raw_json, true);

            if ( !is_array($graph) ) {
                $graph = [];
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'ska_data_sys_workflows';
            $node_count = isset($graph['nodes']) && is_array($graph['nodes']) ? count($graph['nodes']) : 0;

            $wpdb->update(
                $table_name,
                [
                    'graph'      => wp_json_encode($graph),
                    'node_count' => $node_count
                ],
                ['workflow_id' => $form_id]
            );
            self::sync_workflow_ids_cache();
            echo __( '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #059669;"><p><strong>The network wire has successfully saved the complex JSON Graph structure!</strong></p></div>', 'ska-logic-engine' );
        }

        $current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'list';
        ?>
        <div class="wrap" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-top: 15px;">
                <div style="background: #4f46e5; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);">
                    <span class="dashicons dashicons-networking" style="color: white; font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <div>
                    <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #111827; line-height: 1.2;">Ska Logic Engine</h1>
                    <p style="margin: 0; color: #6b7280; font-size: 13px;"><?php esc_html_e( 'Setting up automation & data flow (Ska-cement)', 'ska-logic-engine' ); ?></p>
                </div>
            </div>

            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <?php if ($current_view === 'builder'): ?>
                    <!-- Nhúng Giao diện Builder Full Width -->
                    <div style="flex: 1; width: 100%;">
                        <?php require_once SKA_LOGIC_ENGINE_DIR . 'includes/admin/admin-builder-ui.php'; ?>
                    </div>
                <?php else: ?>
                    <!-- Màn hình Danh sách Quản lý (Manager UI) đè toàn bộ -->
                    <div style="flex: 1; width: 100%;">
                        <?php require_once SKA_LOGIC_ENGINE_DIR . 'includes/admin/admin-manager-ui.php'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function protect_system_tables( $protected_tables ) {
        global $wpdb;
        $protected_tables[] = $wpdb->prefix . 'ska_data_sys_workflows';
        return $protected_tables;
    }

    public function maybe_create_tables() {
        $db_version = get_option( 'ska_logic_db_version', '' );
        if ( $db_version === '1.1.0' ) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_workflows';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE `{$table_name}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `workflow_id` varchar(191) NOT NULL,
            `name` varchar(255) NOT NULL,
            `app_id` varchar(100) DEFAULT 'ska_system',
            `status` varchar(50) DEFAULT 'active',
            `node_count` int(11) DEFAULT 0,
            `graph` json DEFAULT NULL,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `workflow_id` (`workflow_id`)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Xóa option cũ
        delete_option( 'ska_logic_simple_workflows' );

        // Đăng ký vào dictionary để hiển thị trong Site Management
        $dictionary = get_option( 'ska_data_dictionary', [] );
        if ( ! is_array( $dictionary ) ) {
            $dictionary = [];
        }
        if ( ! isset( $dictionary[ $table_name ] ) ) {
            $dictionary[ $table_name ] = [];
        }
        $dictionary[ $table_name ]['__table_info'] = [
            'name'   => 'Workflows',
            'icon'   => 'dashicons-networking',
            'app_id' => 'ska_system'
        ];
        update_option( 'ska_data_dictionary', $dictionary );

        update_option( 'ska_logic_db_version', '1.1.0' );

        self::sync_workflow_ids_cache();
    }

    public static function sync_workflow_ids_cache() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_workflows';
        $wpdb->suppress_errors( true );
        $ids = $wpdb->get_col( "SELECT workflow_id FROM `{$table_name}`" );
        $wpdb->suppress_errors( false );
        if ( ! is_array( $ids ) ) {
            $ids = [];
        }
        update_option( 'ska_logic_workflow_ids', $ids );
    }
}
