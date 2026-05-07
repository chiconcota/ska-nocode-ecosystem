<?php
require_once 'wp-load.php';
global $wpdb;
$table_name = $wpdb->prefix . 'ska_data_sys_organisms';
$columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
echo "<pre>";
print_r($columns);
echo "</pre>";
