<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

$table = $wpdb->prefix . 'ska_data_sys_presets';
$results = $wpdb->get_results( "DESCRIBE {$table}" );
print_r($results);
