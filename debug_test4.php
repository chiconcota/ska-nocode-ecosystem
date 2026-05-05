<?php
require_once 'wp-load.php';

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows('ska_data_bac_si', [], 5);
if (empty($rows)) {
    $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows('ska_data_doctors', [], 5);
}

foreach ($rows as $row) {
    $context = $row;
    $context['$item'] = $row;
    
    $condition = '$item.experience == 5';
    
    echo "Evaluating: $condition on Row ID: " . ($row['id'] ?? 'unknown') . "\n";
    print_r($row);
    $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute($condition, $context);
    echo "SkaFX Result:\n";
    print_r($result);
    echo "--------------------------\n";
}
