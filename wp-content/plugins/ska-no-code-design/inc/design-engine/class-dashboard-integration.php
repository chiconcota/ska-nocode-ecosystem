<?php
defined( 'ABSPATH' ) || exit;

class Ska_Design_Dashboard_Integration {
    public static function init() {
        add_action( 'ska_system_dashboard_modules', [ __CLASS__, 'render_module_card' ] );
        add_action( 'ska_system_dashboard_settings', [ __CLASS__, 'render_settings' ] );
    }

    public static function render_module_card() {
        ?>
        <!-- Module: Design Engine -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row gap-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
            <div class="w-14 h-14 bg-pink-50 text-pink-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-pink-100">
                <span class="material-symbols-outlined text-[28px]">brush</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="m-0 border-0 pt-0 pb-0 font-bold text-slate-900 text-base">Ska No-Code Design</h3>
                        <p class="text-sm text-slate-500 mt-1">Cung cấp Base Atomic Blocks, Tailwind v4 JIT Compiler và bộ chuyển đổi html2tailwind. Trái tim của giao diện.</p>
                    </div>
                    <span class="h-[22px] inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Đang hoạt động
                    </span>
                </div>
                <div class="mt-4 flex gap-3 text-sm">
                    <a href="#" class="text-indigo-600 font-medium hover:text-indigo-800 hover:underline">Mở Trình thiết kế</a>
                    <span class="text-slate-300">|</span>
                    <a href="#" class="text-slate-600 hover:text-slate-900">Brand, Font & Theme Options</a>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_settings() {
        $dev_mode = get_option('ska_system_dev_mode', '1');
        ?>
        <style>
            /* Reset WP Admin native checkbox styles inside our toggle */
            .ska-framework-wrap input[type="checkbox"].toggle-checkbox {
                -webkit-appearance: none !important;
                appearance: none !important;
                background-image: none !important;
            }
            .ska-framework-wrap input[type="checkbox"].toggle-checkbox::before {
                content: none !important;
                display: none !important;
            }
            .ska-framework-wrap input[type="checkbox"].toggle-checkbox:checked::before {
                content: none !important;
                display: none !important;
            }
        </style>
        <!-- Toggle: Dev Mode -->
        <div class="flex items-start justify-between">
            <div class="pr-4">
                <h4 class="m-0 border-0 pt-0 pb-0 text-sm font-semibold text-slate-800">Chế độ Nhà phát triển (Dev Mode)</h4>
                <p class="text-xs text-slate-500 mt-1 block">Khi bật, hiển thị Badge Đỏ khi xảy ra lỗi
                    Frontend (Trùng lặp ID, sai AST) thay vì giấu đi để giữ Layout.</p>
            </div>
            <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in flex-shrink-0 mt-1">
                <input type="checkbox" name="ska_system_dev_mode" id="ska_system_dev_mode" value="1" <?php checked($dev_mode, '1'); ?>
                    class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-300 transition-all z-10" />
                <label for="ska_system_dev_mode" class="toggle-label block overflow-hidden h-5 rounded-full bg-slate-300 cursor-pointer transition-colors"></label>
            </div>
        </div>
        <input type="hidden" name="ska_settings_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ska_save_settings' ) ); ?>">
        <?php
    }
}
