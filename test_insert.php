<?php
require 'wp-load.php';

$template_data = array(
    'name' => 'Test Template',
    'location' => 'app_layout',
    'organism_id' => 1,
    'conditions' => wp_json_encode(array(
        array('rule' => 'specific_portal_list', 'value' => 'test', 'type' => 'include')
    )),
    'is_active' => 1
);

$result = apply_filters('ska_data_insert_record', false, $template_data, 'ska_data_sys_theme_templates');
var_dump($result);

global $wpdb;
echo "Last error: " . $wpdb->last_error;
