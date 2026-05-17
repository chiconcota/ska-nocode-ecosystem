<?php
$html = file_get_contents('http://ska-core-builder.local/test-content-padding/');
if(preg_match_all('/\.dark\\\\:bg-slate-900/', $html, $m)) echo 'YES'; else echo 'NO';
