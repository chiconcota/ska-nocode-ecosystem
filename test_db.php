<?php
require 'wp-load.php';
global $wpdb;
$results = $wpdb->get_results("SELECT id, name FROM ska_data_sys_organisms ORDER BY id DESC LIMIT 5");
print_r($results);
