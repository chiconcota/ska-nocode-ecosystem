<?php
require_once 'wp-load.php';
global $wpdb;
$table_name = $wpdb->prefix . 'ska_data_sys_organisms';
$results = $wpdb->get_results( "SELECT id, name, type, created_at, updated_at FROM {$table_name} WHERE type = 'organism' OR type = '' OR type IS NULL ORDER BY id DESC", ARRAY_A );
if ($wpdb->last_error) {
    echo "ERROR: " . $wpdb->last_error . "\n";
} else {
    print_r($results);
}
