<?php
/**
 * Render logic for ska-builder/organism-ref.
 * Reads the cached organism JSON from uploads/ska-data/organisms.json
 * and outputs the corresponding html_content for Zero-Query rendering.
 *
 * @package Ska_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $attributes['organismId'] ) ) {
    echo '<div style="padding:10px; border:1px dashed gray; color:gray;">[Ska Symbol] No organism ID provided.</div>';
    return;
}

$organism_id = sanitize_text_field( $attributes['organismId'] );

// Locate cache file
$upload_dir = wp_upload_dir();
$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/organisms.json';

if ( ! file_exists( $cache_file ) ) {
    echo '<div style="padding:10px; border:1px dashed red; color:red;">[Ska Symbol] organisms.json not found!</div>';
    return;
}

// Read cache JSON
$file_contents = file_get_contents( $cache_file );
if ( empty( $file_contents ) ) {
    echo '<div style="padding:10px; border:1px dashed red; color:red;">[Ska Symbol] organisms.json is empty!</div>';
    return;
}

$organisms = json_decode( $file_contents, true );
if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $organisms ) ) {
    echo '<div style="padding:10px; border:1px dashed red; color:red;">[Ska Symbol] JSON Parse Error!</div>';
    return;
}

// Find Organism
$found_html = '';
foreach ( $organisms as $org ) {
    if ( isset( $org['id'] ) && (string) $org['id'] === (string) $organism_id ) {
        $found_html = isset( $org['html_content'] ) ? $org['html_content'] : '';
        break;
    }
}

if ( empty( $found_html ) ) {
    $error_ui = sprintf(
        '<div style="padding:16px; border:2px dashed #f43f5e; background:#fff1f2; color:#be123c; border-radius:6px; font-family:sans-serif;">
            <strong style="display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Ska Symbol (ID: %s) Is Empty
            </strong>
             <p style="margin-top:8px; font-size:13px; margin-bottom:0;">Bản mẫu này chưa được lưu HTML (Hoặc do nó được tạo ra trước bản cập nhật mới nhất). Vui lòng mở lại Symbol gốc và bấm Cập nhật (Update) để hệ thống tự render lại HTML Content nhé!</p>
        </div>',
        esc_html( $organism_id )
    );
    echo $error_ui;
    return;
}

$output = do_blocks( $found_html );

if ( empty( trim( $output ) ) ) {
    echo sprintf(
        '<div style="padding:16px; border:2px dashed #eab308; background:#fefce8; color:#a16207; border-radius:6px;">
            <strong>Warning:</strong> Symbol ID %s returned empty HTML after do_blocks().
        </div>',
        esc_html( $organism_id )
    );
    return;
}

echo $output;
return;
