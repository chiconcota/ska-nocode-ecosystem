<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

global $wpdb;

header('Content-Type: text/plain; charset=UTF-8');

// 1. Show all post types registered
echo "Registered Post Types:\n";
print_r(get_post_types());

// 2. Query posts that might contain the loop or layout
$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type, post_status FROM {$wpdb->posts} WHERE post_status = 'publish' AND (post_content LIKE '%ska-builder%' OR post_content LIKE '%ska-loop%') LIMIT 50", ARRAY_A);
echo "\nPosts with Ska blocks:\n";
foreach ($posts as $p) {
    echo "ID: {$p['ID']} | Title: {$p['post_title']} | Name: {$p['post_name']} | Type: {$p['post_type']} | Status: {$p['post_status']}\n";
    // Check if it contains phong_dat
    if (strpos($p['post_content'], 'phong_dat') !== false) {
        echo "--> CONTAINS phong_dat!\n";
    }
}

// 3. Let's dump all options that contain "ska_theme_builder" or similar configuration
$theme_builder_posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type, post_content FROM {$wpdb->posts} WHERE post_type = 'ska_theme_builder'", ARRAY_A);
echo "\nSka Theme Builder Posts:\n";
foreach ($theme_builder_posts as $p) {
    echo "ID: {$p['ID']} | Title: {$p['post_title']} | Name: {$p['post_name']} | Content length: " . strlen($p['post_content']) . "\n";
    if (strpos($p['post_content'], 'phong') !== false || strpos($p['post_content'], 'dat') !== false) {
        echo "Content:\n" . esc_html($p['post_content']) . "\n";
    }
}
