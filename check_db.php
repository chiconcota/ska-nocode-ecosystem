<?php
require_once 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_presets';
$rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
print_r($rows);
