<?php
require 'wp-load.php';
global $wpdb;

echo "--- ORGANISMS ---\n";
$orgs = $wpdb->get_results("SHOW TABLES LIKE '%ska_data_sys_organisms%'");
print_r($orgs);

echo "--- TEMPLATES ---\n";
$templates = $wpdb->get_results("SHOW TABLES LIKE '%ska_data_sys_theme_templates%'");
print_r($templates);

if (!empty($orgs)) {
    echo "\n--- DESCRIBE ORGANISMS ---\n";
    $desc = $wpdb->get_results("DESCRIBE " . $wpdb->prefix . "ska_data_sys_organisms");
    print_r($desc);
}

if (!empty($templates)) {
    echo "\n--- DESCRIBE TEMPLATES ---\n";
    $desc2 = $wpdb->get_results("DESCRIBE " . $wpdb->prefix . "ska_data_sys_theme_templates");
    print_r($desc2);
}

echo "--- LAST ERROR ---\n";
echo $wpdb->last_error;
