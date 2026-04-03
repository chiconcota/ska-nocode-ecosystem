<?php
require 'wp-load.php';
global $wpdb;
$meta_keys = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta}");
echo "Total distinct meta_keys: " . count($meta_keys) . "\n";
$values = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_desired_post_slug' LIMIT 5");
print_r($values);
