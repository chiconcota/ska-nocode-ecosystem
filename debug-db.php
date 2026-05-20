<?php
require 'wp-load.php';
global $wpdb;

$cols2 = $wpdb->get_results("DESCRIBE {$wpdb->prefix}ska_data_sys_theme_templates");
echo "<pre>"; print_r($cols2); echo "</pre>";

$cols1 = $wpdb->get_results("DESCRIBE {$wpdb->prefix}ska_data_sys_organisms");
echo "<pre>"; print_r($cols1); echo "</pre>";
