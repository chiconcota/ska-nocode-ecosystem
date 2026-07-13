<?php
/**
 * Render for Skaaa Form Rich Text
 *
 * @package Skaaa_Builder_Core
 */

$field_name = isset($attributes['field']) ? esc_attr($attributes['field']) : 'noi_dung';
$label      = isset($attributes['label']) ? esc_html($attributes['label']) : __( 'Detailed description', 'skaaa-no-code-design' );
$class_name = isset($attributes['className']) ? esc_attr($attributes['className']) : '';
$tw_classes = isset($attributes['tailwindClasses']) ? esc_attr($attributes['tailwindClasses']) : '';

// HTML cho Frontend 
// Về phía UI, ta sẽ bọc vào AlpineJS x-data="skaaaScratchpad('field_name')"
$wrapper_class = trim("skaaa-form-rich-text $class_name $tw_classes");
$editor_id = 'skaaa_editor_' . str_replace('-', '_', sanitize_title($field_name));
?>

<div class="<?php echo esc_attr($wrapper_class); ?>" x-data="skaaaScratchpad('<?php echo esc_js($field_name); ?>')">
    <style>
    /* Custom TinyMCE Editor and Segmented Tabs Styling */
    .skaaa-form-rich-text .wp-editor-wrap {
        border: 1px solid #cbd5e1 !important; /* slate-300 */
        border-radius: 8px !important;
        overflow: hidden !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
    }
    .skaaa-form-rich-text .wp-editor-wrap:focus-within {
        border-color: #06b6d4 !important; /* cyan-500 */
        box-shadow: 0 0 0 1px #06b6d4 !important;
    }
    .skaaa-form-rich-text .wp-editor-tools {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        background: #f8fafc !important; /* slate-50 */
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 8px 12px !important;
        clear: none !important;
    }
    .skaaa-form-rich-text .wp-editor-tabs {
        float: none !important;
        display: inline-flex !important;
        background: #f1f5f9 !important; /* slate-100 */
        padding: 2px !important;
        border-radius: 6px !important;
        border: 1px solid #e2e8f0 !important;
        margin: 0 !important;
    }
    .skaaa-form-rich-text .wp-switch-editor {
        float: none !important;
        background: transparent !important;
        border: none !important;
        margin: 0 !important;
        padding: 4px 12px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        color: #64748b !important; /* slate-500 */
        border-radius: 4px !important;
        cursor: pointer !important;
        height: auto !important;
        line-height: 1.5 !important;
        transition: all 0.2s ease !important;
    }
    .skaaa-form-rich-text .wp-switch-editor:hover {
        color: #0f172a !important; /* slate-900 */
    }
    .skaaa-form-rich-text .tmce-active .switch-tmce,
    .skaaa-form-rich-text .html-active .switch-html {
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1) !important;
        font-weight: 600 !important;
    }
    .skaaa-form-rich-text .wp-media-buttons {
        float: none !important;
        margin: 0 !important;
    }
    .skaaa-form-rich-text .wp-media-buttons .button {
        margin: 0 !important;
        border-radius: 6px !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
        font-size: 12px !important;
        padding: 4px 12px !important;
        height: auto !important;
        line-height: 1.5 !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .skaaa-form-rich-text .wp-media-buttons .button:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
    }
    /* Hide quicktags/editor toolbar default bottom border to make it clean */
    .skaaa-form-rich-text .quicktags-toolbar {
        border-bottom: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        padding: 6px 12px !important;
    }
    .skaaa-form-rich-text .quicktags-toolbar input.button {
        border-radius: 4px !important;
        font-size: 11px !important;
        padding: 2px 6px !important;
        height: auto !important;
        margin-right: 4px !important;
    }
    /* Hide default wp editor borders */
    .skaaa-form-rich-text .wp-editor-container {
        border: none !important;
    }
    /* Make iframe full width and remove default bottom margin */
    .skaaa-form-rich-text iframe.skaaa-tiny-mce {
        margin: 0 !important;
    }

    /* Scoped styling for Gutenberg Design Editor Modal */
    .skaaa-designer-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 99999 !important;
        background-color: rgba(75, 85, 99, 0.75) !important;
        display: flex;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important; /* Full width full height, remove padding */
    }
    .skaaa-designer-modal-container {
        background-color: #ffffff !important;
        border-radius: 0 !important; /* Full screen, remove rounded corners */
        box-shadow: none !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important; /* Stretch to full width */
        max-height: 100vh !important; /* Stretch to full screen height */
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        border: none !important;
    }
    .skaaa-designer-modal-header {
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background-color: #f8fafc !important;
    }
    .skaaa-designer-modal-body {
        flex: 1 1 0% !important;
        position: relative !important;
        background-color: #ffffff !important;
    }
    .skaaa-designer-modal-loader {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        display: flex;
        align-items: center !important;
        justify-content: center !important;
        background-color: rgba(255, 255, 255, 0.75) !important;
        z-index: 10 !important;
    }
    .skaaa-designer-modal-loader .animate-spin {
        animation: skaaa-spin 1s linear infinite !important;
    }
    .skaaa-designer-modal-iframe {
        width: 100% !important;
        height: 100% !important;
        border: 0 !important;
    }
    .skaaa-designer-modal-footer {
        padding: 0.75rem 1rem !important;
        border-top: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        display: flex !important;
        flex-direction: row-reverse !important;
    }
    @keyframes skaaa-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    </style>

    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-bold text-slate-800">
            <?php echo $label; ?>
        </h3>
        
        <!-- Button để mở Gutenberg Iframe -->
        <button type="button" @click="openDesigner()" class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-md shadow-sm text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            <span class="dashicons dashicons-edit mr-1" style="font-size: 14px; width: 14px; height: 14px;"></span>
            Open Design Editor
        </button>
    </div>

    <!-- Vùng soạn thảo TinyMCE cơ bản -->
    <div class="scratchpad-editor-wrap" wire:ignore>
        <?php 
        // Bắt buộc enqueue editor scripts để Frontend có TinyMCE
        if ( ! function_exists( 'wp_enqueue_editor' ) ) {
            require_once ABSPATH . 'wp-includes/general-template.php';
        }
        wp_enqueue_editor();
        
        wp_editor('', $editor_id, array(
            'textarea_name' => $field_name,
            'media_buttons' => true,
            'editor_class'  => 'skaaa-tiny-mce',
            'textarea_rows' => 10,
            'teeny'         => false,
            'quicktags'     => true
        )); 
        ?>
    </div>

    <!-- Modal chứa Iframe Gutenberg -->
    <template x-teleport="body">
        <div x-show="isOpen" style="display: none;" class="skaaa-designer-modal-overlay fixed inset-0 z-[99999] bg-gray-500 bg-opacity-75 flex items-center justify-center p-0">
            <div class="skaaa-designer-modal-container bg-white shadow-xl w-full h-full max-w-full max-h-screen flex flex-col overflow-hidden" @click.away="closeDesigner(false)">
                
                <div class="skaaa-designer-modal-header px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2" style="margin: 0;">
                        <span class="dashicons dashicons-edit"></span>
                        Trình Thiết Kế: <?php echo $label; ?>
                    </h3>
                    <button type="button" @click="closeDesigner(false)" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" style="border: none; background: transparent; cursor: pointer;">
                        <span class="sr-only">Close</span>
                        <span class="dashicons dashicons-no-alt" style="font-size: 20px; width: 20px; height: 20px;"></span>
                    </button>
                </div>
                
                <div class="skaaa-designer-modal-body flex-1 relative bg-white">
                    <div x-show="isLoading" class="skaaa-designer-modal-loader absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                        <span class="dashicons dashicons-update animate-spin text-indigo-600" style="font-size: 32px; width: 32px; height: 32px; color: #4f46e5;"></span>
                    </div>
                    <iframe id="skaaa_iframe_<?php echo esc_attr($field_name); ?>" x-show="iframeUrl" :src="iframeUrl" class="skaaa-designer-modal-iframe w-full h-full border-0" @load="isLoading = false"></iframe>
                </div>
 
                <div class="skaaa-designer-modal-footer px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-row-reverse">
                    <button type="button" @click="closeDesigner(true)" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" style="background-color: #4f46e5; border: none; color: #fff; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer;">
                        Hoàn tất & Cập nhật
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
