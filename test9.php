<?php
$html = file_get_contents('http://ska-core-builder.local/doctor-form/');
file_put_contents(__DIR__ . '/test9.html', $html);
echo "Done";
