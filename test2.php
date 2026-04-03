<?php
require 'wp-load.php';
global $wpdb;
$res = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_page_template'");
print_r($res);
