<?php
/**
 * CLI Test Runner
 *
 * Usage: php cli-test-runner.php
 */

// Define ABSPATH to avoid exit calls in files we might include directly, 
// though we usually include via WP loader.
define( 'WP_USE_THEMES', false );

// Load WordPress
require_once __DIR__ . '/../../../../../wp-load.php';

// Check if plugin class is loaded.
if ( ! class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
    echo "Error: Plugin classes not loaded. Is the plugin active?\n";
    exit(1);
}

// Start Test
echo "\n========================================\n";
echo "       DATA ENGINE CLI TEST             \n";
echo "========================================\n";

$engine = \Ska\Builder\Data\Core::instance();

// 1. Post Binding
$posts = get_posts( ['numberposts' => 1] );
if ( empty( $posts ) ) {
    echo "No posts found. Creating a dummy post for testing.\n";
    $post_id = wp_insert_post( [
        'post_title' => 'Test Post',
        'post_content' => 'Test Content',
        'post_status' => 'publish',
    ] );
} else {
    $post_id = $posts[0]->ID;
}

echo "[TEST] Post Binding (ID: $post_id)\n";
$engine->context_manager->push_context( $post_id, 'post' );
$template = "Title: {{post:title}} | ID: {{post:id}}";
$result = $engine->bind_data( $template );
echo "Result: $result\n";
if ( strpos( $result, 'Title:' ) !== false && strpos( $result, (string)$post_id ) !== false ) {
    echo "[PASS] Post Binding\n";
} else {
    echo "[FAIL] Post Binding\n";
}
$engine->context_manager->pop_context();
echo "----------------------------------------\n";

// 2. User Binding
$users = get_users( ['number' => 1] );
$user_id = $users ? $users[0]->ID : 0;
echo "[TEST] User Binding (ID: $user_id)\n";
$template = "User: {{user:display_name}} | Email: {{user:email}}";
// Note: We are in Global context (or empty context) now.
// bind_data uses current context. If we want to use User provider, we generally expect to be in User context OR use specific logic.
// However, our Provider logic for 'user' usually requires a user ID. 
// If we just say {{user:email}}, the provider receives current context ID.
// If current context is global (Post 0 or Post X), User Provider will look for User with that ID.
// This is flakely unless we are in User context.

$engine->context_manager->push_context( $user_id, 'user' );
$result = $engine->bind_data( $template );
echo "Result: $result\n";
$engine->context_manager->pop_context();
echo "----------------------------------------\n";

// 3. Register Verification
$registry = $engine->registry;
$providers = $registry->get_all();
echo "[TEST] Registry Check\n";
$expected = ['post', 'scf', 'term', 'user'];
foreach ($expected as $slug) {
    if ( isset( $providers[$slug] ) ) {
        echo "[PASS] Provider '$slug' registered.\n";
    } else {
        echo "[FAIL] Provider '$slug' NOT registered.\n";
    }
}

echo "========================================\n";
echo "       TEST COMPLETE                    \n";
echo "========================================\n";
