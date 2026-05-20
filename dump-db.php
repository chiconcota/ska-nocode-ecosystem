<?php
require 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
print_r($wpdb->get_results("DESCRIBE $table"));
