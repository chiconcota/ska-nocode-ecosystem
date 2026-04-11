<?php
require_once('wp-load.php');
$table_name = 'wp_ska_data_app_do5p1uxz_test_sync';
$args = [
    'filter_field' => 'id',
    'filter_val'   => 1,
    'filter_op'    => 'eq'
];
$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, $args, 1 );

print_r($rows);

// Let's also check Evaluator!
$evaluator = new \Ska\Logic\SkaFX\SkaFX_Evaluator( [ 'GLOBAL_ID' => 1 ] );
$script = "[app_do5p1uxz.test_sync.kieu_text]";

$result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $script, [ 'GLOBAL_ID' => 1 ] );
echo "\n--- Engine Result ---\n";
print_r($result);
