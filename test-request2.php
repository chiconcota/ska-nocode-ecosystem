<?php
require_once 'wp-load.php';

$request = new WP_REST_Request( 'POST', '/wp/v2/block-renderer/ska-builder/loop' );
$request->set_header( 'Content-Type', 'application/json' );

// This simulates what ServerSideRender sends using apiFetch POST
$body = json_encode( array(
    'context' => 'edit',
    'post_id' => 919,
    'attributes' => array(
        'sourceTable' => '',
        'limit' => 10,
        'slots' => array(
            array( 'organismId' => '15', 'condition' => '' )
        )
    )
) );
$request->set_body( $body );

$response = rest_do_request( $request );
if ( $response->is_error() ) {
    print_r( $response->get_error_message() );
    echo "\n";
    print_r( $response->get_error_data() );
} else {
    echo "SUCCESS\n";
    print_r( $response->get_data() );
}
