<?php
require 'wp-load.php';

$all_workflows = get_option('ska_logic_simple_workflows', []);
$workflow = $all_workflows['add_products'] ?? null;
if (!$workflow) {
    file_put_contents('test_logic.json', "Workflow 'add_products' not found.\n");
    exit;
}
file_put_contents('test_logic.json', json_encode($workflow, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "DONE";
