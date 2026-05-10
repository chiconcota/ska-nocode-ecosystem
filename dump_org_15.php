<?php
require_once 'wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
$html = $wpdb->get_var("SELECT html_content FROM {$table} WHERE id = 15");
echo "HTML FOR ORG 15:\n" . substr($html, 0, 1000) . "...\n";
