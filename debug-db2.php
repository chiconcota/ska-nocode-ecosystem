<?php
require 'wp-load.php';

$dict = get_option('ska_data_dictionary', array());
var_dump(isset($dict['ska_data_sys_organisms']));
var_dump(isset($dict['ska_data_sys_theme_templates']));
