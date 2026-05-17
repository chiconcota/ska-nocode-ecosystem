<?php
require 'wp-load.php';

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (strpos($errstr, 'strpos(): Passing null') !== false || strpos($errstr, 'str_replace(): Passing null') !== false) {
        echo "Found deprecated error!\n";
        echo $errstr . "\n";
        echo "Trace:\n";
        $e = new Exception();
        echo $e->getTraceAsString();
        echo "\n\n";
    }
    return false; // Let normal error handler run
}, E_DEPRECATED);

// Simulate the portal request
global $wp;
$wp->parse_request('khoa-hoc'); // Replace with what the URL was

wp();

