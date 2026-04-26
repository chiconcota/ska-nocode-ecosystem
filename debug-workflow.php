<?php
require_once 'wp-load.php';
$w = get_option('ska_logic_simple_workflows', []);
if (isset($w['simplenodetest']['graph']['edges'])) {
    echo json_encode($w['simplenodetest']['graph']['edges'], JSON_PRETTY_PRINT);
} else {
    echo "No edges";
}
