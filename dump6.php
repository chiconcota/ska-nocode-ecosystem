<?php
require 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
$res = $wpdb->get_row("SELECT * FROM $table WHERE id = 30");
echo "<pre>";
print_r($res);
echo "</pre>";
