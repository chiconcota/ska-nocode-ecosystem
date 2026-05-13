<?php
require_once 'wp-load.php';
$dict = get_option('ska_data_dictionary', array());
echo json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
