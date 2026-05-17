<?php
require 'wp-load.php';
global $wpdb;
$rows = $wpdb->get_results('SELECT * FROM wp_ska_data_courses LIMIT 5', ARRAY_A);
print_r($rows);
