<?php
require_once 'wp-load.php';

$source_table = 'ska_data_app_test_loop_block_bang_bac_si';
echo "ROWS:\n";
$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $source_table, [], 10 );
var_dump($rows);

echo "\nBULK HTML:\n";
$html = \Ska\Design\Api\Organisms_API::get_bulk_html( [17] );
var_dump($html);

echo "\nRENDER BLOCK:\n";
$attributes = [
    'sourceTable' => 'ska_data_app_test_loop_block_bang_bac_si',
    'limit' => 10,
    'slots' => [
        [
            'organismId' => '17',
            'condition' => ''
        ]
    ]
];
$content = '';
$block = [ 'blockName' => 'ska-builder/loop', 'attrs' => $attributes, 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => [] ];

ob_start();
require WP_PLUGIN_DIR . '/ska-no-code-design/src/ska-loop/render.php';
$output = ob_get_clean();

echo "OUTPUT:\n";
var_dump($output);

