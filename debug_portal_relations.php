<?php
/**
 * Temp script to debug portal relations.
 * Accessed via browser: http://ska-core-builder.local/debug_portal_relations.php
 */
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

header('Content-Type: application/json; charset=utf-8');

global $wpdb;
$dict = get_option('ska_data_dictionary', []);

$booking_table = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_phong_khach_san';
$room_table = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong';

$result = [
    'booking_dict' => isset($dict[$booking_table]) ? $dict[$booking_table] : null,
    'room_dict' => isset($dict[$room_table]) ? $dict[$room_table] : null,
    'booking_rows_raw' => $wpdb->get_results("SELECT * FROM `$booking_table` LIMIT 5", ARRAY_A),
    'room_rows_raw' => $wpdb->get_results("SELECT * FROM `$room_table` LIMIT 5", ARRAY_A),
];

if (class_exists('\Ska\Data\Core\Data_Fetcher')) {
    $result['booking_rows_enriched'] = \Ska\Data\Core\Data_Fetcher::get_table_rows($booking_table, [], 5);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
