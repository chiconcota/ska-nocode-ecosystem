<?php
require 'wp-load.php';
global $wpdb;

$table = $wpdb->prefix . 'ska_data_sys_organisms';
var_dump(strpos($table, $wpdb->prefix . 'ska_data_'));

$cols = \Ska\Data\Core\Data_Fetcher::get_table_columns($table);
echo "Columns: "; print_r($cols);
