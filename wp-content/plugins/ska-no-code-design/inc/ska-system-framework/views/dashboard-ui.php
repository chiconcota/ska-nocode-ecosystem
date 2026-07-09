<?php
/**
 * View: Dashboard UI
 */
defined( 'ABSPATH' ) || exit;
?>

<!-- Tailwind CSS & Icons (Dành cho Dashboard nội bộ) -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Premium Reset and Custom Properties */
    .ska-framework-wrap {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
    }
    .ska-framework-wrap .material-symbols-outlined {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .ska-framework-wrap .toggle-checkbox:checked { right: 0; border-color: #4f46e5; }
    .ska-framework-wrap .toggle-checkbox:checked+.toggle-label { background-color: #4f46e5; }
    
    /* Scrollbar Styling */
    .ska-framework-wrap ::-webkit-scrollbar { width: 6px; }
    .ska-framework-wrap ::-webkit-scrollbar-track { background: transparent; }
    .ska-framework-wrap ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .ska-framework-wrap ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* WordPress admin notice fix */
    #wpcontent { padding-left: 0; }

    /* Module Cards Enhancements (Targets injected cards) */
    .ska-framework-wrap .module-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
    }
    .ska-framework-wrap .module-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.05), 0 4px 10px -4px rgba(0, 0, 0, 0.03);
        border-color: rgba(99, 102, 241, 0.3);
    }
    .ska-framework-wrap .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.39);
        transition: all 0.2s ease;
    }
    .ska-framework-wrap .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }
    
    /* Header Animation */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }
    .ska-header-icon {
        animation: float 6s ease-in-out infinite;
    }
</style>

<div class="wrap ska-framework-wrap p-4 md:p-8 text-slate-800" style="margin:0;">
    <div class="max-w-6xl mx-auto space-y-8 relative">
        
        <!-- Background decorative elements -->
        <div class="absolute top-0 left-10 w-64 h-64 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 pointer-events-none"></div>
        <div class="absolute top-20 right-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 pointer-events-none"></div>

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10 bg-white/60 backdrop-blur-md p-6 rounded-2xl border border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="ska-header-icon w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 border border-white/20">
                    <span class="material-symbols-outlined text-[32px]">dashboard_customize</span>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-700 m-0 p-0 leading-tight"><?php esc_html_e( 'Ska Ecosystem', 'ska-no-code-design' ); ?></h1>
                    <p class="text-sm font-medium text-slate-500 mt-1"><?php esc_html_e( 'Central management of Modules and Core Configuration', 'ska-no-code-design' ); ?></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold border border-indigo-100 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <?php esc_html_e( 'System Stable', 'ska-no-code-design' ); ?>
                </span>
            </div>
        </header>

        <?php
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $is_data_active = is_plugin_active('ska-data-pro/ska-data-pro.php');
        $is_logic_active = is_plugin_active('ska-logic-engine/ska-logic-engine.php');
        
        if ( ! $is_data_active || ! $is_logic_active ) :
        ?>
        <!-- Ecosystem Warning -->
        <div class="bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 p-5 rounded-2xl shadow-sm flex items-start gap-4 relative overflow-hidden z-10">
            <div class="absolute top-0 left-0 w-1 h-full bg-rose-500"></div>
            <div class="w-12 h-12 bg-white text-rose-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border border-rose-100">
                <span class="material-symbols-outlined text-[28px]">error</span>
            </div>
            <div>
                <h3 class="text-rose-800 font-bold m-0 border-0 p-0 text-base"><?php esc_html_e( 'Missing Core Module!', 'ska-no-code-design' ); ?></h3>
                <p class="text-rose-700 text-sm mt-1.5 mb-0 leading-relaxed"><?php printf( __( 'The ecosystem is running in a limited mode. Please install and activate %1$s & %2$s to unlock infinite No-code power.', 'ska-no-code-design' ), '<strong>Ska Data Pro</strong>', '<strong>Ska Logic Engine</strong>' ); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grid 2 Cột: Modules (Trái) & Settings (Phải) -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 relative z-10">

            <!-- CỘT TRÁI: QUẢN LÝ MODULES VÀ THEME -->
            <div class="xl:col-span-2 space-y-6">

                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 m-0 border-0 pb-0">
                        <span class="material-symbols-outlined text-indigo-500 bg-indigo-50 p-1.5 rounded-lg">extension</span>
                        <?php esc_html_e( 'Installed Modules', 'ska-no-code-design' ); ?>
                    </h2>
                    <span class="text-xs font-bold bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-800 border border-emerald-200/50 px-3 py-1 rounded-full shadow-sm"><?php esc_html_e( 'Synced', 'ska-no-code-design' ); ?></span>
                </div>

                <div class="space-y-4">
                    <?php 
                    /**
                     * Hook để các plugin khác (Data Pro, Logic Engine, Design Engine) inject card module của họ vào đây.
                     */
                    do_action( 'ska_system_dashboard_modules' ); 
                    ?>
                </div>
            </div>

            <!-- CỘT PHẢI: GLOBAL SETTINGS & DANGER ZONE -->
            <div class="space-y-6">

                <!-- Global Cài Đặt (Global Settings) -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden transition-all hover:shadow-md">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="m-0 font-bold text-slate-800 flex items-center gap-2 text-base">
                            <span class="material-symbols-outlined text-indigo-500">settings_applications</span>
                            <?php esc_html_e( 'System Settings', 'ska-no-code-design' ); ?>
                        </h3>
                    </div>
                    <form id="skaSystemSettingsForm" method="post" action="" class="p-5 space-y-5" style="margin:0;">
                        <?php if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' ) : ?>
                            <div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl text-sm border border-emerald-200 flex items-center gap-2 shadow-inner">
                                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                <span class="font-medium"><?php esc_html_e( 'Updated settings.', 'ska-no-code-design' ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php 
                        /**
                         * Hook để các plugin tự đăng ký setting vào đây
                         */
                        do_action( 'ska_system_dashboard_settings' ); 
                        ?>
                        
                        <div class="pt-5 border-t border-slate-100">
                            <button type="submit" class="w-full py-2.5 border-0 btn-primary text-white rounded-xl text-sm font-bold uppercase tracking-wider cursor-pointer flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <?php esc_html_e( 'Save Settings', 'ska-no-code-design' ); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-gradient-to-br from-white to-rose-50/50 rounded-2xl border border-rose-200 shadow-sm p-5 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-rose-100 opacity-50 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                        <span class="material-symbols-outlined text-[100px]">warning</span>
                    </div>
                    <div class="relative z-10">
                        <h3 class="m-0 border-0 pb-0 pt-0 font-bold text-rose-800 flex items-center gap-2 mb-2 text-base">
                            <span class="material-symbols-outlined text-rose-500">gavel</span>
                            <?php esc_html_e( 'Danger Zone', 'ska-no-code-design' ); ?>
                        </h3>
                        <p class="text-xs text-rose-600/80 mb-5 font-medium"><?php esc_html_e( 'The operations below deeply impact Blueprint and RAM/Cache.', 'ska-no-code-design' ); ?></p>
                        
                        <div class="space-y-3">
                            <button class="w-full py-2.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 rounded-xl text-sm font-bold transition-all cursor-pointer shadow-sm flex justify-center items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                                <?php esc_html_e( 'Clear Context Cache', 'ska-no-code-design' ); ?>
                            </button>
                            <button class="w-full py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 rounded-xl text-sm font-bold transition-all cursor-pointer shadow-sm flex justify-center items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">cleaning_services</span>
                                <?php esc_html_e( 'Flush JIT CSS', 'ska-no-code-design' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- KHU VỰC TIỆN ÍCH MỞ RỘNG (EXTENSIONS) -->
        <div class="pt-8 pb-12 relative z-10">
            <div class="flex items-center justify-between border-b border-slate-200/60 pb-3 mb-6">
                <h2 class="m-0 border-0 pb-0 pt-0 text-xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-500 bg-purple-50 p-1.5 rounded-lg">grid_view</span>
                    <?php esc_html_e( 'Extensions', 'ska-no-code-design' ); ?>
                </h2>
            </div>

            <!-- Grid Extension Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                <?php 
                /**
                 * Hook để các tiện ích inject card
                 */
                do_action( 'ska_system_dashboard_extensions' ); 

                // Lặp render các dynamic pluggable custom nodes
                $ska_custom_nodes = \Ska_System_Framework\Framework_UI::get_custom_pluggable_nodes();
                if ( ! empty( $ska_custom_nodes ) ) {
                    foreach ( $ska_custom_nodes as $node_type => $addon ) {
                        $is_active = $addon['active'];
                        $addon_class = $is_active ? 'border-indigo-200 shadow-md' : 'opacity-70 grayscale-[30%]';
                        ?>
                        <div class="bg-white/90 backdrop-blur rounded-2xl border p-5 transition-all duration-300 group relative overflow-hidden flex flex-col justify-between <?php echo esc_attr( $addon_class ); ?>" id="addon-card-<?php echo esc_attr( $node_type ); ?>">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-600 rounded-xl flex items-center justify-center border border-indigo-100 shadow-sm relative z-10 group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-[20px]">extension</span>
                                    </div>
                                    
                                    <!-- Nút Xóa (Thùng rác) ở góc để xóa plugin addon vật lý -->
                                    <button onclick="skaDeleteNodePlugin('<?php echo esc_js( $node_type ); ?>')" class="text-slate-400 hover:text-rose-600 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-rose-50 transition-colors flex items-center justify-center" title="<?php esc_attr_e( 'Uninstall Extension Plugin', 'ska-no-code-design' ); ?>">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                                
                                <h3 class="font-bold text-base m-0 p-0 border-0 mb-1 text-slate-800"><?php echo esc_html( $addon['label'] ); ?></h3>
                                <p class="text-xs text-slate-500 m-0 leading-relaxed min-h-[48px] line-clamp-3 mb-4"><?php echo esc_html( $addon['description'] ); ?></p>
                            </div>
                            
                            <!-- Toggle switch Bật / Tắt ở chân Card -->
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100/60 mt-auto">
                                <span class="text-xs font-semibold <?php echo $is_active ? 'text-indigo-600' : 'text-slate-400'; ?>" id="addon-status-text-<?php echo esc_attr( $node_type ); ?>">
                                    <?php echo $is_active ? __( 'Active', 'ska-no-code-design' ) : __( 'Disabled', 'ska-no-code-design' ); ?>
                                </span>
                                
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" <?php checked( $is_active ); ?> onchange="skaToggleNodeStatus('<?php echo esc_js( $node_type ); ?>', this)" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <?php if ( ! has_action( 'ska_system_dashboard_extensions' ) && empty( $ska_custom_nodes ) ) : ?>
                <div class="bg-white/60 backdrop-blur border border-dashed border-slate-300 rounded-2xl text-center py-12 text-slate-500 text-sm font-medium">
                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">extension_off</span>
                    <?php esc_html_e( 'No extensions have been installed yet.', 'ska-no-code-design' ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Script JS xử lý AJAX cho Extensions (Node Soft-Toggle & Plugin Deletion) -->
        <script>
        function skaToggleNodeStatus(nodeType, toggleEl) {
            const cardEl = document.getElementById('addon-card-' + nodeType);
            const statusTextEl = document.getElementById('addon-status-text-' + nodeType);
            
            toggleEl.disabled = true;
            const isActiveVal = toggleEl.checked ? 1 : 0;
            
            jQuery.post(skaSystemObj.ajax_url, {
                action: 'ska_system_toggle_node_status',
                node_type: nodeType,
                active: isActiveVal,
                nonce: skaSystemObj.addon_nonce
            }, function(response) {
                toggleEl.disabled = false;
                if (response.success) {
                    const is_active = response.data.status === 'active';
                    toggleEl.checked = is_active;
                    
                    if (is_active) {
                        cardEl.classList.remove('opacity-70', 'grayscale-[30%]');
                        cardEl.classList.add('border-indigo-200', 'shadow-md');
                        statusTextEl.textContent = '<?php echo esc_js( __( 'Active', 'ska-no-code-design' ) ); ?>';
                        statusTextEl.className = 'text-xs font-semibold text-indigo-600';
                    } else {
                        cardEl.classList.add('opacity-70', 'grayscale-[30%]');
                        cardEl.classList.remove('border-indigo-200', 'shadow-md');
                        statusTextEl.textContent = '<?php echo esc_js( __( 'Disabled', 'ska-no-code-design' ) ); ?>';
                        statusTextEl.className = 'text-xs font-semibold text-slate-400';
                    }
                } else {
                    alert(response.data.message || 'Error toggling node');
                    toggleEl.checked = !toggleEl.checked;
                }
            }).fail(function() {
                toggleEl.disabled = false;
                alert('Network error. Failed to toggle node.');
                toggleEl.checked = !toggleEl.checked;
            });
        }

        function skaDeleteNodePlugin(nodeType) {
            if (!confirm('<?php echo esc_js( __( 'Are you absolutely sure you want to delete this extension? This will delete all physical files of the extension and CANNOT be undone! Type CONFIRM to delete.', 'ska-no-code-design' ) ); ?>')) {
                return;
            }

            const inputConfirm = prompt('<?php echo esc_js( __( 'Type CONFIRM to execute deletion:', 'ska-no-code-design' ) ); ?>');
            if (inputConfirm !== 'CONFIRM') {
                alert('<?php echo esc_js( __( 'Deletion cancelled. Confirmation text did not match.', 'ska-no-code-design' ) ); ?>');
                return;
            }

            const cardId = 'addon-card-' + nodeType;
            const cardEl = document.getElementById(cardId);
            
            cardEl.style.opacity = '0.5';
            
            jQuery.post(skaSystemObj.ajax_url, {
                action: 'ska_system_delete_node_plugin',
                node_type: nodeType,
                nonce: skaSystemObj.addon_nonce
            }, function(response) {
                if (response.success) {
                    jQuery('#' + cardId).fadeOut(300, function() {
                        jQuery(this).remove();
                        // Tải lại trang nếu không còn card nào để hiển thị blank state
                        if (jQuery('.grid-cols-1.sm\\:grid-cols-2 .bg-white\\/90').length === 0) {
                            window.location.reload();
                        }
                    });
                } else {
                    cardEl.style.opacity = '1';
                    alert(response.data.message || 'Error deleting addon');
                }
            }).fail(function() {
                cardEl.style.opacity = '1';
                alert('Network error. Failed to delete addon.');
            });
        }
        </script>
    </div>
</div>
