<?php
require_once 'wp-load.php';

$registry = WP_Block_Type_Registry::get_instance();
$is_registered = $registry->is_registered('ska-builder/form-rich-text');
echo "Is ska-builder/form-rich-text registered? " . ($is_registered ? "Yes" : "No") . "\n";
