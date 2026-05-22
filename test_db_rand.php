<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Không dùng SHORTINIT vì ta muốn load toàn bộ WordPress, plugins (ska-logic-engine, ska-no-code-design) để chạy filter render_block
require 'wp-load.php';

echo "<h2>Testing Detail View Block Rendering</h2>";

global $wpdb;
$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';

// Tìm organism Detail View của Phòng Khách Sạn
$detail_org = $wpdb->get_row("SELECT * FROM `$table_organisms` WHERE `type` = 'single' AND `name` LIKE '%Detail View: Phòng Khách Sạn%'", ARRAY_A);

if (!$detail_org) {
    // Thử tìm theo name khác
    $detail_org = $wpdb->get_row("SELECT * FROM `$table_organisms` WHERE `type` = 'single' AND `name` LIKE '%Detail View%'", ARRAY_A);
}

if (!$detail_org) {
    die("Detail View organism not found in database!");
}

echo "<h3>Found Organism: {$detail_org['name']} (ID: {$detail_org['id']})</h3>";

// Giả lập $_GET['id'] = 1 và $_GET['table'] = 'app_quan_ly_dat_phong_khach_san_phong_khach_san'
$_GET['id'] = 1;
$_GET['table'] = 'app_quan_ly_dat_phong_khach_san_phong_khach_san';

// Thử parse và render blocks từ html_content hoặc json_content của organism
$raw_html = $detail_org['html_content'];
echo "<h4>Raw HTML Content (first 500 chars):</h4>";
echo "<pre>" . esc_html(substr($raw_html, 0, 500)) . "</pre>";

$blocks = parse_blocks($raw_html);

// Render từng block
echo "<h4>Rendered Output blocks scanning for select:</h4>";
$found_select = false;

function scan_rendered_blocks($blocks) {
    foreach ($blocks as $block) {
        $rendered = render_block($block);
        if (strpos($block['blockName'], 'ska-builder/select') !== false || strpos($rendered, 'phong_dat') !== false) {
            echo "<h5>Found Select Block: " . esc_html($block['blockName']) . "</h5>";
            echo "Attrs:<pre>";
            print_r($block['attrs']);
            echo "</pre>";
            echo "Rendered HTML:<pre>";
            echo esc_html($rendered);
            echo "</pre>";
        }
        if (!empty($block['innerBlocks'])) {
            scan_rendered_blocks($block['innerBlocks']);
        }
    }
}

scan_rendered_blocks($blocks);
