<?php
require __DIR__ . '/wp-load.php';

$workflows = get_option('ska_logic_simple_workflows', []);
$g = $workflows['loop-node']['graph'] ?? [];

echo "=== NODES ===" . PHP_EOL;
foreach (($g['nodes'] ?? []) as $n) {
    echo $n['id'] 
        . ' | type=' . ($n['type'] ?? '?') 
        . ' | parentId=' . ($n['parentId'] ?? 'null') 
        . ' | parentNode=' . ($n['parentNode'] ?? 'null') 
        . ' | class=' . ($n['class'] ?? 'null')
        . PHP_EOL;
}

echo PHP_EOL . "=== EDGES ===" . PHP_EOL;
foreach (($g['edges'] ?? []) as $e) {
    echo ($e['source'] ?? '?') . ' -> ' . ($e['target'] ?? '?') . PHP_EOL;
}
