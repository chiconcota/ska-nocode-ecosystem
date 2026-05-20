<?php
require 'wp-load.php';
$org_data = array(
    'type' => 'archive',
    'name' => 'List View: Test',
    'json_content' => wp_json_encode(array()),
    'html_content' => '<!-- test -->'
);
$list_org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');
echo "List Org ID: "; var_dump($list_org_id);
