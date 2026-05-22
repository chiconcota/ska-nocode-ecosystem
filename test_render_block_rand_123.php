<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'wp-load.php';

echo "<h2>Testing Detail View Block Rendering (Uncached)</h2>";

global $wpdb;
$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';

$detail_org = $wpdb->get_row("SELECT * FROM `$table_organisms` WHERE `type` = 'single' AND `name` LIKE '%Detail View: Phòng Khách Sạn%'", ARRAY_A);
if (!$detail_org) {
    $detail_org = $wpdb->get_row("SELECT * FROM `$table_organisms` WHERE `type` = 'single' AND `name` LIKE '%Detail View%'", ARRAY_A);
}

if (!$detail_org) {
    die("Detail View organism not found in database!");
}

echo "<h3>Found Organism: {$detail_org['name']} (ID: {$detail_org['id']})</h3>";

$_GET['id'] = 1;
$_GET['table'] = 'app_quan_ly_dat_phong_khach_san_phong_khach_san';

$raw_html = $detail_org['html_content'];
$blocks = parse_blocks($raw_html);

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
