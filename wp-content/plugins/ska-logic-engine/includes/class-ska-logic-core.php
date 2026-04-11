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
        $this->init_hooks();
    }

    private function includes() {
        // 1. Interfaces (Khuôn mẫu)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/processors/interface-ska-node.php';
        
        // 2. Các cục Cục Nút (Nodes)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/processors/class-slug-processor.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/processors/class-date-processor.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/processors/class-format-processor.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/actions/class-insert-data-action.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/actions/class-email-action.php';
        
        // 3. Đường ray Tàu Hỏa (Pipeline Runner)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/class-workflow-runner.php';
        
        // 4. Họng Đón Dữ liệu (API)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/api/class-form-receiver.php';
        
        // 5. Đánh chặn Giao diện (Frontend Hydration)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/pipeline/class-dynamic-content.php';
        
        // 6. SkaFX Engine (Parser/Evaluator)
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-exceptions.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-lexer.php';
        require_once SKA_LOGIC_ENGINE_DIR . 'includes/skafx/class-skafx-parser.php';
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
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

        // Bơm ống Cáp JS cho Lớp Vỏ ở Frontend kết nối với Endpoint
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        // Nạp UI Component Universal Dynamic Binding vào Editor Gutenberg
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_scripts']);
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
        wp_enqueue_script(
            'ska-logic-frontend',
            SKA_LOGIC_ENGINE_URL . 'assets/js/ska-logic-frontend.js',
            [],
            SKA_LOGIC_ENGINE_VERSION,
            true
        );

        // Trao quyền Bản đồ Đường Đi (Route API) cho Javascript
        wp_localize_script('ska-logic-frontend', 'skaLogicEnv', [
            'rest_url' => esc_url_raw( rest_url( 'ska-logic/v1/submit' ) )
        ]);
    }

    public function register_admin_menu() {
        add_menu_page(
            'Ska Logic Automation', 
            'Ska Logic Engine', 
            'manage_options', 
            'ska-logic-engine', 
            [ $this, 'render_admin_page' ], 
            'dashicons-networking', // Icon mạng lưới Grid/Node
            32 // Nằm dưới Ska Data
        );
    }

    public function render_admin_page() {
        // XỬ LÝ ACTIONS QUẢN LÝ TỪ MANAGER UI
        if ( isset($_POST['ska_logic_action']) && check_admin_referer('ska_logic_nonce') ) {
            $action = sanitize_text_field($_POST['ska_logic_action']);
            $workflows = get_option('ska_logic_simple_workflows', []);
            
            if ($action === 'create') {
                $new_id = sanitize_title($_POST['new_workflow_id']);
                if (!empty($new_id) && !isset($workflows[$new_id])) {
                    $workflows[$new_id] = ['graph' => []];
                    update_option('ska_logic_simple_workflows', $workflows);
                    // Chuyển thẳng tới Builder
                    echo "<script>window.location.href='?page=ska-logic-engine&view=builder&workflow_id={$new_id}';</script>";
                    exit;
                }
            } elseif ($action === 'delete') {
                $del_id = sanitize_text_field($_POST['workflow_id']);
                if (isset($workflows[$del_id])) {
                    unset($workflows[$del_id]);
                    update_option('ska_logic_simple_workflows', $workflows);
                    echo '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #ef4444;"><p><strong>Đã băm nát Băng chuyền mục tiêu!</strong></p></div>';
                }
            } elseif ($action === 'rename') {
                $old_id = sanitize_text_field($_POST['old_id']);
                $new_id = sanitize_title($_POST['new_id']);
                if (isset($workflows[$old_id]) && !empty($new_id) && !isset($workflows[$new_id])) {
                    $workflows[$new_id] = $workflows[$old_id];
                    unset($workflows[$old_id]);
                    update_option('ska_logic_simple_workflows', $workflows);
                    echo '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #3b82f6;"><p><strong>Biển tên đã được cập nhật!</strong></p></div>';
                }
            }
        }

        // Xử lý Lưu Form Line Builder
        if ( isset($_POST['ska_logic_save']) && check_admin_referer('ska_logic_nonce') ) {
            $form_id    = sanitize_text_field( $_POST['ska_form_id'] );
            $raw_json   = wp_unslash( $_POST['ska_linear_graph'] );
            $graph      = json_decode($raw_json, true);

            if ( !is_array($graph) ) {
                $graph = [];
            }

            $workflows = get_option('ska_logic_simple_workflows', []);
            $workflows[ $form_id ] = [
                'graph' => $graph
            ];
            update_option('ska_logic_simple_workflows', $workflows);
            echo '<div class="notice notice-success is-dismissible" style="margin-top:15px; margin-left:0; border-left-color: #059669;"><p><strong>Dây mạng đã lưu thành công cấu trúc JSON Graph phức hợp!</strong></p></div>';
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
                    <p style="margin: 0; color: #6b7280; font-size: 13px;">Thiết lập tự động hoá & luồng dữ liệu (Ska-xi măng)</p>
                </div>
            </div>

            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <?php if ($current_view === 'builder'): ?>
                    <!-- Nhúng Giao diện Builder vào Cột Trái -->
                    <div style="flex: 1; min-width: 400px; max-width: 600px;">
                        <?php require_once SKA_LOGIC_ENGINE_DIR . 'includes/admin/admin-builder-ui.php'; ?>
                    </div>

                    <!-- Cột Phải Giải Thích -->
                    <div style="flex: 1; min-width: 300px; max-width: 400px;">
                        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                            <h2 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Về Băng Chuyền Logic</h2>
                            <p style="margin: 0 0 16px 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
                                Hệ thống Linear Builder này sẽ biến chuỗi thao tác của bạn thành một mảng <strong style="color:#0ea5e9;">JSON Graph Nhiều Tầng</strong>.
                            </p>
                            <p style="margin: 0 0 16px 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
                                Khi người dùng Submit Form, dữ liệu rác sẽ chạy qua bộ <code>Processor</code> để chắt lọc (vd: tạo Slug tự động, dọn dẹp Tag). Sau đó chạy tiếp tới bộ <code>Action</code> để phát sinh kết quả vật lý ngoài đời thực (Vd: Gửi Email cảnh báo hoặc Ghi Data xuống Kho).
                            </p>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 12px;">
                                <strong style="display: block; color: #b45309; margin-bottom: 4px; font-size: 13px;">Biến Động {{field}}</strong>
                                <p style="margin: 0; color: #78350f; font-size: 12px; line-height: 1.5;">
                                    Trong khối Cấu hình của Bước Action (Vd: Gửi Email), bạn có thể gọi tên một cột bất kỳ được gửi lên từ Form bằng cú pháp râu mép kép: <code>{{name}}</code>, <code>{{sdt}}</code>.
                                </p>
                            </div>
                        </div>
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
}
