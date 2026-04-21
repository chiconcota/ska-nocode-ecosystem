<?php
require_once 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
echo "<pre>";
print_r($wpdb->get_results("DESCRIBE $table"));
print_r($wpdb->get_results("SELECT * FROM $table"));
echo "</pre>";
