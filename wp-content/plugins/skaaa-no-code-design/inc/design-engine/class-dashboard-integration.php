<?php
defined( 'ABSPATH' ) || exit;

class Skaaa_Design_Dashboard_Integration {
    public static function init() {
        add_action( 'skaaa_system_dashboard_modules', [ __CLASS__, 'render_module_card' ] );
        add_action( 'skaaa_system_dashboard_settings', [ __CLASS__, 'render_settings' ] );
    }

    public static function render_module_card() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $data_pro_active     = is_plugin_active( 'skaaa-data-pro/skaaa-data-pro.php' );
        $logic_engine_active = is_plugin_active( 'skaaa-logic-engine/skaaa-logic-engine.php' );
        $can_use_design      = $data_pro_active && $logic_engine_active;

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
                        <h3 class="m-0 border-0 pt-0 pb-0 font-bold text-slate-900 text-base">Skaaa No-Code Design</h3>
                        <p class="text-sm text-slate-500 mt-1"><?php esc_html_e( 'Provides Base Atomic Blocks, Tailwind v4 JIT Compiler and html2tailwind converter. ', 'skaaa-no-code-design' ); ?></p>
                    </div>
                    <span class="h-[22px] inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <?php esc_html_e( 'Active', 'skaaa-no-code-design' ); ?>
                    </span>
                </div>
                <div class="mt-4 flex gap-3 text-sm">
                    <?php if ( $can_use_design ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=skaaa-design-workspace' ) ); ?>" class="text-indigo-600 font-medium hover:text-indigo-800 hover:underline"><?php esc_html_e( 'Open Designer', 'skaaa-no-code-design' ); ?></a>
                        <span class="text-slate-300">|</span>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=skaaa-design-tokens' ) ); ?>" class="text-slate-600 hover:text-slate-900"><?php esc_html_e( 'Brand, Font & Theme Options', 'skaaa-no-code-design' ); ?></a>
                        <span class="text-slate-300">|</span>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=skaaa-theme-builder' ) ); ?>" class="text-indigo-600 font-medium hover:text-indigo-800 hover:underline"><?php esc_html_e( 'Theme Builder', 'skaaa-no-code-design' ); ?></a>
                    <?php else : ?>
                        <span class="text-slate-400 font-medium flex items-center gap-1 cursor-not-allowed" title="<?php echo esc_attr( __( 'Requires activation of Skaaa Data Pro & Skaaa Logic Engine', 'skaaa-no-code-design' ) ); ?>">
                            <span class="material-symbols-outlined text-[16px]">lock</span> <?php esc_html_e( 'Open Designer', 'skaaa-no-code-design' ); ?>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-400 flex items-center gap-1 cursor-not-allowed" title="<?php echo esc_attr( __( 'Requires activation of Skaaa Data Pro & Skaaa Logic Engine', 'skaaa-no-code-design' ) ); ?>">
                            <span class="material-symbols-outlined text-[16px]">lock</span> <?php esc_html_e( 'Brand, Font & Theme Options', 'skaaa-no-code-design' ); ?>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-400 flex items-center gap-1 cursor-not-allowed" title="<?php echo esc_attr( __( 'Requires activation of Skaaa Data Pro & Skaaa Logic Engine', 'skaaa-no-code-design' ) ); ?>">
                            <span class="material-symbols-outlined text-[16px]">lock</span> <?php esc_html_e( 'Theme Builder', 'skaaa-no-code-design' ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function render_settings() {
        $dev_mode = get_option('skaaa_system_dev_mode', '1');
        ?>
        <style>
            /* Reset WP Admin native checkbox styles inside our toggle */
            .skaaa-framework-wrap input[type="checkbox"].toggle-checkbox {
                -webkit-appearance: none !important;
                appearance: none !important;
                background-image: none !important;
            }
            .skaaa-framework-wrap input[type="checkbox"].toggle-checkbox::before {
                content: none !important;
                display: none !important;
            }
            .skaaa-framework-wrap input[type="checkbox"].toggle-checkbox:checked::before {
                content: none !important;
                display: none !important;
            }
        </style>
        <!-- Toggle: Dev Mode -->
        <div class="flex items-start justify-between">
            <div class="pr-4">
                <h4 class="m-0 border-0 pt-0 pb-0 text-sm font-semibold text-slate-800"><?php esc_html_e( 'Dev Mode', 'skaaa-no-code-design' ); ?></h4>
                <p class="text-xs text-slate-500 mt-1 block"><?php esc_html_e( 'When enabled, displays a Red Badge when frontend errors occur (duplicate ID, AST error) instead of failing silently to preserve layout.', 'skaaa-no-code-design' ); ?></p>
            </div>
            <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in flex-shrink-0 mt-1">
                <input type="checkbox" name="skaaa_system_dev_mode" id="skaaa_system_dev_mode" value="1" <?php checked($dev_mode, '1'); ?>
                    class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-300 transition-all z-10" />
                <label for="skaaa_system_dev_mode" class="toggle-label block overflow-hidden h-5 rounded-full bg-slate-300 cursor-pointer transition-colors"></label>
            </div>
        </div>
        <input type="hidden" name="skaaa_settings_nonce" value="<?php echo esc_attr( wp_create_nonce( 'skaaa_save_settings' ) ); ?>">
        <?php
    }
}
