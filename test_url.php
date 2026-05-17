<?php
require 'wp-load.php';
$url = '/dang-nhap-app/';
$esc = esc_url_raw($url);
echo "Raw: " . $url . "\n";
echo "Escaped: " . $esc . "\n";
$with_query = add_query_arg('redirect_to', urlencode('http://test.com/'), $esc);
echo "With query: " . $with_query . "\n";
