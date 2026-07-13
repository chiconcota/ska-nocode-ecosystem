<?php
/**
 * Test Runner for Data Engine
 * 
 * Usage: Access ?skaaa_test_data=1
 */

if ( ! isset( $_GET['skaaa_test_data'] ) ) {
    return;
}

// Mock Context
$post_id = get_option( 'page_on_front' ); // Use front page ID
if ( ! $post_id ) {
    $posts = get_posts( ['numberposts' => 1] );
    $post_id = $posts ? $posts[0]->ID : 0;
}

$user = wp_get_current_user();
$user_id = $user->ID;

// Mock Terms (if any)
$terms = get_terms( ['taxonomy' => 'category', 'number' => 1, 'hide_empty' => false] );
$term_id = $terms ? $terms[0]->term_id : 0;

echo '<h2>Data Engine Test</h2>';

$engine = \Skaaa\Builder\Data\Core::instance();

// 1. Post Binding
echo '<h3>Post Binding (ID: ' . $post_id . ')</h3>';
// Set context manually for testing
$engine->context_manager->push_context($post_id, 'post');
$template = 'Title: {{post:title}} <br> ID: {{post:id}} <br> Permalink: {{post:permalink}}';
echo 'Template: ' . htmlspecialchars($template) . '<br>';
echo 'Result: ' . $engine->bind_data($template) . '<hr>';

// 2. User Binding
echo '<h3>User Binding (ID: ' . $user_id . ')</h3>';
$template = 'User: {{user:display_name}} <br> Email: {{user:email}}';
// We need to fetch directly to test provider, as bind_data defaults to current context
// But bind_data supports provider prefix {{user:key}} so it should work if we implemented logic in get_field_value correctly.
echo 'Template: ' . htmlspecialchars($template) . '<br>';
echo 'Result: ' . $engine->bind_data($template) . '<hr>';

// 3. Term Binding
echo '<h3>Term Binding (ID: ' . $term_id . ')</h3>';
// For term provider to work via {{term:name}}, we need to pass ID. 
// However, current Core::bind_data implementation uses current context ID.
// If I use {{term:name}}, it calls get_field_value(default, 'name', 'term').
// Inside get_field_value, it uses current context ID.
// So if current context is POST, it will try to find term with POST ID, which is wrong.
// LIMITATION FOUND: bind_data logic needs to handle provider-specific IDs or we must switch context.

// Test A: Switch Context
$engine->context_manager->push_context($term_id, 'term');
$template = 'Term Name: {{term:name}} <br> Term Slug: {{term:slug}}';
echo 'Template: ' . htmlspecialchars($template) . '<br>';
echo 'Result: ' . $engine->bind_data($template) . '<br>';
$engine->context_manager->pop_context();

// Test B: Cross-context access (e.g. User data while in Post context)
// Current implementation of `get_field_value` uses `$context['id']`.
// If I am in Post Context (ID 1), and call {{user:display_name}}, it calls User_Provider->get_field('display_name', 1).
// This means it tries to get User with ID 1.
// This is coincidentally correct if Post ID == User ID, but WRONG generally.
// FIX NEEDED: We need a way to resolve "Initial Data" or "Global Data" or "Parent Data".
// For now, let's just verify basic provider works when context is correct.

echo '<hr>';
echo 'Test Complete. Remove this file after use.';
exit;
