<?php
require 'wp-load.php';
global $wpdb;

// Thử mock chức năng get_results trực tiếp với 1 post bất kỳ để xem SQL lỗi hay ko có data
$post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='post' LIMIT 1");

$meta_key = '_wp_page_template';

if ($post_id) {
    echo "Testing post ID: $post_id\n";
    $val = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s AND post_id=%d", $meta_key, $post_id));
    var_dump($val);
} else {
    echo "No posts found\n";
}
