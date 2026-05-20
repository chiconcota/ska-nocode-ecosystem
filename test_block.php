<?php
require 'wp-load.php';

$registry = WP_Block_Type_Registry::get_instance();
$is_registered = $registry->is_registered('ska-builder/form-rich-text');

var_dump($is_registered);
