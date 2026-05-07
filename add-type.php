<?php
require_once 'wp-load.php';
global $wpdb;

$table_name = $wpdb->prefix . 'ska_data_sys_organisms';

$query = "ALTER TABLE {$table_name} ADD COLUMN `type` varchar(50) DEFAULT NULL AFTER `id`";
$result = $wpdb->query($query);

if ($result !== false) {
    echo "Column 'type' added successfully to $table_name!";
} else {
    echo "Failed to add column: " . $wpdb->last_error;
}
