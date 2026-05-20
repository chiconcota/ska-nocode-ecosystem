<?php
require 'wp-load.php';
$dictionary = get_option('ska_data_dictionary', array());
$keys = array_keys($dictionary);
echo json_encode($keys, JSON_PRETTY_PRINT);
