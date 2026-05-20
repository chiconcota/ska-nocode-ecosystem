<?php
require 'wp-load.php';
global $wpdb;
$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ska_data_sys_organisms ORDER BY id DESC LIMIT 5", ARRAY_A);
foreach($res as $r) {
    echo "ID: {$r['id']} - Name: {$r['name']}\nHTML: \n" . substr($r['html_content'], 0, 500) . "\n\n";
}
