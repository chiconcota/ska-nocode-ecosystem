<?php
defined( 'ABSPATH' ) || exit;
?>
<!-- SCHEMA BUILDER: MÀN HÌNH TẠO CỘT VẬT LÝ NỔI BẬT THEO CÁCH ĐIỀU CHỈNH AIRTABLE -->
<div id="ska-add-col-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <!-- Đỉnh Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-bold text-gray-800 m-0">Thêm Trường (Cột) Mới</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-add-col-modal').classList.add('hidden');"></span>
        </div>
        
        <!-- Không gian làm việc Nhập liệu -->
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tên Cột Hiển Thị</label>
                <input type="text" id="ska-col-label" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" placeholder="vd: Hình Mẫu Sản Phẩm..." autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Định Dạng Dữ Liệu</label>
                <select id="ska-col-type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="short_text" selected>Chữ Ngắn (Short Text)</option>
                    <option value="long_text">Nội Dung Dài (Văn bản / Rich Text HTML)</option>
                    <option value="number">Số Đếm (Number)</option>
                    <option value="currency">Tiền Lẻ / Tỷ Giá (Currency)</option>
                    <option value="url">Đường Dẫn Link (URL)</option>
                    <option value="media">Ảnh Đơn (Single Image)</option>
                    <option value="media_gallery">Thư Viện Ảnh (Media Gallery)</option>
                    <option value="boolean">Nút Gạt (True/False)</option>
                    <option value="select">Danh Sách Chọn (Select Đơn)</option>
                    <option value="multi_select">Chọn Nhiều (Multi Select)</option>
                    <option value="relation">Tham Chiếu Nối Bảng (Relation)</option>
                    <option value="rollup">Rollup (Lấy Cột Bảng Khác)</option>
                </select>
            </div>
            
            <div id="ska-col-options-wrapper" class="hidden">
                <label id="ska-col-options-label" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Danh Sách Lựa Chọn (Option List)</label>
                
                <!-- Input dành cho Select thường -->
                <input type="text" id="ska-col-options" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white font-mono" placeholder="vd: xanh,đỏ,tím,vàng" autocomplete="off">
                
                <!-- Select dành riêng cho Relation -->
                <select id="ska-col-options-relation" class="hidden w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="">-- Chọn Bảng Đích --</option>
                    <?php 
                    global $wpdb;
                    // Bổ sung Nguồn WP Core
                    echo '<option value="' . esc_attr($wpdb->posts) . '">📦 WP Posts / Sản Phẩm Woo (' . esc_html($wpdb->posts) . ')</option>';
                    echo '<option value="' . esc_attr($wpdb->users) . '">👤 WP Users (' . esc_html($wpdb->users) . ')</option>';
                    echo '<option disabled>----------------------</option>';
                    
                    if ( isset($all_tables) && is_array($all_tables) ) {
                        foreach ( $all_tables as $tb ) {
                            $f_name = ucwords(str_replace('_', ' ', str_replace($wpdb->prefix . 'ska_data_', '', $tb)));
                            echo '<option value="'.esc_attr($tb).'">'.esc_html($f_name).' ('.esc_html($tb).')</option>';
                        }
                    }
                    ?>
                </select>

                <!-- Bộ đôi Cascading Selects dành riêng cho Rollup -->
                <div id="ska-col-options-rollup-wrapper" class="hidden flex flex-col gap-3">
                    <div class="px-3 py-2 bg-orange-50 border border-orange-100 rounded text-xs text-orange-800">
                        Bạn phải có sẵn Cột Tham Chiếu (Relation) thì mới có thể kéo dữ liệu (Rollup).
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">1. Chọn Cột Tham Chiếu (Relation)</label>
                        <select id="ska-col-options-rollup-rel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                            <option value="">-- Chọn cột Relation --</option>
                            <?php 
                            if ( isset($columns) && is_array($columns) ) {
                                foreach ($columns as $col) {
                                    if ( isset($table_dict[$col->Field]) && $table_dict[$col->Field]['type'] === 'relation' ) {
                                        $lbl = $table_dict[$col->Field]['label'];
                                        $tgt = $table_dict[$col->Field]['options'];
                                        echo '<option value="' . esc_attr($col->Field) . '" data-target="' . esc_attr($tgt) . '">' . esc_html($lbl) . ' (Trỏ tới: ' . esc_html(str_replace($wpdb->prefix.'ska_data_','',$tgt)) . ')</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">2. Chọn Cột Tra Cứu (Mục Tiêu)</label>
                        <select id="ska-col-options-rollup-target" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 disabled:bg-gray-100 disabled:text-gray-400" disabled>
                            <option value="">-- Vui lòng chọn Cột Tham Chiếu trước --</option>
                        </select>
                    </div>
                </div>

                <p id="ska-col-options-hint" class="text-[11px] text-gray-400 mt-1 italic">Các trạng thái cách nhau bằng dấu phẩy. Mặc định option đầu tiên sẽ được chọn.</p>
            </div>
            
            <p class="text-xs text-gray-400 m-0 italic"><span class="dashicons dashicons-info" style="font-size: 12px; height: 12px; margin-top: 1px"></span> Cột sẽ được ghi thẳng vào Hệ Thống Đĩa Ổ cứng.</p>
        </div>

        <!-- Cẳng cuối Action -->
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-add-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Hủy</button>
            <button id="ska-submit-col-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-500 hover:bg-emerald-600 shadow-sm text-sm transition flex items-center gap-1">Tạo Trường Dữ Liệu</button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH SỬA CỘT (EDIT COLUMN) -->
<div id="ska-edit-col-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-edit text-emerald-500"></span> Chỉnh Sửa Thuộc Tính</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-edit-col-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="ska-edit-col-slug">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tên Ký Danh Mới</label>
                <input type="text" id="ska-edit-col-label" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Ép Kiểu Dữ Liệu Lại</label>
                <select id="ska-edit-col-type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="short_text">Chữ Ngắn (Short Text)</option>
                    <option value="long_text">Nội Dung Dài (Văn bản / Rich Text HTML)</option>
                    <option value="number">Số Đếm (Number)</option>
                    <option value="currency">Tiền Lẻ / Tỷ Giá (Currency)</option>
                    <option value="url">Đường Dẫn Link (URL)</option>
                    <option value="media">Ảnh Đơn (Single Image)</option>
                    <option value="media_gallery">Thư Viện Ảnh (Media Gallery)</option>
                    <option value="boolean">Nút Gạt (True/False)</option>
                    <option value="select">Danh Sách Chọn (Select Đơn)</option>
                    <option value="multi_select">Chọn Nhiều (Multi Select)</option>
                    <option value="relation">Tham Chiếu Nối Bảng (Relation)</option>
                    <option value="rollup">Rollup (Lấy Cột Bảng Khác)</option>
                </select>
            </div>
            
            <div id="ska-edit-col-options-wrapper" class="hidden">
                <label id="ska-edit-col-options-label" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Danh Sách Lựa Chọn (Option List)</label>
                
                <input type="text" id="ska-edit-col-options" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white font-mono" autocomplete="off">
                
                <select id="ska-edit-col-options-relation" class="hidden w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="">-- Chọn Bảng Đích --</option>
                    <?php 
                    // Nguồn lấy từ Global vì ở bên trên đã gọi $wpdb rồi
                    // Bổ sung Nguồn WP Core giống phần thêm mới
                    echo '<option value="' . esc_attr($wpdb->posts) . '">📦 WP Posts / Sản Phẩm Woo (' . esc_html($wpdb->posts) . ')</option>';
                    echo '<option value="' . esc_attr($wpdb->users) . '">👤 WP Users (' . esc_html($wpdb->users) . ')</option>';
                    echo '<option disabled>----------------------</option>';

                    if ( isset($all_tables) && is_array($all_tables) ) {
                        foreach ( $all_tables as $tb ) {
                            $f_name = ucwords(str_replace('_', ' ', str_replace($wpdb->prefix . 'ska_data_', '', $tb)));
                            echo '<option value="'.esc_attr($tb).'">'.esc_html($f_name).' ('.esc_html($tb).')</option>';
                        }
                    }
                    ?>
                </select>

                <div id="ska-edit-col-options-rollup-wrapper" class="hidden flex flex-col gap-3">
                    <div class="px-3 py-2 bg-orange-50 border border-orange-100 rounded text-xs text-orange-800">
                        Bạn phải có sẵn Cột Tham Chiếu (Relation) thì mới có thể kéo dữ liệu (Rollup).
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">1. Chọn Cột Tham Chiếu (Relation)</label>
                        <select id="ska-edit-col-options-rollup-rel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                            <option value="">-- Chọn cột Relation --</option>
                            <?php 
                            if ( isset($columns) && is_array($columns) ) {
                                foreach ($columns as $col) {
                                    if ( isset($table_dict[$col->Field]) && $table_dict[$col->Field]['type'] === 'relation' ) {
                                        $lbl = $table_dict[$col->Field]['label'];
                                        $tgt = $table_dict[$col->Field]['options'];
                                        echo '<option value="' . esc_attr($col->Field) . '" data-target="' . esc_attr($tgt) . '">' . esc_html($lbl) . ' (Trỏ tới: ' . esc_html(str_replace($wpdb->prefix.'ska_data_','',$tgt)) . ')</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1">2. Chọn Cột Tra Cứu (Mục Tiêu)</label>
                        <select id="ska-edit-col-options-rollup-target" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 disabled:bg-gray-100 disabled:text-gray-400" disabled>
                            <option value="">-- Vui lòng chọn Cột Tham Chiếu trước --</option>
                        </select>
                    </div>
                </div>

            </div>
            
            <div class="p-3 bg-red-50 border border-red-100 rounded text-xs text-red-600">
                <strong><span class="dashicons dashicons-warning" style="font-size:13px;height:12px;margin-top:2px;"></span> Rủi ro ép kiểu:</strong> Nếu bạn Đổi Loại Cột sai quy cách (VD đang chứa chữ mà đổi thành Ngày Tháng), MySQL có thể sẽ gọt sạch dữ liệu bên trong những ô hiện tại.
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-edit-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Hủy</button>
            <button id="ska-update-col-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1">Lưu Thuộc Tính</button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH DELETE COLUMN WARNING -->
<div id="ska-delete-col-modal" class="hidden fixed inset-0 bg-red-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50 text-red-600">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-warning"></span> Cảnh Báo Phá Hủy</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-delete-col-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5">
            <input type="hidden" id="ska-del-col-slug">
            <p class="text-sm text-gray-700 mb-3">Bạn đang vung rìu tính chém đứt cột <strong>"<span id="ska-del-col-name" class="font-bold text-red-600"></span>"</strong>.</p>
            <p class="text-sm text-gray-500 m-0 leading-relaxed bg-red-50/50 p-2 rounded">
                Mọi dòng dữ liệu lưu trữ bên trong cột này sẽ tan biến vào hư không (Không Thể Chọn Hoàn Tác). Bạn đã suy xét kỹ chưa?
            </p>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-delete-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Quay Tay Lại</button>
            <button id="ska-execute-del-col-btn" class="px-4 py-2 rounded font-medium text-white bg-red-600 hover:bg-red-700 shadow-sm text-sm transition flex items-center gap-1">Trảm (Xóa Mãi Mãi)</button>
        </div>
    </div>
</div>

<!-- ======================= MẢNG QUẢN TRỊ TABLE (BẢNG) ======================= -->

<!-- MÀN HÌNH TẠO BẢNG MỚI (CREATE TABLE) -->
<div id="ska-create-table-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <!-- Đỉnh Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50 text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-database text-emerald-500"></span> Khởi tạo Bảng Dữ Liệu</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-create-table-modal').classList.add('hidden');"></span>
        </div>
        
        <!-- Không gian làm việc Nhập liệu -->
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tên Bảng Dữ Liệu</label>
                <input type="text" id="ska-new-table-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" placeholder="vd: Học Sinh, Sản Phẩm..." autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Biểu Tượng (Icon)</label>
                <select id="ska-new-table-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-media-spreadsheet">Biểu bảng cơ bản</option>
                    <option value="dashicons-admin-users">Người dùng / User</option>
                    <option value="dashicons-cart">Giỏ hàng / Đơn hàng</option>
                    <option value="dashicons-products">Sản phẩm / Hàng hóa</option>
                    <option value="dashicons-calendar-alt">Lịch / Sự kiện</option>
                    <option value="dashicons-format-gallery">Hình ảnh / Media</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Thuộc Hệ Sinh Thái (Nhóm)</label>
                <select id="ska-new-table-group" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="custom">Bảng Tùy Chỉnh (Độc lập)</option>
                    <?php 
                    $modal_app_names = array(
                        'ecommerce' => 'E-Commerce App (Bán hàng)',
                        'lms'       => 'Hệ Thống LMS (Khóa học)',
                        'booking'   => 'App Đặt Lịch (Booking)'
                    );
                    $active_groups = isset($grouped_tables) && is_array($grouped_tables) ? array_keys($grouped_tables) : array();
                    foreach ( $active_groups as $g_key ) {
                        if ( isset( $modal_app_names[ $g_key ] ) ) {
                            echo '<option value="' . esc_attr($g_key) . '">' . esc_html($modal_app_names[$g_key]) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Cẳng cuối Action -->
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-create-table-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Hủy</button>
            <button id="ska-execute-create-table-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-500 hover:bg-emerald-600 shadow-sm text-sm transition flex items-center gap-1">Tạo Bảng</button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH SỬA TÊN BẢNG (RENAME TABLE) -->
<div id="ska-rename-table-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-edit text-emerald-500"></span> Chỉnh Sửa Tên Bảng</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-rename-table-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="ska-rename-table-slug">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tên Ký Danh Mới</label>
                <input type="text" id="ska-rename-table-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Biểu Tượng</label>
                <select id="ska-rename-table-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-media-spreadsheet">Biểu bảng cơ bản</option>
                    <option value="dashicons-admin-users">Người dùng / User</option>
                    <option value="dashicons-cart">Giỏ hàng / Đơn hàng</option>
                    <option value="dashicons-products">Sản phẩm / Hàng hóa</option>
                    <option value="dashicons-calendar-alt">Lịch / Sự kiện</option>
                    <option value="dashicons-format-gallery">Hình ảnh / Media</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Thuộc Hệ Sinh Thái (Nhóm)</label>
                <select id="ska-rename-table-group" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="custom">Bảng Tùy Chỉnh (Độc lập)</option>
                    <?php 
                    foreach ( $active_groups as $g_key ) {
                        if ( isset( $modal_app_names[ $g_key ] ) ) {
                            echo '<option value="' . esc_attr($g_key) . '">' . esc_html($modal_app_names[$g_key]) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-rename-table-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Hủy</button>
            <button id="ska-execute-rename-table-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1">Lưu Thông Tin</button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH DELETE TABLE LỚN VỚI CHỮ KÝ BẢO MẬT (XACNHAN) -->
<div id="ska-delete-table-modal" class="hidden fixed inset-0 bg-red-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[450px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50 text-red-600">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-warning" style="font-size:24px;width:24px;height:24px;margin-top:-2px"></span> PHÁ HỦY TOÀN BỘ BẢNG</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-delete-table-modal').classList.add('hidden'); document.getElementById('ska-delete-confirm-input').value='';"></span>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="ska-del-tbl-slug">
            <p class="text-[15px] text-gray-700 mb-3">Hành động này sẽ Xóa Vĩnh Viễn bảng <strong class="text-red-600">"<span id="ska-del-tbl-name"></span>"</strong> khỏi Máy tính.</p>
            <div class="bg-red-50/70 p-4 rounded text-sm text-gray-700 leading-relaxed mb-4 border border-red-100">
                ⚠️ Cảnh báo: Mọi Cột dữ liệu và Bản ghi (dữ liệu khách hàng, đơn hàng, hóa đơn...) bên trong bảng này sẽ bị xóa không thể lấy lại.
            </div>
            
            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Nhập chữ <span class="bg-gray-200 text-red-600 px-1 py-0.5 rounded font-mono">XACNHAN</span> để hoàn thành</label>
            <input type="text" id="ska-delete-confirm-input" class="w-full border-red-200 rounded-md shadow-inner focus:border-red-500 focus:ring-red-500 p-3 bg-red-50/30 text-red-700 font-mono tracking-widest text-center" autocomplete="off" placeholder="Gõ chữ XACNHAN vào đây">
        </div>

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-delete-table-modal').classList.add('hidden'); document.getElementById('ska-delete-confirm-input').value='';" class="px-6 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 transition">Đóng lại</button>
            <button id="ska-execute-del-table-btn" class="px-6 py-2 rounded font-medium text-white bg-red-600 hover:bg-red-700 shadow-sm transition flex items-center gap-2 opacity-50 cursor-not-allowed" disabled>
                <span class="dashicons dashicons-trash mt-0.5" style="font-size: 16px;"></span> Chấp nhận Rủi ro & Xóa
            </button>
        </div>
    </div>
</div>

<!-- ======================= MẢNG QUẢN TRỊ VIEW (VIEW TOOLS) ======================= -->

<!-- MÀN HÌNH POPUP QUICK FILTER (LỌC DỮ LIỆU) -->
<div id="ska-filter-data-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50 text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-filter text-emerald-500"></span> Lọc Dữ Liệu</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-filter-data-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Lọc Theo Cột</label>
                <select id="ska-filter-field" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <?php if ( isset($columns) && is_array($columns) ) : ?>
                        <?php foreach ( $columns as $col ) : ?>
                            <option value="<?php echo esc_attr($col->Field); ?>" <?php selected(isset($_GET['filter_field']) ? $_GET['filter_field'] : '', $col->Field); ?>>
                                <?php echo esc_html(isset($table_dict[$col->Field]['label']) ? $table_dict[$col->Field]['label'] : $col->Field); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Điều Kiện</label>
                <select id="ska-filter-op" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="like" <?php selected(isset($_GET['filter_op']) ? $_GET['filter_op'] : '', 'like'); ?>>Chứa từ khóa (Bao gồm)</option>
                    <option value="eq" <?php selected(isset($_GET['filter_op']) ? $_GET['filter_op'] : '', 'eq'); ?>>Bằng chính xác (=)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Giá trị so sánh</label>
                <input type="text" id="ska-filter-val" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white placeholder-gray-300" placeholder="vd: Sản phẩm A..." autocomplete="off" value="<?php echo isset($_GET['filter_val']) ? esc_attr(wp_unslash($_GET['filter_val'])) : ''; ?>">
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-filter-data-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Đóng</button>
            <button id="ska-execute-filter-btn" class="px-5 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-saved mt-0.5"></span> Áp Dụng Lọc</button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH POPUP GỘP NHÓM (GROUP BY) -->
<div id="ska-group-data-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[350px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-indigo-50 text-indigo-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-networking text-indigo-500"></span> Gộp Nhóm Bản Ghi</h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('ska-group-data-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <p class="text-xs text-gray-500 m-0 leading-relaxed">
                Tự động gom các dòng có chung giá trị hiện tại lại với nhau thành từng cụm.
            </p>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Chọn cột để gộp</label>
                <select id="ska-group-field" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white text-gray-700 font-medium">
                    <option value="">-- Không gộp nhóm --</option>
                    <?php if ( isset($columns) && is_array($columns) ) : ?>
                        <?php foreach ( $columns as $col ) : ?>
                            <option value="<?php echo esc_attr($col->Field); ?>" <?php selected(isset($_GET['group_by']) ? $_GET['group_by'] : '', $col->Field); ?>>
                                <?php echo esc_html(isset($table_dict[$col->Field]['label']) ? $table_dict[$col->Field]['label'] : $col->Field); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-indigo-100 bg-indigo-50/30 flex justify-end gap-2">
            <button onclick="document.getElementById('ska-group-data-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition">Đóng</button>
            <button id="ska-execute-group-btn" class="px-5 py-2 rounded font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-image-filter relative mt-0.5"></span> Nhóm Ngay</button>
        </div>
    </div>
</div>

<script>
    // Bơm Từ Điển Máy Chủ xuống Biến Toàn Cục Trình Duyệt để AJAX Bypass
    window.skaGlobalDict = <?php echo json_encode(isset($all_dict) ? $all_dict : array()); ?>;
</script>
