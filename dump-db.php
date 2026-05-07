<?php
require_once 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
$rows = $wpdb->get_results("SELECT id, type, name FROM {$table} LIMIT 10", ARRAY_A);
print_r($rows);
