<?php
$html = file_get_contents('http://ska-core-builder.local/test-content-padding/');
if (preg_match('/html\.dark body\.ska-builder \.dark\\\\:bg-primary.*?}/s', $html, $matches)) {
    echo $matches[0];
} else {
    echo "NOT FOUND";
}
