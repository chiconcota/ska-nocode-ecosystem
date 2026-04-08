<?php
defined( 'ABSPATH' ) || exit;

// 1. Cột Sidebar Trái (Danh sách Bảng Mẫu)
?>
<div class="w-64 bg-gray-50 border-r border-gray-200 flex flex-col shadow-sm z-20">
    
    <div class="p-4 border-b border-gray-200 bg-white flex items-center gap-2 shrink-0">
        <span class="dashicons dashicons-database text-emerald-500"></span>
        <h2 class="font-bold text-lg text-gray-800 tracking-tight">Ska Data</h2>
    </div>

    <div class="px-4 py-3 uppercase text-xs font-bold text-gray-400 tracking-wider flex items-center justify-between shrink-0">
        <span>Bảng Dữ Liệu</span>
        <button type="button" aria-label="Khởi tạo bảng mới" class="dashicons dashicons-plus hover:text-emerald-500 cursor-pointer transition text-gray-400 focus-visible:ring-2" style="background: none; border: none; padding: 0;" title="Khởi tạo bảng mới" onclick="document.getElementById('ska-create-table-modal').classList.remove('hidden'); document.getElementById('ska-new-table-name').focus();"></button>
    </div>

    <ul class="flex-1 overflow-y-auto w-full px-2 py-2">
        <?php if ( empty( $all_tables ) ) : ?>
            <li class="p-4 text-sm text-gray-500 text-center">Chưa có bảng nào. Hãy cài Schema trước.</li>
        <?php else : 
            // Xử lý phân nhóm Group tables
            $templates      = \Ska\Data\Core\Template_Registry::get_all_templates();
            $grouped_tables = array();
            $custom_tables  = array();
            $table_to_app   = array();
            
            $app_names = array(
                'ecommerce' => 'E-Commerce App',
                'lms'       => 'Hệ Thống LMS',
                'booking'   => 'App Đặt Lịch',
                'custom'    => 'Bảng Tùy Chỉnh'
            );
            $app_icons = array(
                'ecommerce' => 'dashicons-cart',
                'lms'       => 'dashicons-welcome-learn-more',
                'booking'   => 'dashicons-calendar-alt',
                'custom'    => 'dashicons-welcome-write-blog'
            );

            // Build lookup map
            foreach ( $templates as $key => $tpl ) {
                foreach ( $tpl['tables'] as $raw_name => $sql ) {
                    $table_to_app[ $wpdb->prefix . $raw_name ] = $key;
                }
            }

            foreach ( $all_tables as $table ) {
                $assigned_group = '';
                if ( isset( $all_dict[$table]['__table_info']['group'] ) ) {
                    $assigned_group = $all_dict[$table]['__table_info']['group'];
                }

                if ( ! empty( $assigned_group ) && $assigned_group !== 'custom' ) {
                    // Ưu tiên gom bảng theo Group mới gán từ Schema
                    $grouped_tables[ $assigned_group ][] = $table;
                } elseif ( isset( $table_to_app[ $table ] ) ) {
                    // Gom nhóm theo Template Hardcode truyền thống
                    $app_key = $table_to_app[ $table ];
                    $grouped_tables[ $app_key ][] = $table;
                } else {
                    // Unassigned Custom
                    $custom_tables[] = $table;
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
                    <li class="mb-0.5 ml-1 relative group">
                        <a href="<?php echo esc_url( $link ); ?>" class="flex items-center gap-2 px-2 py-1.5 text-sm font-medium rounded-md transition-colors <?php echo $is_active ? 'bg-emerald-100/50 text-emerald-700' : 'text-gray-600 hover:bg-gray-200/50 hover:text-gray-900 border border-transparent'; ?>">
                            <span class="dashicons <?php echo esc_attr( $custom_icon ); ?> opacity-70" style="font-size: 15px; margin-top: 1px;"></span>
                            <span class="truncate pr-4 flex-1" title="<?php echo esc_attr($table); ?>"><?php echo esc_html( $display_name ); ?></span>
                        </a>
                        
                        <!-- Kebab Menu (Table Actions) -->
                        <button type="button" aria-label="Tùy chọn bảng" aria-haspopup="true" class="dashicons dashicons-ellipsis opacity-0 group-hover:opacity-100 absolute right-2 top-1.5 text-gray-400 hover:text-gray-700 cursor-pointer z-10 pl-1 rounded pointer-events-auto focus:opacity-100 focus-visible:ring-2" style="font-size: 16px; margin-top: 2px; background: none; border: none; padding: 0;" onclick="document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.toggle('hidden')"></button>
                        <div id="dd-tbl-<?php echo esc_attr($table); ?>" class="hidden absolute top-7 left-10 w-48 bg-white rounded-md shadow-[0_10px_25px_rgba(0,0,0,0.1)] border border-gray-100 z-[60] text-gray-700 py-1 font-normal overflow-hidden animate-[pulse_0.1s_ease-out]">
                            <button onclick="skaOpenRenameTable('<?php echo esc_js($table); ?>', '<?php echo esc_js($display_name); ?>', '<?php echo esc_js($custom_icon); ?>', '<?php echo esc_js($group); ?>'); document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-edit text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Đổi Ký Danh Bảng
                            </button>
                            <div class="h-px bg-gray-100 my-0.5"></div>
                            <button onclick="skaOpenDeleteTable('<?php echo esc_js($table); ?>', '<?php echo esc_js($display_name); ?>'); document.getElementById('dd-tbl-<?php echo esc_attr($table); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 hover:text-red-700 flex items-center gap-2 transition-colors">
                                <span class="dashicons dashicons-trash text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Xóa Vĩnh Viễn
                            </button>
                        </div>
                    </li>
                    <?php
                }
            }
        ?>
            <!-- Khối bảng theo chuẩn Ứng Dụng Mẫu -->
            <?php foreach ( $grouped_tables as $app_key => $tables_in_group ) : ?>
                <li class="px-2 pt-2 pb-1 mt-2 mb-1 border-b border-gray-200">
                    <div class="flex items-center gap-1.5 font-bold text-[10px] uppercase text-gray-400 tracking-wider">
                        <span class="dashicons <?php echo esc_attr( $app_icons[ $app_key ] ); ?>" style="font-size: 14px; width: 14px; height: 14px; margin-top:-2px"></span>
                        <?php echo esc_html( $app_names[ $app_key ] ); ?>
                    </div>
                </li>
                <?php foreach ( $tables_in_group as $table ) : 
                    $clean_name = str_replace( $wpdb->prefix . 'ska_data_', '', $table );
                    $display_name = ucfirst( $clean_name );
                    if ( $clean_name === 'custom' ) $display_name = 'Bảng Trống 1';

                    ska_render_sidebar_li( $table, $current_table, $wpdb, $all_dict, $display_name );
                endforeach; ?>
            <?php endforeach; ?>

            <!-- Khối bảng Custom không thuộc App Nào -->
            <?php if ( ! empty( $custom_tables ) ) : ?>
                <li class="px-2 pt-2 pb-1 mt-3 mb-1 border-b border-gray-200">
                    <div class="flex items-center gap-1.5 font-bold text-[10px] uppercase text-gray-400 tracking-wider">
                        <span class="dashicons dashicons-category" style="font-size: 14px; width: 14px; height: 14px; margin-top:-2px"></span>
                        Bảng Tùy Biến (Custom)
                    </div>
                </li>
                <?php foreach ( $custom_tables as $table ) : 
                    ska_render_sidebar_li( $table, $current_table, $wpdb, $all_dict, '' );
                endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    </ul>

    <div class="p-4 border-t border-gray-200 shrink-0 bg-white">
        <a href="<?php echo admin_url('admin.php?page=ska-data-pro'); ?>" class="flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-500 transition font-medium">
            <span class="dashicons dashicons-arrow-left-alt2"></span> Library Dashboard
        </a>
    </div>
</div>
