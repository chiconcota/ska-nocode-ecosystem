<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

global $wpdb;
$dict = get_option('ska_data_dictionary', []);

// Tìm các bảng có chứa 'quan_ly_dat_phong_khach_san'
$tables = $wpdb->get_col("SHOW TABLES LIKE '%quan_ly_dat_phong_khach_san%'");

$result = [
    'tables' => $tables,
    'relevant_dictionary' => []
];

foreach ($tables as $t) {
    if (isset($dict[$t])) {
        $result['relevant_dictionary'][$t] = $dict[$t];
    }
}

// Lấy thêm 5 hàng của mỗi bảng để xem cấu trúc thực tế
foreach ($tables as $t) {
    $rows = $wpdb->get_results("SELECT * FROM `$t` LIMIT 5", ARRAY_A);
    $result['sample_data'][$t] = $rows;
}

file_put_contents(__DIR__ . '/ska-debug-relations.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
unlink(__FILE__);
