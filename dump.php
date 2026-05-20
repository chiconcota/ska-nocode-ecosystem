<?php
require 'wp-load.php';

$org_data = array(
    'type' => 'archive',
    'name' => 'List View: test',
    'json_content' => wp_json_encode(array('test' => 1)),
    'html_content' => '<!-- test -->'
);
$res = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');

global $wpdb;
echo "<pre>";
var_dump($res);
var_dump($wpdb->last_error);
echo "</pre>";
