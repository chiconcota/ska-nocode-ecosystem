<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

header('Content-Type: application/json; charset=utf-8');

global $wpdb;

$templates_table = $wpdb->prefix . 'ska_data_sys_theme_templates';
$organisms_table = $wpdb->prefix . 'ska_data_sys_organisms';

$templates = $wpdb->get_results("SELECT * FROM `$templates_table` WHERE conditions LIKE '%phong-khach-san%'", ARRAY_A);
$organisms = [];
if (!empty($templates)) {
    foreach ($templates as $t) {
        $org_id = $t['organism_id'];
        $org = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$organisms_table` WHERE id = %d", $org_id), ARRAY_A);
        if ($org) {
            $organisms[] = $org;
        }
    }
}

// Lấy tất cả organisms cho chắc
$all_organisms = $wpdb->get_results("SELECT id, name, type FROM `$organisms_table`", ARRAY_A);

echo json_encode([
    'templates' => $templates,
    'organisms' => $organisms,
    'all_organisms' => $all_organisms
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
