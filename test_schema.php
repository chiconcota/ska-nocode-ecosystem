<?php
require 'wp-load.php';
global $wpdb;
print_r($wpdb->get_results("DESCRIBE {$wpdb->prefix}ska_data_sys_organisms"));
print_r($wpdb->get_results("DESCRIBE {$wpdb->prefix}ska_data_sys_theme_templates"));
