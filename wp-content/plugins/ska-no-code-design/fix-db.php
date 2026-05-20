<?php
require_once __DIR__ . '/../../../wp-load.php';
global $wpdb;
$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';

$organism = $wpdb->get_row("SELECT * FROM {$table_organisms} WHERE id = 85", ARRAY_A);
if ($organism) {
    $json = $organism['json_content'];
    $html = $organism['html_content'];
    
    $new_json = str_replace('wp_app_quan_ly_dat_phong_khach_san_lich_dat_phong', 'wp_ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong', $json);
    $new_html = str_replace('wp_app_quan_ly_dat_phong_khach_san_lich_dat_phong', 'wp_ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong', $html);
    
    $wpdb->update($table_organisms, [
        'json_content' => $new_json,
        'html_content' => $new_html
    ], ['id' => 85]);
    
    echo "Updated successfully!";
} else {
    echo "Organism not found!";
}
