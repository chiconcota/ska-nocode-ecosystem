<?php
/**
 * Render for Ska Form Rich Text
 *
 * @package Ska_Builder_Core
 */

$field_name = isset($attributes['field']) ? esc_attr($attributes['field']) : 'noi_dung';
$label      = isset($attributes['label']) ? esc_html($attributes['label']) : 'Mô tả chi tiết';
$class_name = isset($attributes['className']) ? esc_attr($attributes['className']) : '';
$tw_classes = isset($attributes['tailwindClasses']) ? esc_attr($attributes['tailwindClasses']) : '';

// HTML cho Frontend 
// Về phía UI, ta sẽ bọc vào AlpineJS x-data="skaScratchpad('field_name')"
$wrapper_class = trim("ska-form-rich-text $class_name $tw_classes");
$editor_id = 'ska_editor_' . str_replace('-', '_', sanitize_title($field_name));
?>

<div class="<?php echo esc_attr($wrapper_class); ?>" x-data="skaScratchpad('<?php echo esc_js($field_name); ?>')">
    <div class="flex items-center justify-between mb-2">
        <label class="block text-lg font-bold text-gray-800">
            <?php echo $label; ?>
        </label>
        
        <!-- Button để mở Gutenberg Iframe -->
        <button type="button" @click="openDesigner()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span class="dashicons dashicons-edit mr-1" style="font-size: 16px; width: 16px; height: 16px;"></span>
            Open Design Editor
        </button>
    </div>

    <!-- Vùng soạn thảo TinyMCE cơ bản -->
    <div class="scratchpad-editor-wrap bg-white text-black border border-gray-300 rounded-md shadow-sm overflow-hidden" wire:ignore>
        <?php 
        // Bắt buộc enqueue editor scripts để Frontend có TinyMCE
        if ( ! function_exists( 'wp_enqueue_editor' ) ) {
            require_once ABSPATH . 'wp-includes/general-template.php';
        }
        wp_enqueue_editor();
        
        wp_editor('', $editor_id, array(
            'textarea_name' => $field_name,
            'media_buttons' => true,
            'editor_class'  => 'ska-tiny-mce',
            'textarea_rows' => 10,
            'teeny'         => false,
            'quicktags'     => true
        )); 
        ?>
    </div>

    <!-- Modal chứa Iframe Gutenberg -->
    <template x-teleport="body">
        <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-[99999] bg-gray-500 bg-opacity-75 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full h-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden" @click.away="closeDesigner()">
                
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2">
                        <span class="dashicons dashicons-edit"></span>
                        Trình Thiết Kế: <?php echo $label; ?>
                    </h3>
                    <button type="button" @click="closeDesigner()" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <span class="dashicons dashicons-no-alt" style="font-size: 20px; width: 20px; height: 20px;"></span>
                    </button>
                </div>
                
                <div class="flex-1 relative bg-white">
                    <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                        <span class="dashicons dashicons-update animate-spin text-indigo-600" style="font-size: 32px; width: 32px; height: 32px;"></span>
                    </div>
                    <iframe x-show="iframeUrl" :src="iframeUrl" class="w-full h-full border-0" @load="isLoading = false"></iframe>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-row-reverse">
                    <button type="button" @click="closeDesigner()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Hoàn tất & Cập nhật
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
