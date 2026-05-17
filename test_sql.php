<?php
require_once 'wp-load.php';
global $wpdb;
$val_str = '1';
$val_json = wp_json_encode( $val_str );
$query = $wpdb->prepare("SELECT * FROM wp_ska_data_app_courses_courses WHERE ( (JSON_VALID(`teacher_id`) AND (JSON_CONTAINS(`teacher_id`, %s, '$') OR JSON_CONTAINS(`teacher_id`, %s, '$'))) OR FIND_IN_SET(%s, REPLACE(`teacher_id`, ' ', '')) > 0 )", $val_str, $val_json, $val_str);
$rows = $wpdb->get_results($query);
echo json_encode(['query' => $query, 'results' => $rows]);
