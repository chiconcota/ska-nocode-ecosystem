<?php
/**
 * Skaaa Code Block Frontend Render
 * 
 * @package Skaaa_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

// Ensure Skaaa_Code_Block_Queue is loaded (it is loaded early, but fallback just in case)
if ( ! class_exists( 'Skaaa_Code_Block_Queue' ) ) {
	require_once dirname( dirname( __DIR__ ) ) . '/blocks/class-skaaa-code-block-queue.php';
}

// Trích xuất các thuộc tính của block
$code_type         = isset( $attributes['codeType'] ) ? $attributes['codeType'] : 'inline';
$library_script_id = isset( $attributes['libraryScriptId'] ) ? $attributes['libraryScriptId'] : '';
$inline_code       = isset( $attributes['inlineCode'] ) ? $attributes['inlineCode'] : '';
$location          = isset( $attributes['location'] ) ? $attributes['location'] : 'inline';

// 1. Trường hợp nạp từ Thư viện Scripts
if ( 'library' === $code_type && ! empty( $library_script_id ) ) {
    // Gọi action decoupled gửi sang Skaaa Data Pro để load script và khử trùng lặp
    if ( has_action( 'skaaa_enqueue_custom_script' ) ) {
        do_action( 'skaaa_enqueue_custom_script', $library_script_id );
    } else {
        echo "<!-- Skaaa Code: Script Library is unavailable because Skaaa Data Pro is inactive. -->";
    }
} 
// 2. Trường hợp viết Inline trực tiếp
elseif ( 'inline' === $code_type && ! empty( $inline_code ) ) {
    if ( 'inline' === $location ) {
        // In trực tiếp tại chỗ (giống Custom HTML của WordPress)
        echo $inline_code;
    } else {
        // Đẩy vào hàng đợi để in ở Head/Footer không trùng lặp
        Skaaa_Code_Block_Queue::add( $inline_code, $location );
    }
}
