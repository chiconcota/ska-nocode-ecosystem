<?php
require_once 'wp-load.php';
global $wpdb;
$dict = get_option('ska_data_dictionary');
print_r($dict[$wpdb->prefix . 'ska_data_sys_presets']);
