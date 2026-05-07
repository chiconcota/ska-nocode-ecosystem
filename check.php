<?php
require_once 'wp-load.php';
global $wpdb;

echo "<h2>Checking Both Tables</h2>";

$old_table = $wpdb->prefix . 'ska_data_organisms';
$new_table = $wpdb->prefix . 'ska_data_sys_organisms';

$old_records = $wpdb->get_results( "SELECT * FROM {$old_table}", ARRAY_A );
echo "<pre><h3>Old Table: $old_table</h3>";
print_r($old_records);
echo "</pre>";

$new_records = $wpdb->get_results( "SELECT * FROM {$new_table}", ARRAY_A );
echo "<pre><h3>New Table: $new_table</h3>";
print_r($new_records);
echo "</pre>";
