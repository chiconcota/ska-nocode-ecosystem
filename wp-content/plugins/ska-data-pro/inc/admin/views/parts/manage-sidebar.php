<?php
defined( 'ABSPATH' ) || exit;

// 1. Cột Sidebar Trái (Danh sách Bảng Mẫu)
?>
<div class="w-64 bg-gray-50 border-r border-gray-200 flex flex-col shadow-sm z-20">
    
    <div class="p-4 border-b border-gray-200 bg-white flex items-center gap-2 shrink-0">
        <span class="dashicons dashicons-database text-emerald-500"></span>
        <h2 class="font-bold text-lg text-gray-800 tracking-tight">Ska Data</h2>
    </div>

    <div class="px-3 py-3 uppercase text-[11px] font-bold text-gray-400 tracking-wider flex items-center justify-between shrink-0 bg-gray-100/50">
        <span><?php esc_html_e( 'Database space', 'ska-data-pro' ); ?></span>
        <div class="flex gap-2">
            <span class="dashicons dashicons-upload hover:text-indigo-500 cursor-pointer transition text-gray-400" title="<?php echo esc_attr__( 'Import Workspace from JSON file', 'ska-data-pro' ); ?>" onclick="document.getElementById('ska-import-app-modal').classList.remove('hidden');" style="font-size: 16px; width: 16px; height: 16px;"></span>
            <span class="dashicons dashicons-portfolio hover:text-indigo-500 cursor-pointer transition text-gray-400" title="<?php echo esc_attr__( 'Create a new Workspace', 'ska-data-pro' ); ?>" onclick="document.getElementById('ska-create-app-modal').classList.remove('hidden'); document.getElementById('ska-new-app-name').focus();" style="font-size: 16px; width: 16px; height: 16px;"></span>
            <span class="dashicons dashicons-plus hover:text-emerald-500 cursor-pointer transition text-gray-400" title="<?php echo esc_attr__( 'Initialize the sample table', 'ska-data-pro' ); ?>" onclick="document.getElementById('ska-create-table-modal').classList.remove('hidden'); document.getElementById('ska-new-table-name').focus();" style="font-size: 16px; width: 16px; height: 16px;"></span>
        </div>
    </div>

    <ul class="flex-1 overflow-y-auto w-full px-2 py-2">
        <?php if ( empty( $all_tables ) ) : ?>
            <li class="p-4 text-sm text-gray-500 text-center"><?php esc_html_e( 'There are no tables yet. ', 'ska-data-pro' ); ?></li>
        <?php else : 
            // Xử lý phân nhóm Group tables
            $apps = \Ska\Data\Core\App_Manager::get_apps();
            $grouped_tables = array();
            
            foreach ( $apps as $app_key => $app_data ) {
                $grouped_tables[ $app_key ] = array();
            }

            foreach ( $all_tables as $table ) {
                $assigned_app = \Ska\Data\Core\App_Manager::UNCATEGORIZED_APP;
                if ( isset( $all_dict[$table]['__table_info']['app_id'] ) ) {
                    $assigned_app = $all_dict[$table]['__table_info']['app_id'];
                }

                if ( ! isset( $grouped_tables[ $assigned_app ] ) ) {
                    // Fallback an toàn nếu app bị xóa mồ côi
                    $grouped_tables[ \Ska\Data\Core\App_Manager::UNCATEGORIZED_APP ][] = $table;
                } else {
                    $grouped_tables[ $assigned_app ][] = $table;
                }
            }

            // Hàm cục bộ vẽ L.I Bảng (Tái sử dụng code)
            if ( ! function_exists( 'ska_render_sidebar_li' ) ) {
                function ska_render_sidebar_li( $table, $current_table, $wpdb, $all_dict, $default_name = '' ) {
                    $is_active  = ( $table === $current_table );
                    $link       = admin_url( 'admin.php?page=ska-data-pro-manage&table=' . urlencode( $table ) );
                    $clean_name = str_replace( $wpdb->prefix . 'ska_data_', '', $table );
                    
                    $display_name = $default_name ?: ucfirst( $clean_name );
                    $custom_icon  = 'dashicons-media-spreadsheet';
                    $group        = 'custom';

                    if ( isset( $all_dict[$table]['__table_info']['name'] ) ) {
                        $display_name = $all_dict[$table]['__table_info']['name'];
                    }
                    if ( isset( $all_dict[$table]['__table_info']['icon'] ) ) {
                        $custom_icon = $all_dict[$table]['__table_info']['icon'];
                    }
                    if ( isset( $all_dict[$table]['__table_info']['group'] ) ) {
                        $group = $all_dict[$table]['__table_info']['group'];
                    }
                    ?>
                    <?php 
                    $is_protected_tbl = \Ska\Data\Core\Database_Engine::get_instance()->is_table_protected( $table );
                    ?>
                    <li class="mb-0.5 ml-1 relative group">
                        <a href="<?php echo esc_url( $link ); ?>" class="flex items-center gap-2 px-2 py-1.5 text-sm font-medium rounded-md transition-colors <?php echo $is_active ? 'bg-emerald-100/50 text-emerald-700' : 'text-gray-600 hover:bg-gray-200/50 hover:text-gray-900 border border-transparent'; ?>">
                            <span class="dashicons <?php echo esc_attr( $custom_icon ); ?> opacity-70" style="font-size: 15px; margin-top: 1px;"></span>
                            <span class="truncate pr-4 flex-1" title="<?php echo esc_attr($table); ?>"><?php echo esc_html( $display_name ); ?></span>
                        </a>
                        
                        <!-- Kebab Menu (Table Actions) -->
                        <?php if ( ! $is_protected_tbl ) : ?>
                        <span class="dashicons dashicons-ellipsis opacity-0 group-hover:opacity-100 absolute right-2 top-1.5 text-gray-400 hover:text-gray-700 cursor-pointer z-10 pl-1 rounded pointer-events-auto" style="font-size: 16px; margin-top: 2px;" onclick="document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.toggle('hidden')"></span>
                        <div id="dd-tbl-<?php echo esc_attr($table); ?>" class="hidden absolute top-7 left-10 w-48 bg-white rounded-md shadow-[0_10px_25px_rgba(0,0,0,0.1)] border border-gray-100 z-[60] text-gray-700 py-1 font-normal overflow-hidden animate-[pulse_0.1s_ease-out]">
                            <button onclick="skaOpenRenameTable('<?php echo esc_js($table); ?>', '<?php echo esc_js($display_name); ?>', '<?php echo esc_js($custom_icon); ?>', '<?php echo esc_js(isset($all_dict[$table]['__table_info']['app_id']) ? $all_dict[$table]['__table_info']['app_id'] : 'uncategorized'); ?>'); document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-edit text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Đổi Thuộc Tính Bảng
                            </button>
                            <div class="h-px bg-gray-100 my-0.5"></div>
                            <button onclick="skaOpenDeleteTable('<?php echo esc_js($table); ?>', '<?php echo esc_js($display_name); ?>'); document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 hover:text-red-700 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-trash text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Xóa Vĩnh Viễn Bảng
                            </button>
                        </div>
                        <?php endif; ?>
                    </li>
                    <?php
                }
            }
        ?>
            <!-- Khối bảng theo chuẩn Ứng Dụng Mẫu -->
            <?php foreach ( $grouped_tables as $app_key => $tables_in_group ) : 
                if ( ! isset( $apps[ $app_key ] ) ) continue;
                $app_name = $apps[ $app_key ]['name'];
                $app_icon = $apps[ $app_key ]['icon'];
            ?>
                <li class="px-2 pt-3 pb-1 mt-2 mb-1 border-b border-gray-200 relative group">
                    <div class="flex items-center gap-1.5 font-bold text-[10px] uppercase text-gray-500 tracking-wider">
                        <span class="dashicons <?php echo esc_attr( $app_icon ); ?>" style="font-size: 14px; width: 14px; height: 14px; margin-top:-2px"></span>
                        <span class="flex-1 truncate truncate" title="<?php echo esc_attr($app_name); ?>"><?php echo esc_html( $app_name ); ?></span>
                        
                        <?php if ( $app_key !== \Ska\Data\Core\App_Manager::UNCATEGORIZED_APP ) : ?>
                        <!-- Icon setting App -->
                        <span class="dashicons dashicons-admin-generic opacity-0 group-hover:opacity-100 cursor-pointer text-gray-400 hover:text-indigo-500 transition ml-auto" style="font-size: 14px; width: 14px; height: 14px; margin-top:-2px" title="<?php echo esc_attr__( 'Space Setting', 'ska-data-pro' ); ?>" onclick="document.getElementById('dd-app-<?php echo esc_attr($app_key); ?>').classList.toggle('hidden')"></span>
                        <div id="dd-app-<?php echo esc_attr($app_key); ?>" class="hidden absolute top-8 right-2 w-48 bg-white rounded-md shadow-[0_10px_25px_rgba(0,0,0,0.1)] border border-gray-100 z-[60] text-gray-700 py-1 font-normal overflow-hidden animate-[pulse_0.1s_ease-out] lowercase normal-case">
                            <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=ska_data_export_app&app_id=' . $app_key . '&security=' . wp_create_nonce( 'ska_data_nonce' ) ) ); ?>" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-download text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Xuất Blueprint (JSON)
                            </a>
                            <div class="h-px bg-gray-100 my-0.5"></div>
                            <button onclick="skaOpenRenameApp('<?php echo esc_js($app_key); ?>', '<?php echo esc_js($app_name); ?>', '<?php echo esc_js($app_icon); ?>'); document.getElementById('dd-app-<?php echo esc_attr($app_key); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-edit text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Đổi Tên Không Gian
                            </button>
                            <div class="h-px bg-gray-100 my-0.5"></div>
                            <button onclick="skaOpenDeleteApp('<?php echo esc_js($app_key); ?>', '<?php echo esc_js($app_name); ?>'); document.getElementById('dd-app-<?php echo esc_attr($app_key); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 hover:text-red-700 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-trash text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Giải Tán (Xóa App)
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                <?php foreach ( $tables_in_group as $table ) : 
                    $clean_name = str_replace( $wpdb->prefix . 'ska_data_', '', $table );
                    $display_name = ucfirst( $clean_name );

                    ska_render_sidebar_li( $table, $current_table, $wpdb, $all_dict, $display_name );
                endforeach; ?>
            <?php endforeach; ?>


        <?php endif; ?>
    </ul>

    <div class="p-4 border-t border-gray-200 shrink-0 bg-white">
        <a href="<?php echo admin_url('admin.php?page=ska-data-pro'); ?>" class="flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-500 transition font-medium">
            <span class="dashicons dashicons-arrow-left-alt2"></span> Library Dashboard
        </a>
    </div>
</div>
