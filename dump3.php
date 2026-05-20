<?php
require 'wp-load.php';
global $wpdb;
$engine = \Ska\Data\Core\Database_Engine::get_instance();
$res = $engine->generate_portal_ui($wpdb->prefix . 'ska_data_hotels', array('add_gutenberg' => true));

echo "<pre>";
print_r($res);
echo "</pre>";
