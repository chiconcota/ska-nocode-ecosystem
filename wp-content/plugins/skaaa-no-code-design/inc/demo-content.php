<?php
/**
 * Demo Content Generator
 *
 * Creates a "Skaaa Logic Demo" page with blocks to test the Logic Engine.
 * Run once on admin_init.
 *
 * @package Skaaa_Builder_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function() {
    // Only run if user is admin
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Check if demo page exists
    $page_slug = 'skaaa-logic-demo';
    $existing = get_page_by_path( $page_slug );

    if ( $existing ) {
        // Optional: Update content? For now, just skip if exists.
        return;
    }

    // Prepare Mock Data for Logic Testing
    // We will use the Page itself as the context.
    
    // Block 1: Simple Data Binding
    $block1 = '<!-- wp:skaaaaa-builder/text {"content":"<h3>Test 1: Simple Binding</h3><p>Title: {{post:title}} (ID: {{post:id}})</p>","tagName":"div"} /-->';

    // Block 2: IF Condition (True)
    // We will add meta 'skaaa_feature_enabled' = '1' to this page.
    $block2 = '<!-- wp:skaaaaa-builder/text {"content":"<h3>Test 2: IF Condition (True)</h3>{{#if post:skaaa_feature_enabled}}<p style=\u0022color:green\u0022>PASS: Feature is enabled</p>{{/if}}","tagName":"div"} /-->';

    // Block 3: IF Condition (False)
    $block3 = '<!-- wp:skaaaaa-builder/text {"content":"<h3>Test 3: IF Condition (False)</h3>{{#if post:skaaa_feature_disabled}}<p style=\u0022color:red\u0022>FAIL: Should not see this</p>{{/if}}<p>If you see nothing red above, PASS.</p>","tagName":"div"} /-->';

    // Block 4: FOREACH Loop (User List)
    // We need an array. We can't easily fake array meta in basic WP without serializing, 
    // but Provider logic for 'foreach' supports specific providers.
    // Let's rely on 'user' provider returning current user?
    // Or better, let's add specific meta that returns an array of IDs.
    // We need to ensure `get_field_value` handles array meta correctly.
    // WP `get_post_meta` with single=true returns array if serialized. Simple arrays (multiple rows) need single=false.
    // Our provider `WP_Post_Provider` uses `get_post_meta(..., true)`. 
    // So we should save an array as a single serialized value.

    $block4 = '<!-- wp:skaaaaa-builder/text {"content":"<h3>Test 4: FOREACH Loop</h3><ul>{{#foreach item in post:skaaa_related_items}}<li>Item ID: {{post:id}} (Type: {{post:post_type}})</li>{{/foreach}}</ul>","tagName":"div"} /-->';

    $content = $block1 . $block2 . $block3 . $block4;

    // Create Page
    $page_id = wp_insert_post( array(
        'post_title'   => 'Skaaa Logic Demo',
        'post_name'    => $page_slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ) );

    if ( $page_id && ! is_wp_error( $page_id ) ) {
        // Set Meta for Tests
        update_post_meta( $page_id, 'skaaa_feature_enabled', '1' );
        
        // Create dummy related posts
        $p1 = wp_insert_post( ['post_title' => 'Related A', 'post_status' => 'publish'] );
        $p2 = wp_insert_post( ['post_title' => 'Related B', 'post_status' => 'publish'] );
        
        update_post_meta( $page_id, 'skaaa_related_items', array( $p1, $p2 ) ); // Save as array (serialized)

        // Add notice
        add_action( 'admin_notices', function() use ( $page_id ) {
            $link = get_permalink( $page_id );
            echo '<div class="notice notice-success is-dismissible"><p>Skaaa Logic Demo Page created! <a href="' . esc_url( $link ) . '" target="_blank">View Demo</a></p></div>';
        });
    }
});
