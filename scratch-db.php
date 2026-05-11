<?php
require 'wp-load.php';
global $wpdb;
$results = $wpdb->get_results("SELECT * FROM wp_ska_data_sys_presets WHERE category = 'tokens'");
print_r($results);
