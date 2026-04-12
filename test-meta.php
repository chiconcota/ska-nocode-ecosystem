<?php
require 'wp-load.php';
$d = get_option('ska_data_dictionary');
echo '<pre>';
print_r($d);
echo '</pre>';
