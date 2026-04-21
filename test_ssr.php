<?php
require_once dirname(__FILE__) . '/wp-load.php';

// Simulate how PHP parses an empty array from query string
$attributes = array(
    'organismId' => '4',
    'skaDynamicBinding' => array( 'script' => '' ),
    'htmlAttributes' => '', // Empty array becomes string in some URIs
    'logic' => '',          // Same here
    'isSkaForm' => 'false'
);

// Call it
$request = new WP_REST_Request( 'GET', '/wp/v2/block-renderer/ska-builder/organism-ref' );
$request->set_param( 'context', 'edit' );
$request->set_param( 'attributes', $attributes );

// Fake the logged in user as admin
wp_set_current_user( 1 ); 

$response = rest_do_request( $request );

echo "STATUS: " . $response->get_status() . "\n";
print_r($response->get_data());
