<?php
require_once 'wp-load.php';
global $wpdb;
$rows = $wpdb->get_results("SELECT * FROM wp_ska_data_app_courses_courses");
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
