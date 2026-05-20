<?php
require 'wp-load.php';
$engine = \Ska\Data\Core\Database_Engine::get_instance();
$result = apply_filters('ska_design_generate_portal_assets', false, 'ska_data_hotels', array());
echo json_encode($result, JSON_PRETTY_PRINT);
