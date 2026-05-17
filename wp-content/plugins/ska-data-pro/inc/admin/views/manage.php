<?php
defined( 'ABSPATH' ) || exit;

use Ska\Data\Core\Data_Fetcher;

// 1. Lấy dữ liệu thông qua Data_Fetcher Helper
$all_tables = Data_Fetcher::get_all_tables();
$current_table = isset( $_GET['table'] ) ? sanitize_text_field( wp_unslash( $_GET['table'] ) ) : '';

if ( empty( $current_table ) && ! empty( $all_tables ) ) {
	$current_table = $all_tables[0];
}

// Lựa chọn Cột và Dữ Liệu
$columns = array();
$rows    = array();

if ( ! empty( $current_table ) ) {
    $columns = Data_Fetcher::get_table_columns( $current_table );
    
    // Thu thập tham số Query từ URL
    $query_args = array();
    if ( isset( $_GET['filter_field'] ) ) $query_args['filter_field'] = sanitize_text_field( $_GET['filter_field'] );
    if ( isset( $_GET['filter_val'] ) )   $query_args['filter_val']   = sanitize_text_field( $_GET['filter_val'] );
    if ( isset( $_GET['filter_op'] ) )    $query_args['filter_op']    = sanitize_text_field( $_GET['filter_op'] );
    if ( isset( $_GET['group_by'] ) )     $query_args['group_by']     = sanitize_text_field( $_GET['group_by'] );
    if ( isset( $_GET['orderby'] ) )      $query_args['orderby']      = sanitize_text_field( $_GET['orderby'] );
    if ( isset( $_GET['order'] ) )        $query_args['order']        = sanitize_text_field( $_GET['order'] );

    $rows    = Data_Fetcher::get_table_rows( $current_table, $query_args );
}

// Lược bỏ prefix để hiển thị tên thân thiện trên Header
global $wpdb;
$friendly_name = str_replace( $wpdb->prefix . 'ska_data_', '', $current_table );
$friendly_name = ucwords( str_replace( '_', ' ', $friendly_name ) );

// Nạp cuốn Từ Điển (Dictionary) Ánh Xạ Tên Tiếng Việt của Bảng Này
$all_dict    = get_option('ska_data_dictionary', array());
$table_dict  = isset($all_dict[$current_table]) ? $all_dict[$current_table] : array();
?>
<!-- Tích hợp Tailwind CDN để Code UI Grid mô phỏng Airtable -->
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Reset WP content box padding để giao diện Full Width (tràn sang trái) */
#wpcontent { padding-left: 0; padding-bottom: 0; }
.ska-manage-wrap { 
    display: flex; 
    height: calc(100vh - 32px); /* Trừ hao chiều cao của Topbar Admin WP */
    margin: 0; 
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

/* Scrollbar tinh tế cho DataGrid (Áp dụng riêng cho khối bảng) */
.ska-datagrid-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
.ska-datagrid-scroll::-webkit-scrollbar-track { background: #f8fafc; border-left: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; }
.ska-datagrid-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border: 2px solid #f8fafc; border-radius: 6px; }
.ska-datagrid-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* CSS Cell đặc thù để tạo viền chìm kiểu Grid View */
.ska-cell {
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.ska-cell-header {
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
}
.ska-cell:last-child, .ska-cell-header:last-child {
    border-right: none;
}
</style>

<div class="wrap ska-manage-wrap text-gray-800">

    <!-- 1. Cột Sidebar Trái (Danh sách Bảng Mẫu) - Tách ra file riêng Part -->
    <?php require_once __DIR__ . '/parts/manage-sidebar.php'; ?>

    <!-- 2. Không gian làm việc Chính (Header + DataGrid) -->
    <div class="flex-1 flex flex-col bg-white overflow-hidden relative min-w-0">
        
        <?php if ( empty( $current_table ) ) : ?>
            <div class="flex items-center justify-center h-full text-gray-400 flex-col">
                <span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px;"></span>
                <p class="mt-4 text-lg">Vui lòng chọn hoặc cài đặt một Bảng dữ liệu.</p>
            </div>
        <?php else : ?>

            <!-- Toolbar Header -->
            <div class="h-14 border-b border-gray-200 bg-white flex items-center justify-between px-6 shrink-0 z-20 shadow-sm relative">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <?php echo esc_html( $friendly_name ); ?>
                        <span class="dashicons dashicons-arrow-down-alt2 text-gray-400 opacity-50 text-sm cursor-pointer" style="font-size: 14px;"></span>
                    </h1>
                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded font-mono border border-gray-200">
                        <?php echo count( $rows ); ?> bản ghi
                    </span>
                </div>
                
                <div class="flex items-center gap-3">
                    <button id="ska-btn-filter" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded flex items-center gap-1 shadow-sm transition-colors relative <?php echo isset($_GET['filter_field']) ? 'bg-emerald-50 border-emerald-300 text-emerald-700 hover:bg-emerald-100' : 'text-gray-600 hover:bg-gray-50'; ?>">
                        <span class="dashicons dashicons-filter mt-0.5" style="font-size: 16px;"></span> Lọc Data
                    </button>
                    <button id="ska-btn-group" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded flex items-center gap-1 shadow-sm transition-colors relative <?php echo isset($_GET['group_by']) ? 'bg-indigo-50 border-indigo-300 text-indigo-700 hover:bg-indigo-100' : 'text-gray-600 hover:bg-gray-50'; ?>">
                        <span class="dashicons dashicons-image-filter mt-0.5" style="font-size: 16px;"></span> Gộp Nhóm
                    </button>
                    
                    <?php if ( isset($_GET['filter_field']) || isset($_GET['group_by']) || isset($_GET['orderby']) ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ska-data-pro-manage&table=' . $current_table ) ); ?>" class="text-xs text-red-500 hover:text-red-700 font-medium px-1 flex items-center gap-0.5 ml-1">
                        <span class="dashicons dashicons-dismiss" style="font-size:14px; margin-top:2px;"></span> Xóa lọc
                    </a>
                    <?php endif; ?>
                    
                    <button id="ska-btn-portal-settings" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded flex items-center gap-1 shadow-sm transition-colors text-blue-600 hover:bg-blue-50 hover:border-blue-300 ml-2" onclick="document.getElementById('ska-portal-settings-modal').classList.remove('hidden'); if(window.skaInitPortalSettings) window.skaInitPortalSettings();">
                        <span class="dashicons dashicons-admin-site-alt3 mt-0.5" style="font-size: 16px;"></span> App Portal
                    </button>

                    <div class="w-px h-6 bg-gray-200 mx-1"></div>
                    <button class="ska-add-row-trigger px-4 py-1.5 text-sm bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white rounded font-medium flex items-center gap-1 shadow-sm transition">
                        <span class="dashicons dashicons-plus-alt2 mt-0.5" style="font-size: 16px;"></span> Thêm Dòng Mới
                    </button>
                </div>
            </div>

            <!-- Khối DataGrid (Bảng tính thực thụ với thanh cuộn) -->
            <div class="flex-1 overflow-auto ska-datagrid-scroll bg-gray-50 relative z-10 w-full" style="max-height: 100%;">
                <?php if ( empty( $columns ) ) : ?>
                    <p class="p-6 text-gray-500">Bảng này chưa có định dạng Cột (Schema trống).</p>
                <?php else : ?>
                    <table class="w-full text-left text-sm whitespace-nowrap border-spacing-0 table-fixed" style="border-collapse: separate; min-width: max-content;">
                        <!-- Vòng lặp Header Cột -->
                        <thead class="z-30 sticky top-0 shadow-[0_1px_2px_rgba(0,0,0,0.05)] bg-slate-100">
                            <tr>
                                <!-- Dòng đánh dấu checkbox bên trái -->
                                <th class="w-12 bg-slate-100 p-2 border-b border-gray-300 text-center sticky left-0 z-40 shadow-[1px_0_0_#cbd5e1] border-r">
                                    <input type="checkbox" class="rounded border-gray-300 shadow-sm disabled:opacity-50" disabled>
                                </th>
                                
                                <?php 
                                $current_orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
                                $current_order   = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';

                                foreach ( $columns as $col ) : 
                                    $col_slug = $col->Field;
                                    
                                    // Tạo link sort
                                    $next_order = ( $current_orderby === $col_slug && $current_order === 'DESC' ) ? 'ASC' : 'DESC';
                                    $sort_url   = admin_url( "admin.php?page=ska-data-pro-manage&table=" . urlencode( $current_table ) . "&orderby=" . urlencode( $col_slug ) . "&order=" . $next_order );
                                    // Bảo lưu param
                                    foreach (['filter_field', 'filter_val', 'filter_op', 'group_by'] as $pk) {
                                        if (isset($_GET[$pk])) $sort_url .= "&{$pk}=" . urlencode(sanitize_text_field($_GET[$pk]));
                                    }
                                ?>
                                    <th class="px-3 py-2.5 font-semibold text-gray-600 bg-slate-100 ska-cell-header tracking-wide text-xs w-48 relative group hover:bg-slate-200 transition-colors cursor-pointer select-none" onclick="window.location.href='<?php echo esc_url($sort_url); ?>'">
                                        <div class="flex items-center justify-between pointer-events-none">
                                            <div class="flex items-center gap-2 overflow-hidden pointer-events-auto">
                                                <?php 
                                                // Phân tích Label Diệu kỳ từ Dictionary (Nếu User có tạo)
                                                $display_label = $col_slug; // Ban đầu hiển thị Slug thô
                                                $dict_type  = ''; // Chứa Type (Tùy chọn) trong Dictionary

                                                if ( isset( $table_dict[ $col_slug ] ) ) {
                                                    $display_label = $table_dict[ $col_slug ]['label'];
                                                    $dict_type     = $table_dict[ $col_slug ]['type'];
                                                }

                                                // UX: Chèn Icon đặc tả Type hiển thị thông minh
                                                $type_to_check = ! empty( $dict_type ) ? $dict_type : strtolower( $col->Type );

                                                if ( strpos( $type_to_check, 'int' ) !== false || $type_to_check === 'number' ) {
                                                    echo '<span class="dashicons dashicons-editor-ol text-gray-400 opacity-80" style="font-size: 16px;" title="Numeric"></span>';
                                                } else if ( strpos( $type_to_check, 'datetime' ) !== false || strpos( $type_to_check, 'date' ) !== false ) {
                                                    echo '<span class="dashicons dashicons-calendar-alt text-gray-400 opacity-80" style="font-size: 16px;" title="Date"></span>';
                                                } else if ( strpos( $type_to_check, 'decimal' ) !== false || $type_to_check === 'currency' ) {
                                                    echo '<span class="dashicons dashicons-cart text-gray-400 opacity-80" style="font-size: 16px;" title="Currency"></span>';
                                                } else if ( $type_to_check === 'url' ) {
                                                    echo '<span class="dashicons dashicons-admin-links text-gray-400 opacity-80" style="font-size: 16px;" title="Link URL"></span>';
                                                } else if ( $type_to_check === 'media' ) {
                                                    echo '<span class="dashicons dashicons-format-image text-gray-400 opacity-80" style="font-size: 16px;" title="Single Image"></span>';
                                                } else if ( $type_to_check === 'media_gallery' ) {
                                                    echo '<span class="dashicons dashicons-images-alt2 text-gray-400 opacity-80" style="font-size: 16px;" title="Media Gallery"></span>';
                                                } else if ( $type_to_check === 'multi_select' ) {
                                                    echo '<span class="dashicons dashicons-list-view text-gray-400 opacity-80" style="font-size: 16px;" title="Multi-Select"></span>';
                                                } else if ( $type_to_check === 'relation' ) {
                                                    echo '<span class="dashicons dashicons-external text-indigo-500 opacity-80" style="font-size: 16px;" title="Tham Chiếu (Relation)"></span>';
                                                } else if ( $type_to_check === 'rollup' ) {
                                                    echo '<span class="dashicons dashicons-search text-orange-500 opacity-80" style="font-size: 16px;" title="Tra Cứu (Lookup / Rollup)"></span>';
                                                } else {
                                                    echo '<span class="dashicons dashicons-text text-gray-400 opacity-80" style="font-size: 16px; margin-top:2px;" title="Text"></span>';
                                                }
                                                ?>
                                                <span class="truncate" title="<?php echo esc_attr($col_slug); ?>"><?php echo esc_html( $display_label ); ?></span>
                                                
                                                <!-- Ký hiệu Sort -->
                                                <?php if ( $current_orderby === $col_slug ) : ?>
                                                    <span class="dashicons dashicons-arrow-<?php echo $current_order === 'ASC' ? 'up' : 'down'; ?>-alt2 text-emerald-500" style="font-size: 14px; margin-left: -4px;"></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Mũi tên gọi Dropdown Mở Menu Sửa Cột (Chỉ hiện khi rê chuột) -->
                                            <?php if ( $col_slug !== 'id' ) : ?>
                                            <div class="relative pointer-events-auto">
                                                <span class="dashicons dashicons-edit text-gray-300 opacity-0 group-hover:opacity-100 cursor-pointer hover:text-emerald-500 rounded ska-col-dropdown-trigger transition-colors" style="font-size: 14px;" onclick="event.stopPropagation(); window.skaToggleColDropdown('<?php echo esc_js($col_slug); ?>')"></span>
                                                <!-- Context Dropdown Menu -->
                                                <div id="dd-<?php echo esc_attr($col_slug); ?>" class="hidden ska-col-dropdown absolute top-6 right-0 w-44 bg-white rounded-md shadow-lg border border-gray-100 z-[60] text-gray-700 py-1 font-normal overflow-hidden animate-[pulse_0.1s_ease-out]">
                                                    <?php $col_options = isset($table_dict[$col_slug]['options']) ? $table_dict[$col_slug]['options'] : ''; ?>
                                                    <button onclick="event.stopPropagation(); skaOpenEditCol('<?php echo esc_attr($col_slug); ?>', '<?php echo esc_js($display_label); ?>', '<?php echo esc_attr($dict_type ? $dict_type : 'short_text'); ?>', '<?php echo esc_js($col_options); ?>'); document.getElementById('dd-<?php echo esc_attr($col_slug); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-emerald-50 hover:text-emerald-600 flex items-center gap-2 transition-colors">
                                                        <span class="dashicons dashicons-admin-generic text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Đổi Thuộc Tính
                                                    </button>
                                                    <div class="h-px bg-gray-100 my-0.5"></div>
                                                    <button onclick="event.stopPropagation(); skaOpenDeleteCol('<?php echo esc_attr($col_slug); ?>', '<?php echo esc_js($display_label); ?>'); document.getElementById('dd-<?php echo esc_attr($col_slug); ?>').classList.add('hidden');" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 hover:text-red-700 flex items-center gap-2 transition-colors">
                                                        <span class="dashicons dashicons-trash text-current opacity-70" style="font-size:14px; margin-top:-1px;"></span> Tàn Sát Cột (Xóa)
                                                    </button>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                                
                                <!-- NÚT [+] THÊM CỘT ẢO DIỆU -->
                                <th class="w-16 px-1 py-2.5 bg-slate-100 border-b border-gray-300 text-center sticky right-0 z-40 bg-slate-100 group shadow-[-1px_0_0_#cbd5e1] border-l border-gray-300">
                                    <button class="w-full h-full text-gray-400 hover:text-emerald-500 hover:bg-slate-200 transition-colors rounded" title="Thêm Trường Mới" onclick="document.getElementById('ska-add-col-modal').classList.remove('hidden'); document.getElementById('ska-col-label').focus();">
                                        <span class="dashicons dashicons-plus-alt2 mt-1"></span>
                                    </button>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            <?php if ( empty( $rows ) ) : ?>
                                <tr>
                                    <td colspan="<?php echo count( $columns ) + 1; ?>" class="p-8 text-center text-gray-500 bg-white">
                                        Lưới dữ liệu trống. Bản ghi đầu tiên của bạn sẽ xuất hiện tại đây.
                                    </td>
                                </tr>
                            <?php else : ?>
                                <!-- Vòng lặp Dòng Dữ liệu -->
                                <?php 
                                $prev_group_val = null;
                                $is_grouping = !empty($_GET['group_by']);
                                foreach ( $rows as $index => $row ) : 
                                    if ( $is_grouping ) {
                                        $curr_val = isset($row[$_GET['group_by']]) ? $row[$_GET['group_by']] : '';
                                        if ( $curr_val !== $prev_group_val || $index === 0 ) {
                                            $prev_group_val = $curr_val;
                                            echo '<tr class="bg-indigo-50/80">';
                                            echo '<td colspan="'.(count($columns) + 1).'" class="px-3 py-2 border-b border-indigo-100 text-indigo-800 font-bold text-xs sticky left-0 z-20 shadow-[1px_0_0_#e0e7ff] uppercase tracking-wide">';
                                            $disp_group_val = ($curr_val==='') ? '(Trống rỗng)' : esc_html($curr_val);
                                            echo '<span class="dashicons dashicons-networking align-middle mr-1" style="font-size:16px;"></span> Nhóm: <span class="text-indigo-900 bg-white px-2 py-0.5 rounded shadow-sm border border-indigo-100 ml-1">' . $disp_group_val . '</span>';
                                            echo '</td></tr>';
                                        }
                                    }
                                ?>
                                    <tr class="hover:bg-blue-50/50 group transition-colors ska-data-row" data-id="<?php echo esc_attr( $row['id'] ); ?>">
                                        
                                        <!-- Cột đếm STT giống Spreadsheet (Luôn bám trái màn hình) - Bổ sung Nút Xoá lúc Hover -->
                                        <td class="w-12 text-center text-gray-400 font-mono text-[11px] bg-white sticky left-0 z-20 group-hover:bg-blue-50/50 shadow-[1px_0_0_#e2e8f0] border-b border-gray-200">
                                            <span class="group-hover:hidden"><?php echo ( $index + 1 ); ?></span>
                                            <span class="hidden group-hover:inline-block dashicons dashicons-trash text-red-500 cursor-pointer hover:text-red-700 hover:scale-110 transition-transform ska-delete-row-btn absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="font-size: 16px;" title="Xóa dòng này"></span>
                                        </td>
                                        
                                        <!-- Vòng lặp Ô Data Cell -->
                                        <?php foreach ( $columns as $col ) : 
                                            $field_name = $col->Field;
                                            $val = isset( $row[ $field_name ] ) ? $row[ $field_name ] : '';
                                            
                                            // Kiểm tra Dữ liệu ánh xạ nội bộ
                                            $dict_type    = isset($table_dict[ $field_name ]) ? $table_dict[ $field_name ]['type'] : '';
                                            $dict_options = isset($table_dict[ $field_name ]['options']) ? $table_dict[ $field_name ]['options'] : '';

                                            // Chuyển đối Data Type sang type của thẻ Input HTML 
                                            $html_type = 'text';
                                            $raw_type  = strtolower( $col->Type );
                                            
                                            if ( ! empty( $dict_type ) ) {
                                                $html_type = $dict_type;
                                            } else {
                                                if ( strpos( $raw_type, 'int' ) !== false || strpos( $raw_type, 'decimal' ) !== false ) {
                                                    $html_type = 'number';
                                                } elseif ( strpos( $raw_type, 'datetime' ) !== false || strpos( $raw_type, 'date' ) !== false ) {
                                                    $html_type = 'date';
                                                }
                                            }
                                        ?>
                                            <!-- Bổ sung data-options để JS dùng làm select choices -->
                                            <?php 
                                            $cell_classes = 'px-3 py-2 text-gray-700 ska-cell hover:bg-gray-100/80 transition-colors relative ';
                                            if ( $html_type !== 'rollup' ) {
                                                $cell_classes .= 'ska-editable-cell cursor-text';
                                            } else {
                                                $cell_classes .= 'cursor-not-allowed bg-orange-50/10';
                                            }
                                            ?>
                                            <td class="<?php echo esc_attr($cell_classes); ?>" data-col="<?php echo esc_attr( $field_name ); ?>" data-type="<?php echo esc_attr( $html_type ); ?>" data-value="<?php echo esc_attr( $val ); ?>" data-options="<?php echo esc_attr( $dict_options ); ?>">
                                                <div class="truncate ska-cell-content pointer-events-none w-full h-full flex items-center">
                                                    <?php 
                                                    if ( $val === '' || $val === null ) {
                                                        echo '<span class="text-gray-300 italic opacity-50">#</span>';
                                                    } else {
                                                        // Render Tùy chỉnh theo Data Type
                                                        if ( $html_type === 'boolean' ) {
                                                            $is_checked = (intval($val) === 1);
                                                            $bg_class = $is_checked ? 'bg-emerald-500' : 'bg-gray-300';
                                                            $translate_class = $is_checked ? 'translate-x-3.5' : 'translate-x-0.5';
                                                            echo '<div class="w-8 h-4 flex items-center rounded-full transition-colors pointer-events-none ' . $bg_class . '"><div class="w-3.5 h-3.5 bg-white rounded-full shadow-sm transform transition-transform ' . $translate_class . '"></div></div>';
                                                        } elseif ( $html_type === 'media' ) {
                                                            echo '<img src="'.esc_url($val).'" class="h-6 w-6 object-cover rounded border border-gray-200 inline-block mr-2"><span class="text-[11px] text-gray-400">Media</span>';
                                                        } elseif ( $html_type === 'media_gallery' ) {
                                                            $urls = array_filter(array_map('trim', explode(',', $val)));
                                                            if (empty($urls)) {
                                                                echo '<span class="text-gray-300 italic opacity-50">#</span>';
                                                            } else {
                                                                echo '<div class="flex items-center -space-x-2">';
                                                                $limit = min(3, count($urls));
                                                                for ($i = 0; $i < $limit; $i++) {
                                                                    echo '<img src="'.esc_url($urls[$i]).'" class="h-6 w-6 object-cover rounded-full border border-white ring-1 ring-gray-200 relative z-'.(30-$i).' bg-gray-100 shadow-sm">';
                                                                }
                                                                if (count($urls) > 3) {
                                                                    echo '<span class="flex items-center justify-center h-6 w-6 rounded-full border border-white bg-gray-100 text-[10px] text-gray-500 font-medium relative z-0 ring-1 ring-gray-200">+'.(count($urls)-3).'</span>';
                                                                }
                                                                echo '</div>';
                                                            }
                                                        } elseif ( $html_type === 'select' ) {
                                                            echo '<span class="bg-blue-100 text-blue-700 text-[11px] px-2 py-0.5 rounded-full border border-blue-200">' . esc_html($val) . '</span>';
                                                        } elseif ( $html_type === 'multi_select' ) {
                                                            $selected_vals = array_map('trim', explode(',', $val));
                                                            $selected_vals = array_filter($selected_vals);
                                                            if (empty($selected_vals)) {
                                                                echo '<span class="text-gray-300 italic opacity-50">#</span>';
                                                            } else {
                                                                echo '<div class="flex flex-wrap gap-1">';
                                                                foreach ($selected_vals as $sval) {
                                                                    echo '<span class="bg-purple-100 text-purple-700 text-[11px] px-2 py-px rounded border border-purple-200">' . esc_html($sval) . '</span>';
                                                                }
                                                                echo '</div>';
                                                            }
                                                        } elseif ( $html_type === 'relation' ) {
                                                            $rel_arr = json_decode($val, true);
                                                            if ( empty($rel_arr) || !is_array($rel_arr) ) {
                                                                echo '<span class="text-gray-300 italic opacity-50">#</span>';
                                                            } else {
                                                                echo '<div class="flex flex-wrap gap-1">';
                                                                foreach ($rel_arr as $r) {
                                                                    echo '<span class="bg-indigo-100 text-indigo-700 text-[11px] px-2 py-0.5 font-medium rounded border border-indigo-200 shadow-[inset_0_1px_1px_rgba(255,255,255,0.5)]"><span class="dashicons dashicons-admin-links relative -top-0.5 opacity-50 mr-0.5 scale-75"></span>' . esc_html($r['label']) . '</span>';
                                                                }
                                                                echo '</div>';
                                                            }
                                                        } elseif ( $html_type === 'rollup' ) {
                                                            if ( $val === '' ) {
                                                                echo '<span class="text-gray-300 italic opacity-50">#</span>';
                                                            } else {
                                                                echo '<div class="flex flex-wrap gap-1">';
                                                                $r_vals = array_map('trim', explode(',', $val));
                                                                foreach ($r_vals as $r_v) {
                                                                    if ( $r_v === '' ) continue;
                                                                    echo '<span class="bg-orange-50 text-orange-700 text-[11px] px-2 py-0.5 rounded border border-orange-200 shadow-[inset_0_1px_1px_rgba(255,255,255,0.5)]">' . esc_html($r_v) . '</span>';
                                                                }
                                                                echo '</div>';
                                                            }
                                                        } elseif ( $html_type === 'long_text' ) {
                                                            echo '<span class="dashicons dashicons-editor-justify text-gray-400 origin-left scale-75 mr-1 align-middle"></span><span class="text-gray-400 italic text-xs">Nội dung văn bản...</span>';
                                                        } elseif ( $html_type === 'number' && ( $dict_type === 'currency' || strpos( $raw_type, 'decimal' ) !== false ) && is_numeric( $val ) ) {
                                                            echo number_format( $val, 0, ',', '.' ) . ' đ'; // Định dạng tiền tệ VNĐ demo
                                                        } else {
                                                            echo esc_html( $val ); 
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Hàng Tĩnh Cuối Cùng (Mockup Thêm Dòng dưới đáy) -->
                            <tr>
                                <td class="w-12 px-3 py-2 border-r border-b border-gray-200 bg-gray-50/30"></td>
                                <td colspan="<?php echo count( $columns ) + 1; ?>" class="ska-add-row-trigger px-3 py-2 text-emerald-600 font-medium cursor-pointer hover:bg-emerald-50/50 bg-gray-50 shadow-inner group transition text-sm">
                                    <span class="dashicons dashicons-plus-alt2 align-middle mr-1 group-hover:bg-emerald-100 rounded"></span> Thêm Bản Ghi Mới
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- 3. Khu Vực Render Các Modals (Thêm/Sửa/Xóa Schema) -->
<?php require_once __DIR__ . '/parts/manage-modals.php'; ?>

<!-- 4. Tích hợp Script Đóng Dropdown & Cấu Hình -->
<script>
window.skaToggleColDropdown = function(slug) {
    const id = 'dd-' + slug;
    const target = document.getElementById(id);
    const isHidden = target.classList.contains('hidden');
    
    // Đóng toàn bộ dropdown khác trước khi thao tác
    document.querySelectorAll('.ska-col-dropdown').forEach(dd => dd.classList.add('hidden'));

    if (isHidden) {
        target.classList.remove('hidden');
    }
};

document.addEventListener('click', function(e) {
    // Nếu click RA NGOÀI trigger và RA NGOÀI thân dropdown
    if (!e.target.closest('.ska-col-dropdown-trigger') && !e.target.closest('.ska-col-dropdown')) {
        document.querySelectorAll('.ska-col-dropdown').forEach(dd => dd.classList.add('hidden'));
    }
});
</script>
<script>
window.skaDataConfig = {
    tableId: '<?php echo esc_js( $current_table ); ?>',
    ajaxurl: window.ajaxurl,
    nonce: '<?php echo esc_js( wp_create_nonce("ska_data_nonce") ); ?>'
};
</script>
<?php
$bundle_path = dirname(dirname(dirname(__FILE__))) . '/assets/js/admin-datagrid.bundle.js';
$bundle_ver  = file_exists($bundle_path) ? filemtime($bundle_path) : SKA_DATA_PRO_VERSION;
?>
<script src="<?php echo esc_url( plugins_url( 'assets/js/admin-datagrid.bundle.js', dirname(dirname(dirname(__FILE__))) ) . '?v=' . $bundle_ver ); ?>"></script>
