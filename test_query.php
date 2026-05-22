<?php
header('Content-Type: text/plain; charset=utf-8');
require 'wp-load.php';
global $wpdb;

$table_lich = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong';

// Check if table exists
if ($wpdb->get_var("SHOW TABLES LIKE '$table_lich'") !== $table_lich) {
    // try table name with prefix
    $table_lich = $wpdb->prefix . 'ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong';
}

echo "<h3>Target table resolved to: $table_lich</h3>";

// Chèn thêm 1 phòng mới nếu chỉ có 1 phòng
$count = $wpdb->get_var("SELECT COUNT(*) FROM `$table_lich`");
echo "Current count: $count<br>";
if ($count < 2) {
    $inserted = $wpdb->insert($table_lich, array(
        'ten_phong' => 'Phòng President 202',
        'loai_phong' => 'Vip',
        'gia_moi_dem' => 120000,
        'mo_ta_chi_tiet' => 'Rộng rãi thoáng mát',
        'trang_thai' => 'Trống'
    ));
    if ($inserted === false) {
        echo "Insert failed! Error: " . $wpdb->last_error . "<br>";
    } else {
        echo "Insert succeeded! Rows affected: $inserted<br>";
    }
}

echo "<h2>Tables:</h2>";


$tables = $wpdb->get_results("SHOW TABLES LIKE '%app_quan_ly_dat_phong_khach_san%'", ARRAY_N);
foreach ($tables as $t) {
    $table_name = $t[0];
    echo "<h3>Table: $table_name</h3>";
    $columns = $wpdb->get_results("DESCRIBE `$table_name`", ARRAY_A);
    echo "<h4>Columns:</h4><pre>";
    print_r($columns);
    echo "</pre>";
    
    $rows = $wpdb->get_results("SELECT * FROM `$table_name`", ARRAY_A);
    echo "<h4>Rows:</h4><pre>";
    print_r($rows);
    echo "</pre>";
}
