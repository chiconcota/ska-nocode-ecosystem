<?php
namespace Ska_System_Framework;

defined('ABSPATH') || exit;

class Framework_UI
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_init', array($this, 'save_settings'));
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('ska_system_dashboard_modules', array($this, 'render_default_modules'), 99);
    }

    public function save_settings()
    {
        if (isset($_POST['ska_settings_nonce']) && wp_verify_nonce($_POST['ska_settings_nonce'], 'ska_save_settings')) {
            if (!current_user_can('manage_options')) {
                wp_die('Không đủ quyền hạn.');
            }

            // Lưu trạng thái checkbox (nếu checked thì tồn tại biến POST, nếu không thì vắng mặt)
            $dev_mode = isset($_POST['ska_system_dev_mode']) ? '1' : '0';
            update_option('ska_system_dev_mode', $dev_mode);

            $url = add_query_arg('settings-updated', 'true', wp_get_referer());
            wp_safe_redirect($url ? $url : admin_url('admin.php?page=ska-system-dashboard'));
            exit;
        }
    }

    public function render_default_modules()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Tự động kiểm tra Ska No-code Design (đang chạy framework thì hiển nhiên active nhưng nếu framework load từ chỗ khác thì sao?)
        // Ở đây giả định Ska No-Code Design đã active nếu chạy code này, hoặc được inject từ chính plugin của nó.

        // Mảng cấu hình các module lõi cần điểm danh
        $ecosystem_modules = [
            'ska-data-pro' => [
                'name' => 'Ska Data Pro',
                'desc' => 'Quản trị Flat Tables (ska_data_*), Schema Manager và Strategy Pattern DataGrid. Thay thế hoàn toàn wp_postmeta.',
                'icon' => 'database',
                'path' => 'ska-data-pro/ska-data-pro.php',
                'url' => 'https://ska.vn/data-pro' // Link tải plugin
            ],
            'ska-logic-engine' => [
                'name' => 'Ska Logic Engine',
                'desc' => 'Xi-măng kết dính hệ sinh thái. Xử lý sự kiện (Workflows), Phân giải ngữ cảnh (Smart Context), CodeMirror UI và SkaFX AST.',
                'icon' => 'account_tree',
                'path' => 'ska-logic-engine/ska-logic-engine.php',
                'url' => 'https://ska.vn/logic-engine'
            ]
        ];

        foreach ($ecosystem_modules as $id => $mod) {
            // Nếu đã active thì plugin đó TỰ động hook thẻ của nó vào rồi. 
            // Framework chỉ lo phần hiển thị Fallback (Chưa cài / Chưa kích hoạt).
            if (!is_plugin_active($mod['path'])) {
                $is_installed = file_exists(WP_PLUGIN_DIR . '/' . $mod['path']);
                $status_class = $is_installed ? 'bg-slate-200 text-slate-700 border-slate-300' : 'bg-rose-100 text-rose-700 border-rose-200';
                $status_text = $is_installed ? 'Chưa kích hoạt' : 'Chưa cài đặt';
                ?>
                <div class="module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group opacity-75 grayscale-[40%] hover:grayscale-0 transition-all duration-500">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-slate-300 group-hover:bg-indigo-400 transition-colors"></div>
                    <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center flex-shrink-0 border border-slate-200 shadow-inner group-hover:bg-indigo-50 group-hover:text-indigo-500 group-hover:border-indigo-100 transition-all duration-300">
                        <span class="material-symbols-outlined text-[32px]"><?php echo esc_attr($mod['icon']); ?></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg m-0 border-0 pt-0 pb-0">
                                    <?php echo esc_html($mod['name']); ?></h3>
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed"><?php echo esc_html($mod['desc']); ?></p>
                            </div>
                            <span class="h-[24px] inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold border shadow-sm <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_text); ?>
                            </span>
                        </div>
                        <div class="mt-5 flex gap-3 text-sm">
                            <?php if ($is_installed): ?>
                                <button class="border-0 bg-slate-100 text-slate-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">power_off</span> Vui lòng bật trong thẻ Plugins
                                </button>
                            <?php else: ?>
                                <a href="<?php echo esc_url($mod['url']); ?>" target="_blank"
                                    class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:text-indigo-800 transition-colors bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg no-underline">
                                    <span class="material-symbols-outlined text-[18px]">download</span> Tải xuống Plugin
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        ?>
        <!-- Module: Bridge -->
        <div class="module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group opacity-75 grayscale-[40%] hover:grayscale-0 transition-all duration-500">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-slate-300 group-hover:bg-slate-400 transition-colors"></div>
            <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center flex-shrink-0 border border-slate-200 shadow-inner group-hover:scale-105 transition-transform duration-300">
                <span class="material-symbols-outlined text-[32px]">api</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="m-0 pt-0 pb-0 border-0 font-bold text-slate-800 text-lg">Ska Bridge (Đang phát triển)</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">Cầu nối kiến trúc Headless (wordpress2nextjs) & JSON Schema API Export. Giải pháp tối thượng tách biệt hoàn toàn Frontend và Backend.</p>
                    </div>
                    <span class="h-[24px] inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 shadow-sm">
                        Đóng băng
                    </span>
                </div>
                <div class="mt-5 flex gap-3 text-sm">
                    <button class="border-0 bg-slate-50 text-slate-400 px-4 py-2 rounded-lg font-medium cursor-not-allowed flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">lock_clock</span> Tính năng sắp ra mắt
                    </button>
                </div>
            </div>
        </div>

        <!-- Theme: Ska Canvas -->
        <?php
        $theme_obj = wp_get_theme('ska-canvas');
        $theme_exists = $theme_obj->exists();
        $theme_active = (get_template() === 'ska-canvas' || get_stylesheet() === 'ska-canvas');

        // CSS Style theo trạng thái Theme
        $t_wrap_style = $theme_active ? 'module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group' : 'module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group opacity-75 grayscale-[40%] hover:grayscale-0 transition-all duration-500';
        $t_icon_style = $theme_active ? 'bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-600 border border-indigo-100 shadow-inner group-hover:scale-105 transition-transform duration-300' : 'bg-slate-100 text-slate-500 border-slate-200 shadow-inner group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-all duration-300';
        $t_title_style = $theme_active ? 'text-slate-900' : 'text-slate-800';
        ?>
        <div class="<?php echo esc_attr($t_wrap_style); ?>">
            <div class="absolute top-0 left-0 w-1.5 h-full <?php echo $theme_active ? 'bg-gradient-to-b from-indigo-400 to-purple-600' : 'bg-slate-300 group-hover:bg-indigo-400'; ?> transition-colors"></div>
            <?php if ($theme_active): ?>
                <div class="absolute top-0 right-0 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-[10px] font-bold px-4 py-1.5 rounded-bl-xl uppercase tracking-wider shadow-sm">
                    Active Theme
                </div>
            <?php endif; ?>
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 <?php echo esc_attr($t_icon_style); ?>">
                <span class="material-symbols-outlined text-[32px]">web</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="m-0 pt-0 pb-0 border-0 font-bold text-lg <?php echo esc_attr($t_title_style); ?>">Ska Canvas Theme</h3>
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed">Barebone WP Theme (Zero CSS). Loại bỏ 100% rác CSS mặc định của WordPress để nhường sân khấu cho JIT Compiler.</p>
                    </div>
                    <?php if (!$theme_active): ?>
                        <?php
                        $t_status_class = $theme_exists ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-rose-50 text-rose-600 border-rose-200';
                        $t_status_text = $theme_exists ? 'Chưa kích hoạt' : 'Chưa cài đặt';
                        ?>
                        <span class="h-[24px] inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold border shadow-sm <?php echo esc_attr($t_status_class); ?>">
                            <?php echo esc_html($t_status_text); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="mt-5 flex gap-4 text-sm">
                    <?php if ($theme_active): ?>
                        <a href="#" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:text-indigo-800 transition-colors bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg no-underline">
                            <span class="material-symbols-outlined text-[18px]">brush</span> Tùy biến Theme.json
                        </a>
                    <?php elseif ($theme_exists): ?>
                        <a href="<?php echo admin_url('themes.php'); ?>" class="inline-flex items-center gap-1 text-slate-700 font-semibold hover:text-indigo-700 transition-colors bg-slate-100 hover:bg-indigo-50 px-4 py-2 rounded-lg no-underline border border-slate-200 hover:border-indigo-200">
                            <span class="material-symbols-outlined text-[18px]">toggle_on</span> Kích hoạt trong Appearance
                        </a>
                    <?php else: ?>
                        <a href="https://ska.vn/canvas-theme" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:text-indigo-800 transition-colors bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg no-underline">
                            <span class="material-symbols-outlined text-[18px]">download</span> Tải xuống Base Theme
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function register_menu()
    {
        add_menu_page(
            __('Ska Ecosystem', 'ska-no-code-design'),
            __('Ska Ecosystem', 'ska-no-code-design'),
            'manage_options',
            'ska-system-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-building',
            3
        );
    }

    public function enqueue_assets($hook)
    {
        if ('toplevel_page_ska-system-dashboard' !== $hook) {
            return;
        }

        wp_enqueue_script('wp-util');

        wp_enqueue_script(
            'ska-system-ai',
            SKA_SYSTEM_FRAMEWORK_URL . 'assets/js/ai-blueprint.js',
            array('jquery', 'wp-util'),
            SKA_SYSTEM_FRAMEWORK,
            true
        );

        wp_localize_script('ska-system-ai', 'skaSystemObj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ska_ai_blueprint_nonce')
        ));
    }

    public function render_dashboard()
    {
        include SKA_SYSTEM_FRAMEWORK_PATH . '/views/dashboard-ui.php';
    }
}
