<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

global $wpdb;

// Query theme builder templates (post type ska_theme_builder or similar)
$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_content, post_type FROM {$wpdb->posts} WHERE post_type IN ('ska_theme_builder', 'ska_organism') OR post_name LIKE '%phong-khach-san%' OR post_title LIKE '%Phòng Khách Sạn%'", ARRAY_A);

header('Content-Type: text/plain; charset=UTF-8');
echo "Theme Builder Posts:\n";
foreach ($posts as $p) {
    echo "ID: {$p['ID']} | Title: {$p['post_title']} | Name: {$p['post_name']} | Type: {$p['post_type']}\n";
    echo "Content:\n" . esc_html($p['post_content']) . "\n";
    echo "--------------------------------------------------\n";
}

// Query sys organisms flat table
if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ska_data_sys_organisms'") ) {
    $organisms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ska_data_sys_organisms WHERE name LIKE '%phong-khach-san%' OR name LIKE '%lich-dat-phong%'", ARRAY_A);
    echo "\nSys Organisms:\n";
    print_r($organisms);
}
