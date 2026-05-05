<?php
require_once __DIR__ . '/wp-load.php';

$request = new WP_REST_Request( 'POST', '/wp/v2/block-renderer/ska-builder/ska-loop' );
$request->set_param( 'context', 'edit' );
$request->set_param( 'attributes', [
    'sourceTable' => 'ska_data_app_test_loop_block_bang_bac_si',
    'limit' => 10,
    'slots' => [
        [
            'organismId' => '16',
            'condition' => ''
        ]
    ]
] );

$response = rest_do_request( $request );

echo "<h2>REST API Response:</h2>";
echo "<pre>";
print_r( $response->get_status() );
echo "\n";
print_r( $response->get_data() );
echo "</pre>";
