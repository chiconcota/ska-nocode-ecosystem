<?php
require_once 'wp-load.php';

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows('ska_data_bac_si', [], 5);
if (empty($rows)) {
    $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows('ska_data_doctors', [], 5);
}
if (empty($rows)) {
    echo "NO ROWS FOUND IN ANY TABLE\n";
    $tables = \Ska\Data\Core\Schema_Manager::get_all_schemas();
    print_r(array_keys($tables));
    exit;
}

print_r($rows[0]);

$context = $rows[0];
$context['$item'] = $rows[0];

$condition = '$item.experience > 4'; // test condition

echo "\nEvaluating condition: $condition\n";
$result = \Ska\Logic\SkaFX\SkaFX_Engine::execute($condition, $context);
print_r($result);
