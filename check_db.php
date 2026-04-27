<?php
require_once 'wp-load.php';
global $wpdb;
$table = 'wp_ska_data_app_app_viet_de_test_node_logic_test_api_log';
$result = $wpdb->get_row("SELECT * FROM $table WHERE id = 6", ARRAY_A);
print_r($result);
