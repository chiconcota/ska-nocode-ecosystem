<?php
require 'wp-load.php';
$dict = get_option('ska_data_dictionary', array());
echo "<pre>";
print_r(array_keys($dict));
echo "</pre>";
