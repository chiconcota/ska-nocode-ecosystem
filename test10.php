<?php
require 'wp-load.php';
$dict = get_option('ska_data_dictionary');
echo json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
