<?php
require 'wp-load.php';
$dict = get_option('ska_data_dictionary', []);
file_put_contents('dict_dump.txt', print_r($dict, true));
echo "Dumped.";
