<?php
require_once('wp-load.php');
header('Content-Type: application/json');
echo json_encode(get_option('ska_data_dictionary', array()));
