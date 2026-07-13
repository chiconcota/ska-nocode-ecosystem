<?php
defined( 'ABSPATH' ) || exit;
?>
<!-- SCHEMA BUILDER: MÀN HÌNH TẠO CỘT VẬT LÝ NỔI BẬT THEO CÁCH ĐIỀU CHỈNH AIRTABLE -->
<div id="skaaa-add-col-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <!-- Đỉnh Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-bold text-gray-800 m-0"><?php esc_html_e( 'Add New Field (Column).', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-add-col-modal').classList.add('hidden');"></span>
        </div>
        
        <!-- Không gian làm việc Nhập liệu -->
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Display Column Name', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-col-label" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" placeholder=__( 'eg: Product Model...', 'skaaa-data-pro' ) autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Data Format', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-col-type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="short_text" selected><?php esc_html_e( 'Short Text', 'skaaa-data-pro' ); ?></option>
                    <option value="long_text"><?php esc_html_e( 'Long Content (Text / Rich Text HTML)', 'skaaa-data-pro' ); ?></option>
                    <option value="number"><?php esc_html_e( 'Number', 'skaaa-data-pro' ); ?></option>
                    <option value="currency"><?php esc_html_e( 'Currency', 'skaaa-data-pro' ); ?></option>
                    <option value="date"><?php esc_html_e( 'Date', 'skaaa-data-pro' ); ?></option>
                    <option value="datetime"><?php esc_html_e( 'Date Time', 'skaaa-data-pro' ); ?></option>
                    <option value="url"><?php esc_html_e( 'Link (URL)', 'skaaa-data-pro' ); ?></option>
                    <option value="media"><?php esc_html_e( 'Single Image', 'skaaa-data-pro' ); ?></option>
                    <option value="media_gallery"><?php esc_html_e( 'Media Gallery', 'skaaa-data-pro' ); ?></option>
                    <option value="boolean"><?php esc_html_e( 'Toggle Button (True/False)', 'skaaa-data-pro' ); ?></option>
                    <option value="select"><?php esc_html_e( 'Select List (Single Select)', 'skaaa-data-pro' ); ?></option>
                    <option value="multi_select"><?php esc_html_e( 'Multi Select', 'skaaa-data-pro' ); ?></option>
                    <option value="relation"><?php esc_html_e( 'Table Reference (Relation)', 'skaaa-data-pro' ); ?></option>
                    <option value="rollup"><?php esc_html_e( 'Rollup (Get Other Table Columns)', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>
            
            <div id="skaaa-col-options-wrapper" class="hidden">
                <label id="skaaa-col-options-label" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Option List', 'skaaa-data-pro' ); ?></label>
                
                <!-- Input dành cho Select thường -->
                <input type="text" id="skaaa-col-options" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white font-mono" placeholder=__( 'eg: blue, red, purple, yellow', 'skaaa-data-pro' ) autocomplete="off">
                
                <!-- Select dành riêng cho Relation -->
                <select id="skaaa-col-options-relation" class="hidden w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value=""><?php esc_html_e( '-- Select Destination Table --', 'skaaa-data-pro' ); ?></option>
                    <?php 
                    global $wpdb;
                    // Bổ sung Nguồn WP Core
                    echo '<option value="' . esc_attr($wpdb->posts) . __( '\">📦 WP Posts / Woo Products (', 'skaaa-data-pro' ) . esc_html($wpdb->posts) . ')</option>';
                    echo '<option value="' . esc_attr($wpdb->users) . '">👤 WP Users (' . esc_html($wpdb->users) . ')</option>';
                    echo '<option disabled>----------------------</option>';
                    
                    if ( isset($all_tables) && is_array($all_tables) ) {
                        foreach ( $all_tables as $tb ) {
                            $f_name = ucwords(str_replace('_', ' ', str_replace($wpdb->prefix . 'skaaa_data_', '', $tb)));
                            echo '<option value="'.esc_attr($tb).'">'.esc_html($f_name).' ('.esc_html($tb).')</option>';
                        }
                    }
                    ?>
                </select>

                <!-- Bộ đôi Cascading Selects dành riêng cho Rollup -->
                <div id="skaaa-col-options-rollup-wrapper" class="hidden flex flex-col gap-3">
                    <div class="px-3 py-2 bg-orange-50 border border-orange-100 rounded text-xs text-orange-800">
                        <?php esc_html_e( 'You must have a Reference Column (Relation) to pull data (Rollup).', 'skaaa-data-pro' ); ?>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1"><?php esc_html_e( '1. Select Reference Column (Relation)', 'skaaa-data-pro' ); ?></label>
                        <select id="skaaa-col-options-rollup-rel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                            <option value=""><?php esc_html_e( '-- Select Relation column --', 'skaaa-data-pro' ); ?></option>
                            <?php 
                            if ( isset($columns) && is_array($columns) ) {
                                foreach ($columns as $col) {
                                    if ( isset($table_dict[$col->Field]) && $table_dict[$col->Field]['type'] === 'relation' ) {
                                        $lbl = $table_dict[$col->Field]['label'];
                                        $tgt = $table_dict[$col->Field]['options'];
                                        echo '<option value="' . esc_attr($col->Field) . '" data-target="' . esc_attr($tgt) . '">' . esc_html($lbl) . ' (' . esc_html__( 'Points to:', 'skaaa-data-pro' ) . ' ' . esc_html(str_replace($wpdb->prefix.'skaaa_data_','',$tgt)) . ')</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1"><?php esc_html_e( '2. Select Lookup Column (Target)', 'skaaa-data-pro' ); ?></label>
                        <select id="skaaa-col-options-rollup-target" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 disabled:bg-gray-100 disabled:text-gray-400" disabled>
                            <option value=""><?php esc_html_e( '-- Please select Reference Column first --', 'skaaa-data-pro' ); ?></option>
                        </select>
                    </div>
                </div>

                <p id="skaaa-col-options-hint" class="text-[11px] text-gray-400 mt-1 italic"><?php esc_html_e( 'The states are separated by commas. ', 'skaaa-data-pro' ); ?></p>
            </div>
            
            <p class="text-xs text-gray-400 m-0 italic"><span class="dashicons dashicons-info" style="font-size: 12px; height: 12px; margin-top: 1px"></span> <?php esc_html_e( 'The column will be written directly to the hard disk system.', 'skaaa-data-pro' ); ?></p>
        </div>

        <!-- Cẳng cuối Action -->
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-add-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-submit-col-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-500 hover:bg-emerald-600 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Create Data Field', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH SỬA CỘT (EDIT COLUMN) -->
<div id="skaaa-edit-col-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-edit text-emerald-500"></span> <?php esc_html_e( 'Edit Attributes', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-edit-col-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="skaaa-edit-col-slug">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'New Nickname', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-edit-col-label" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Compress Data Type Again', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-edit-col-type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <option value="short_text"><?php esc_html_e( 'Short Text', 'skaaa-data-pro' ); ?></option>
                    <option value="long_text"><?php esc_html_e( 'Long Content (Text / Rich Text HTML)', 'skaaa-data-pro' ); ?></option>
                    <option value="number"><?php esc_html_e( 'Number', 'skaaa-data-pro' ); ?></option>
                    <option value="currency"><?php esc_html_e( 'Currency', 'skaaa-data-pro' ); ?></option>
                    <option value="date"><?php esc_html_e( 'Date', 'skaaa-data-pro' ); ?></option>
                    <option value="datetime"><?php esc_html_e( 'Date Time', 'skaaa-data-pro' ); ?></option>
                    <option value="url"><?php esc_html_e( 'Link (URL)', 'skaaa-data-pro' ); ?></option>
                    <option value="media"><?php esc_html_e( 'Single Image', 'skaaa-data-pro' ); ?></option>
                    <option value="media_gallery"><?php esc_html_e( 'Media Gallery', 'skaaa-data-pro' ); ?></option>
                    <option value="boolean"><?php esc_html_e( 'Toggle Button (True/False)', 'skaaa-data-pro' ); ?></option>
                    <option value="select"><?php esc_html_e( 'Select List (Single Select)', 'skaaa-data-pro' ); ?></option>
                    <option value="multi_select"><?php esc_html_e( 'Multi Select', 'skaaa-data-pro' ); ?></option>
                    <option value="relation"><?php esc_html_e( 'Table Reference (Relation)', 'skaaa-data-pro' ); ?></option>
                    <option value="rollup"><?php esc_html_e( 'Rollup (Get Other Table Columns)', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>
            
            <div id="skaaa-edit-col-options-wrapper" class="hidden">
                <label id="skaaa-edit-col-options-label" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Option List', 'skaaa-data-pro' ); ?></label>
                
                <input type="text" id="skaaa-edit-col-options" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white font-mono" autocomplete="off">
                
                <select id="skaaa-edit-col-options-relation" class="hidden w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value=""><?php esc_html_e( '-- Select Destination Table --', 'skaaa-data-pro' ); ?></option>
                    <?php 
                    // Nguồn lấy từ Global vì ở bên trên đã gọi $wpdb rồi
                    // Bổ sung Nguồn WP Core giống phần thêm mới
                    echo '<option value="' . esc_attr($wpdb->posts) . __( '\">📦 WP Posts / Woo Products (', 'skaaa-data-pro' ) . esc_html($wpdb->posts) . ')</option>';
                    echo '<option value="' . esc_attr($wpdb->users) . '">👤 WP Users (' . esc_html($wpdb->users) . ')</option>';
                    echo '<option disabled>----------------------</option>';

                    if ( isset($all_tables) && is_array($all_tables) ) {
                        foreach ( $all_tables as $tb ) {
                            $f_name = ucwords(str_replace('_', ' ', str_replace($wpdb->prefix . 'skaaa_data_', '', $tb)));
                            echo '<option value="'.esc_attr($tb).'">'.esc_html($f_name).' ('.esc_html($tb).')</option>';
                        }
                    }
                    ?>
                </select>

                <div id="skaaa-edit-col-options-rollup-wrapper" class="hidden flex flex-col gap-3">
                    <div class="px-3 py-2 bg-orange-50 border border-orange-100 rounded text-xs text-orange-800">
                        <?php esc_html_e( 'You must have a Reference Column (Relation) to pull data (Rollup).', 'skaaa-data-pro' ); ?>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1"><?php esc_html_e( '1. Select Reference Column (Relation)', 'skaaa-data-pro' ); ?></label>
                        <select id="skaaa-edit-col-options-rollup-rel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                            <option value=""><?php esc_html_e( '-- Select Relation column --', 'skaaa-data-pro' ); ?></option>
                            <?php 
                            if ( isset($columns) && is_array($columns) ) {
                                foreach ($columns as $col) {
                                    if ( isset($table_dict[$col->Field]) && $table_dict[$col->Field]['type'] === 'relation' ) {
                                        $lbl = $table_dict[$col->Field]['label'];
                                        $tgt = $table_dict[$col->Field]['options'];
                                        echo '<option value="' . esc_attr($col->Field) . '" data-target="' . esc_attr($tgt) . '">' . esc_html($lbl) . ' (' . esc_html__( 'Points to:', 'skaaa-data-pro' ) . ' ' . esc_html(str_replace($wpdb->prefix.'skaaa_data_','',$tgt)) . ')</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 mb-1"><?php esc_html_e( '2. Select Lookup Column (Target)', 'skaaa-data-pro' ); ?></label>
                        <select id="skaaa-edit-col-options-rollup-target" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 disabled:bg-gray-100 disabled:text-gray-400" disabled>
                            <option value=""><?php esc_html_e( '-- Please select Reference Column first --', 'skaaa-data-pro' ); ?></option>
                        </select>
                    </div>
                </div>

            </div>
            
            <div class="p-3 bg-red-50 border border-red-100 rounded text-xs text-red-600">
                <strong><span class="dashicons dashicons-warning" style="font-size:13px;height:12px;margin-top:2px;"></span> <?php esc_html_e( 'Type casting risk:', 'skaaa-data-pro' ); ?></strong> <?php esc_html_e( 'If you change the Column Type incorrectly (e.g. from text to Date), MySQL might wipe out the data inside the existing cells.', 'skaaa-data-pro' ); ?>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-edit-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-update-col-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Save Attributes', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH DELETE COLUMN WARNING -->
<div id="skaaa-delete-col-modal" class="hidden fixed inset-0 bg-red-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50 text-red-600">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Destruction Warning', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-delete-col-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5">
            <input type="hidden" id="skaaa-del-col-slug">
            <p class="text-sm text-gray-700 mb-3"><?php printf( esc_html__( 'You are about to delete the column %s.', 'skaaa-data-pro' ), '<strong>"<span id="skaaa-del-col-name" class="font-bold text-red-600"></span>"</strong>' ); ?></p>
            <p class="text-sm text-gray-500 m-0 leading-relaxed bg-red-50/50 p-2 rounded">
                <?php esc_html_e( 'All data stored inside this column will vanish into thin air (Undo is NOT possible). Have you carefully considered this?', 'skaaa-data-pro' ); ?>
            </p>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-delete-col-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Turn Your Hands Back', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-del-col-btn" class="px-4 py-2 rounded font-medium text-white bg-red-600 hover:bg-red-700 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Beheaded (Deleted Forever)', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- ======================= MẢNG QUẢN TRỊ TABLE (BẢNG) ======================= -->

<!-- MÀN HÌNH TẠO BẢNG MỚI (CREATE TABLE) -->
<div id="skaaa-create-table-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <!-- Đỉnh Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50 text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-database text-emerald-500"></span> <?php esc_html_e( 'Initialize Data Sheet', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-create-table-modal').classList.add('hidden');"></span>
        </div>
        
        <!-- Không gian làm việc Nhập liệu -->
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Data Table Name', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-new-table-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" placeholder=__( 'eg: Students, Products...', 'skaaa-data-pro' ) autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Icon (Icon)', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-new-table-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-media-spreadsheet"><?php esc_html_e( 'Basic table', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-admin-users"><?php esc_html_e( 'User / User', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-cart"><?php esc_html_e( 'Cart / Order', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-products"><?php esc_html_e( 'Products/Goods', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-calendar-alt"><?php esc_html_e( 'Calendar / Events', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-format-gallery"><?php esc_html_e( 'Images / Media', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Belongs to App (Context Group)', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-new-table-group" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <?php 
                    $apps = \Skaaa\Data\Core\App_Manager::get_apps();
                    foreach ( $apps as $app_key => $app_data ) {
                        echo '<option value="' . esc_attr($app_key) . '">' . esc_html($app_data['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Cẳng cuối Action -->
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-create-table-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-create-table-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-500 hover:bg-emerald-600 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Create Table', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH SỬA TÊN BẢNG (RENAME TABLE) -->
<div id="skaaa-rename-table-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-edit text-emerald-500"></span> <?php esc_html_e( 'Edit Table Name', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-rename-table-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="skaaa-rename-table-slug">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'New Nickname', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-rename-table-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white" autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Icon', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-rename-table-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-media-spreadsheet"><?php esc_html_e( 'Basic table', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-admin-users"><?php esc_html_e( 'User / User', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-cart"><?php esc_html_e( 'Cart / Order', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-products"><?php esc_html_e( 'Products/Goods', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-calendar-alt"><?php esc_html_e( 'Calendar / Events', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-format-gallery"><?php esc_html_e( 'Images / Media', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Belongs to App (Context Group)', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-rename-table-group" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
                    <?php 
                    foreach ( $apps as $app_key => $app_data ) {
                        echo '<option value="' . esc_attr($app_key) . '">' . esc_html($app_data['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-rename-table-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-rename-table-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Save Information', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH DELETE TABLE LỚN VỚI CHỮ KÝ BẢO MẬT (XACNHAN) -->
<div id="skaaa-delete-table-modal" class="hidden fixed inset-0 bg-red-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[450px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50 text-red-600">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-warning" style="font-size:24px;width:24px;height:24px;margin-top:-2px"></span> <?php esc_html_e( 'DESTROY ENTIRE TABLE', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-delete-table-modal').classList.add('hidden'); document.getElementById('skaaa-delete-confirm-input').value='';"></span>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="skaaa-del-tbl-slug">
            <p class="text-[15px] text-gray-700 mb-3"><?php printf( esc_html__( 'This action will permanently delete the table %s from the computer.', 'skaaa-data-pro' ), '<strong class="text-red-600">"<span id="skaaa-del-tbl-name"></span>"</strong>' ); ?></p>
            <div class="bg-red-50/70 p-4 rounded text-sm text-gray-700 leading-relaxed mb-4 border border-red-100">
                <?php esc_html_e( '⚠️ Warning: All columns and records (customer data, orders, invoices...) inside this table will be deleted and cannot be recovered.', 'skaaa-data-pro' ); ?>
            </div>
            
            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide"><?php printf( esc_html__( 'Type the word %s to complete', 'skaaa-data-pro' ), '<span class="bg-gray-200 text-red-600 px-1 py-0.5 rounded font-mono">CONFIRM</span>' ); ?></label>
            <input type="text" id="skaaa-delete-confirm-input" class="w-full border-red-200 rounded-md shadow-inner focus:border-red-500 focus:ring-red-500 p-3 bg-red-50/30 text-red-700 font-mono tracking-widest text-center" autocomplete="off" placeholder="<?php echo esc_attr( __( 'Type the word CONFIRM here', 'skaaa-data-pro' ) ); ?>">
        </div>

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-delete-table-modal').classList.add('hidden'); document.getElementById('skaaa-delete-confirm-input').value='';" class="px-6 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-del-table-btn" class="px-6 py-2 rounded font-medium text-white bg-red-600 hover:bg-red-700 shadow-sm transition flex items-center gap-2 opacity-50 cursor-not-allowed" disabled>
                <span class="dashicons dashicons-trash mt-0.5" style="font-size: 16px;"></span> <?php esc_html_e( 'Accept Risk & Delete', 'skaaa-data-pro' ); ?>
            </button>
        </div>
    </div>
</div>
<!-- ======================= MẢNG QUẢN TRỊ APP BLUEPRINT (WORKSPACE) ======================= -->

<!-- MÀN HÌNH TẠO ỨNG DỤNG (CREATE APP) -->
<div id="skaaa-create-app-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-indigo-50 text-indigo-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-portfolio text-indigo-500"></span> <?php esc_html_e( 'Initialize App Space', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-create-app-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'App Name', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-new-app-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white" placeholder=__( 'eg: Scheduling App, Accounting App...', 'skaaa-data-pro' ) autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Icon (Icon)', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-new-app-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-portfolio">Portfolio / Vali</option>
                    <option value="dashicons-cart"><?php esc_html_e( 'Cart / Order', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-groups"><?php esc_html_e( 'Group/Team', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-calendar-alt"><?php esc_html_e( 'Calendar / Events', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-welcome-learn-more"><?php esc_html_e( 'Education/Courses', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-networking"><?php esc_html_e( 'Network / Network', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>
            
            <p class="text-xs text-indigo-600 m-0 bg-indigo-50 p-2 rounded">
                <?php esc_html_e( 'Smart Objects will group multiple Tables under the same 1 App Blueprint.', 'skaaa-data-pro' ); ?>
            </p>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-create-app-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-create-app-btn" class="px-4 py-2 rounded font-medium text-white bg-indigo-500 hover:bg-indigo-600 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-plus-alt2 mt-0.5" style="font-size:16px;"></span> <?php esc_html_e( 'Create Workspace', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH ĐỔI TÊN ỨNG DỤNG (RENAME APP) -->
<div id="skaaa-rename-app-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-indigo-50 text-indigo-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-admin-generic text-indigo-500"></span> <?php esc_html_e( 'Workspace Settings', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-rename-app-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <input type="hidden" id="skaaa-rename-app-slug">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'New Nickname', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-rename-app-name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white" autocomplete="off">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Icon', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-rename-app-icon" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="dashicons-portfolio">Portfolio / Vali</option>
                    <option value="dashicons-cart"><?php esc_html_e( 'Cart / Order', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-groups"><?php esc_html_e( 'Group/Team', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-calendar-alt"><?php esc_html_e( 'Calendar / Events', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-welcome-learn-more"><?php esc_html_e( 'Education/Courses', 'skaaa-data-pro' ); ?></option>
                    <option value="dashicons-networking"><?php esc_html_e( 'Network / Network', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Unauthorized Redirect URL', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-rename-app-redirect" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white" autocomplete="off" placeholder="e.g. /login or https://example.com/login">
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-rename-app-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Cancel', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-rename-app-btn" class="px-4 py-2 rounded font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm text-sm transition flex items-center gap-1"><?php esc_html_e( 'Save Information', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH DELETE APP LỚN VỚI CHỮ KÝ BẢO MẬT (XACNHAN) -->
<div id="skaaa-delete-app-modal" class="hidden fixed inset-0 bg-red-900/60 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[450px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50 text-red-600">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-warning" style="font-size:24px;width:24px;height:24px;margin-top:-2px"></span> <?php esc_html_e( 'DISSOLVE APPLICATION', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-delete-app-modal').classList.add('hidden'); document.getElementById('skaaa-delete-app-confirm-input').value='';"></span>
        </div>
        
        <div class="p-6">
            <input type="hidden" id="skaaa-del-app-slug">
            <p class="text-[15px] text-gray-700 mb-3"><?php printf( esc_html__( 'This action will delete the Space %s.', 'skaaa-data-pro' ), '<strong class="text-red-600">"<span id="skaaa-del-app-name"></span>"</strong>' ); ?></p>
            <div class="bg-orange-50/70 p-4 rounded text-sm text-gray-700 leading-relaxed mb-4 border border-orange-100">
                <?php esc_html_e( '⚠️ Note: Tables inside this application WILL NOT BE DELETED. All tables will be moved to Default Space.', 'skaaa-data-pro' ); ?>
            </div>
            
            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide"><?php printf( esc_html__( 'Type the word %s to complete', 'skaaa-data-pro' ), '<span class="bg-gray-200 text-red-600 px-1 py-0.5 rounded font-mono">CONFIRM</span>' ); ?></label>
            <input type="text" id="skaaa-delete-app-confirm-input" class="w-full border-red-200 rounded-md shadow-inner focus:border-red-500 focus:ring-red-500 p-3 bg-red-50/30 text-red-700 font-mono tracking-widest text-center" autocomplete="off" placeholder="<?php echo esc_attr( __( 'Type the word CONFIRM here', 'skaaa-data-pro' ) ); ?>">
        </div>

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-delete-app-modal').classList.add('hidden'); document.getElementById('skaaa-delete-app-confirm-input').value='';" class="px-6 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-del-app-btn" class="px-6 py-2 rounded font-medium text-white bg-red-600 hover:bg-red-700 shadow-sm transition flex items-center gap-2 opacity-50 cursor-not-allowed" disabled>
                <span class="dashicons dashicons-trash mt-0.5" style="font-size: 16px;"></span> <?php esc_html_e( 'Accept Risk & Dissolve', 'skaaa-data-pro' ); ?>
            </button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH NHẬP (IMPORT) APP BLUEPRINT -->
<div id="skaaa-import-app-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50 text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-upload text-emerald-500"></span> <?php esc_html_e( 'Import Blueprint', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-import-app-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Upload the JSON file', 'skaaa-data-pro' ); ?></label>
                <div class="w-full flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md bg-gray-50 hover:bg-gray-100 transition py-6 relative">
                    <input type="file" id="skaaa-import-app-file" accept=".json" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="text-center pointer-events-none">
                        <span class="dashicons dashicons-media-document text-gray-400 mb-1" style="font-size:32px;width:32px;height:32px;"></span>
                        <p class="text-sm font-medium text-gray-600 m-0" id="skaaa-import-file-name"><?php esc_html_e( 'Drag and Drop or Select File (.json)', 'skaaa-data-pro' ); ?></p>
                        <p class="text-[11px] text-gray-400 mt-1"><?php esc_html_e( 'The Flat Table structure will automatically unpack', 'skaaa-data-pro' ); ?></p>
                    </div>
                </div>
            </div>
            <div id="skaaa-import-loading-state" class="hidden flex items-center justify-center gap-2 py-2">
                <span class="dashicons dashicons-update animate-spin text-emerald-500"></span>
                <span class="text-sm text-gray-600 font-medium"><?php esc_html_e( 'Casting data, please wait...', 'skaaa-data-pro' ); ?></span>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-import-app-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-import-app-btn" class="px-4 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1 opacity-50 cursor-not-allowed" disabled><?php esc_html_e( 'Start Importing', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- ======================= MẢNG QUẢN TRỊ VIEW (VIEW TOOLS) ======================= -->

<!-- MÀN HÌNH POPUP QUICK FILTER (LỌC DỮ LIỆU) -->
<div id="skaaa-filter-data-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[400px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50 text-emerald-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-filter text-emerald-500"></span> <?php esc_html_e( 'Filter Data', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-filter-data-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Filter By Column', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-filter-field" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700">
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
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Condition', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-filter-op" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white text-gray-700 font-mono">
                    <option value="like" <?php selected(isset($_GET['filter_op']) ? $_GET['filter_op'] : '', 'like'); ?>><?php esc_html_e( 'Contains keywords (Include)', 'skaaa-data-pro' ); ?></option>
                    <option value="eq" <?php selected(isset($_GET['filter_op']) ? $_GET['filter_op'] : '', 'eq'); ?>><?php esc_html_e( 'Exact equals (=)', 'skaaa-data-pro' ); ?></option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Comparative value', 'skaaa-data-pro' ); ?></label>
                <input type="text" id="skaaa-filter-val" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-2 bg-white placeholder-gray-300" placeholder=__( 'eg: Product A...', 'skaaa-data-pro' ) autocomplete="off" value="<?php echo isset($_GET['filter_val']) ? esc_attr(wp_unslash($_GET['filter_val'])) : ''; ?>">
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-filter-data-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-filter-btn" class="px-5 py-2 rounded font-medium text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-saved mt-0.5"></span> <?php esc_html_e( 'Apply Filter', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH POPUP GỘP NHÓM (GROUP BY) -->
<div id="skaaa-group-data-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[350px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-indigo-50 text-indigo-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-networking text-indigo-500"></span> <?php esc_html_e( 'Group Records', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-group-data-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <p class="text-xs text-gray-500 m-0 leading-relaxed">
                <?php esc_html_e( 'Automatically group rows with the same current value together.', 'skaaa-data-pro' ); ?>
            </p>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Select columns to merge', 'skaaa-data-pro' ); ?></label>
                <select id="skaaa-group-field" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-white text-gray-700 font-medium">
                    <option value=""><?php esc_html_e( '-- No grouping --', 'skaaa-data-pro' ); ?></option>
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
            <button onclick="document.getElementById('skaaa-group-data-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-group-btn" class="px-5 py-2 rounded font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-image-filter relative mt-0.5"></span> <?php esc_html_e( 'Group Now', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<!-- MÀN HÌNH APP PORTAL SETTINGS -->
<div id="skaaa-portal-settings-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[99999] flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-[500px] flex flex-col overflow-hidden animate-[pulse_0.2s_ease-out]">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-blue-50 text-blue-700">
            <h3 class="font-bold m-0 flex items-center gap-1.5"><span class="dashicons dashicons-admin-site-alt3 text-blue-500"></span> <?php esc_html_e( 'App Portal Settings', 'skaaa-data-pro' ); ?></h3>
            <span class="dashicons dashicons-no-alt cursor-pointer text-gray-400 hover:text-red-500" onclick="document.getElementById('skaaa-portal-settings-modal').classList.add('hidden');"></span>
        </div>
        
        <div class="p-5 flex flex-col gap-4">
            <p class="text-xs text-gray-500 m-0 leading-relaxed">
                <?php esc_html_e( 'Turn this data table into an independent App with its own URL (e.g. /customers).', 'skaaa-data-pro' ); ?>
            </p>
            
            <input type="hidden" id="skaaa-portal-table-slug" value="<?php echo esc_attr(isset($_GET['table']) ? $_GET['table'] : ''); ?>">
            
            <div class="flex items-center gap-2">
                <input type="checkbox" id="skaaa-portal-active" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="skaaa-portal-active" class="text-sm font-bold text-gray-700"><?php esc_html_e( 'Activate App Portal', 'skaaa-data-pro' ); ?></label>
            </div>

            <div id="skaaa-portal-fields-wrapper" class="opacity-50 pointer-events-none transition-opacity">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Access path (URL Slug)', 'skaaa-data-pro' ); ?></label>
                    <div class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">/</span>
                        <input type="text" id="skaaa-portal-slug" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white" placeholder="vd: danh-sach-khach-hang" autocomplete="off">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Allowed Roles', 'skaaa-data-pro' ); ?></label>
                    <input type="text" id="skaaa-portal-roles" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white" placeholder="vd: administrator,editor" autocomplete="off">
                    <p class="mt-1 text-[11px] text-gray-500"><?php esc_html_e( 'Permissions are separated by commas. ', 'skaaa-data-pro' ); ?></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Data display mode', 'skaaa-data-pro' ); ?></label>
                    <select id="skaaa-portal-view-mode" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white text-gray-700">
                        <option value="readonly"><?php esc_html_e( 'Read-only', 'skaaa-data-pro' ); ?></option>
                        <option value="crud"><?php esc_html_e( 'Read & Write (CRUD Mode)', 'skaaa-data-pro' ); ?></option>
                    </select>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide"><?php esc_html_e( 'Unauthorized Redirect', 'skaaa-data-pro' ); ?></label>
                    <input type="text" id="skaaa-portal-unauthorized-redirect" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white" placeholder=__( 'eg: /dang-nhap or https://...', 'skaaa-data-pro' ) autocomplete="off">
                    <p class="mt-1 text-[11px] text-gray-500"><?php esc_html_e( 'If the Portal is locked (with Roles permission), non-logged users will be pushed to this page instead of the default wp-login. ', 'skaaa-data-pro' ); ?></p>
                </div>

                <!-- NEW: AUTO-GENERATE UI SECTION -->
                <div x-data="skaaaPortalGenerator()" x-init="initData()" id="skaaa-auto-generate-ui-section" class="relative mt-5 p-5 bg-gradient-to-br from-indigo-50/50 to-blue-50/30 border border-indigo-100 rounded-lg">
                    <!-- Badge/Label -->
                    <div class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 text-[11px] font-bold px-2 py-0.5 rounded uppercase tracking-wide flex items-center gap-1">
                        <span class="dashicons dashicons-superhero" style="font-size:12px;width:12px;height:12px;"></span> <?php esc_html_e( 'New', 'skaaa-data-pro' ); ?>
                    </div>

                    <div class="mb-4 mt-1">
                        <h3 class="text-[15px] font-semibold text-gray-800 flex items-center gap-2">
                            <?php esc_html_e( 'Auto-Generate UI', 'skaaa-data-pro' ); ?>
                        </h3>
                        <p class="text-[13px] text-gray-500 mt-1">
                            <?php esc_html_e( 'The system will automatically create Organisms, List View, Detail View and bind data configuration with a single click.', 'skaaa-data-pro' ); ?>
                        </p>
                    </div>

                    <!-- Gợi ý thông minh (Chỉ hiện khi thiếu cột long_text) -->
                    <div x-show="needsLongText && !isSuccess" style="display: none;" x-transition class="bg-amber-50/80 border border-amber-200 rounded-md p-3 mb-4 flex items-start gap-3 shadow-sm transition-opacity" :class="{ 'opacity-50 pointer-events-none': isGenerating }">
                        <div class="text-amber-500 mt-0.5">
                            <span class="dashicons dashicons-lightbulb"></span>
                        </div>
                        <div class="flex-1">
                            <label class="flex items-start gap-2 cursor-pointer group/tip">
                                <input type="checkbox" x-model="addLongText" class="mt-[3px] w-4 h-4 text-amber-500 border-amber-300 rounded focus:ring-amber-500 cursor-pointer">
                                <span class="text-[13px] text-gray-800 font-medium leading-snug group-hover/tip:text-gray-900">
                                    <?php esc_html_e( 'This table does not have a Content field yet. Automatically add the Post (Gutenberg) field for me.', 'skaaa-data-pro' ); ?>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Trạng thái mặc định / Đang chạy: Nút Generate -->
                    <button x-show="!isSuccess" type="button" @click="generatePortal()" :disabled="isGenerating" 
                            class="w-full flex justify-center items-center gap-2 text-white font-medium py-2.5 px-4 rounded-md shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :class="isGenerating ? 'bg-indigo-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'">
                        <span class="dashicons" :class="isGenerating ? 'dashicons-update animate-spin' : 'dashicons-admin-magic'"></span>
                        <span x-text="isGenerating ? '<?php echo esc_js( __( 'Generating...', 'skaaa-data-pro' ) ); ?>' : '<?php echo esc_js( __( 'Create UI', 'skaaa-data-pro' ) ); ?>'"></span>
                    </button>

                    <!-- Trạng thái thành công -->
                    <div x-show="isSuccess" style="display: none;" x-transition class="mt-2">
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4 text-center">
                            <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-green-100 mb-2">
                                <span class="dashicons dashicons-yes text-green-600" style="font-size:24px;width:24px;height:24px;"></span>
                            </div>
                            <h4 class="text-[14px] font-semibold text-green-800"><?php esc_html_e( 'Initialization successful!', 'skaaa-data-pro' ); ?></h4>
                            <p class="text-[13px] text-green-600 mt-1"><?php esc_html_e( 'Organism, List View and Detail View have been created.', 'skaaa-data-pro' ); ?></p>
                        </div>
                        <div class="grid grid-cols-3 gap-3 mt-3">
                            <a :href="listViewEditorUrl" x-show="listViewEditorUrl" target="_blank" class="flex justify-center items-center gap-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-medium py-2 px-3 rounded-md text-[13px] transition-colors shadow-sm">
                                <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit List View', 'skaaa-data-pro' ); ?>
                            </a>
                            <a :href="detailViewEditorUrl" x-show="detailViewEditorUrl" target="_blank" class="flex justify-center items-center gap-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-medium py-2 px-3 rounded-md text-[13px] transition-colors shadow-sm">
                                <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit Detail View', 'skaaa-data-pro' ); ?>
                            </a>
                            <a :href="insertViewEditorUrl" x-show="insertViewEditorUrl" target="_blank" class="flex justify-center items-center gap-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-medium py-2 px-3 rounded-md text-[13px] transition-colors shadow-sm">
                                <span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit Create View', 'skaaa-data-pro' ); ?>
                            </a>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <a :href="portalUrl" target="_blank" class="flex justify-center items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-3 rounded-md text-[13px] transition-colors shadow-sm">
                                <span class="dashicons dashicons-external"></span> <?php esc_html_e( 'View Frontend', 'skaaa-data-pro' ); ?>
                            </a>
                            <a :href="themeBuilderUrl" target="_blank" class="flex justify-center items-center gap-2 bg-gray-50 border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium py-2 px-3 rounded-md text-[13px] transition-colors shadow-sm">
                                <span class="dashicons dashicons-admin-customizer"></span> <?php esc_html_e( 'Manage Templates', 'skaaa-data-pro' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('skaaaPortalGenerator', () => ({
                isGenerating: false,
                isSuccess: false,
                needsLongText: false,
                addLongText: true,
                portalUrl: '',
                themeBuilderUrl: '',
                listViewEditorUrl: '',
                detailViewEditorUrl: '',
                insertViewEditorUrl: '',

                initData() {
                    // Lắng nghe sự kiện mở modal để kiểm tra Schema hiện tại có long_text không
                    const openBtn = document.getElementById('skaaa-btn-portal-settings');
                    if (openBtn) {
                        openBtn.addEventListener('click', () => {
                            setTimeout(() => {
                                this.isSuccess = false;
                                this.isGenerating = false;
                                this.checkSchemaForLongText();
                            }, 50);
                        });
                    }
                },
                
                checkSchemaForLongText() {
                    const tableId = document.getElementById('skaaa-portal-table-slug').value;
                    // skaaaGlobalDict được khởi tạo từ PHP ở index
                    const dict = window.skaaaGlobalDict && window.skaaaGlobalDict[tableId] ? window.skaaaGlobalDict[tableId] : null;
                    let hasLongText = false;
                    if (dict) {
                        for (const col in dict) {
                            if (col !== '__table_info' && dict[col].type === 'long_text') {
                                hasLongText = true;
                                break;
                            }
                        }
                    }
                    this.needsLongText = !hasLongText;
                    if (!this.needsLongText) this.addLongText = false;
                },

                async generatePortal() {
                    const tableId = document.getElementById('skaaa-portal-table-slug').value;
                    const slug = document.getElementById('skaaa-portal-slug').value.trim();
                    const rolesInput = document.getElementById('skaaa-portal-roles').value.trim();
                    const viewMode = document.getElementById('skaaa-portal-view-mode').value;
                    const redirectUrl = document.getElementById('skaaa-portal-unauthorized-redirect') ? document.getElementById('skaaa-portal-unauthorized-redirect').value.trim() : '';
                    const active = document.getElementById('skaaa-portal-active').checked;

                    if (!active) {
                        alert(__( 'Please \"Activate App Portal\" and Save configuration before auto-generating.', 'skaaa-data-pro' ));
                        return;
                    }

                    if (!slug) {
                        alert(__( 'Please fill in the Slug URL before creating the theme.', 'skaaa-data-pro' ));
                        return;
                    }

                    this.isGenerating = true;

                    try {
                        const fd = new URLSearchParams();
                        fd.append('action', 'skaaa_data_generate_portal_ui');
                        fd.append('_ajax_nonce', window.skaaaDataConfig.nonce);
                        fd.append('table', tableId);
                        fd.append('slug', slug);
                        fd.append('roles', rolesInput);
                        fd.append('view_mode', viewMode);
                        fd.append('unauthorized_redirect_url', redirectUrl);
                        fd.append('add_long_text', this.addLongText ? '1' : '0');

                        const response = await fetch(window.skaaaDataConfig.ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: fd
                        });

                        const res = await response.json();
                        
                        if (res.success) {
                            this.isSuccess = true;
                            this.portalUrl = res.data.portal_url;
                            this.themeBuilderUrl = res.data.theme_builder_url;
                            this.listViewEditorUrl = res.data.list_view_editor_url;
                            this.detailViewEditorUrl = res.data.detail_view_editor_url;
                            this.insertViewEditorUrl = res.data.insert_view_editor_url;
                        } else {
                            alert(res.data?.message || __( 'Error initializing UI', 'skaaa-data-pro' ));
                        }
                    } catch (error) {
                        console.error(error);
                        alert(__( 'Server connection error', 'skaaa-data-pro' ));
                    } finally {
                        this.isGenerating = false;
                    }
                }
            }));
        });
        </script>

        <div class="px-5 py-3 border-t border-blue-100 bg-blue-50/30 flex justify-end gap-2">
            <button onclick="document.getElementById('skaaa-portal-settings-modal').classList.add('hidden');" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-50 text-sm transition"><?php esc_html_e( 'Close', 'skaaa-data-pro' ); ?></button>
            <button id="skaaa-execute-portal-settings-btn" class="px-5 py-2 rounded font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm text-sm transition flex items-center gap-1"><span class="dashicons dashicons-saved mt-0.5"></span> <?php esc_html_e( 'Save Configuration', 'skaaa-data-pro' ); ?></button>
        </div>
    </div>
</div>

<script>
    // Bơm Từ Điển Máy Chủ xuống Biến Toàn Cục Trình Duyệt để AJAX Bypass
    window.skaaaGlobalDict = <?php echo json_encode(isset($all_dict) ? $all_dict : array()); ?>;
</script>
