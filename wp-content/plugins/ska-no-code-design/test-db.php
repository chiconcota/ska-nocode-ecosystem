<?php
require_once __DIR__ . '/../../../wp-load.php';
global $wpdb;
$organism = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ska_data_sys_organisms WHERE id = 85", ARRAY_A);
echo json_encode($organism, JSON_PRETTY_PRINT);
