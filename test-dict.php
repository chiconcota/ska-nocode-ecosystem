<?php
require_once 'wp-load.php';
global $wpdb;
$res = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ska_data_sys_organisms WHERE id = 30");
print_r($res);
