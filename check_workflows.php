<?php
require_once 'wp-load.php';
$workflows = get_option('ska_logic_simple_workflows', []);
echo json_encode(array_keys($workflows));
if(isset($workflows['lay-api'])) {
    echo "\n\nlay-api nodes count: " . count($workflows['lay-api']['graph']['nodes'] ?? []);
}
