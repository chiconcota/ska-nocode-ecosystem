<?php
require 'wp-load.php';
// Create test table
$engine = \Ska\Data\Core\Database_Engine::get_instance();
$engine->create_table('ska_data_test_ajax', 'Test AJAX');

// Simulate WP_AJAX
$_POST['action'] = 'ska_data_generate_portal_ui';
$_POST['table'] = 'ska_data_test_ajax';
$_POST['slug'] = 'test-ajax';
$_POST['roles'] = 'public';
$_POST['view_mode'] = 'public';
$_POST['add_gutenberg'] = 'true';

// We need to bypass nonce for the test
remove_action('wp_ajax_ska_data_generate_portal_ui', array(\Ska\Data\Admin\Admin_Ajax::get_instance(), 'data_generate_portal_ui'));
// Let's just directly call the method but mock the verify_crud_request
$ajax = \Ska\Data\Admin\Admin_Ajax::get_instance();

// To avoid 'die' from wp_send_json_success, we capture output
ob_start();
try {
    $result = $engine->generate_portal_ui( $_POST['table'], array(
        'add_gutenberg' => true,
    ) );
    
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'message'           => 'Tự động tạo UI cho Portal thành công.',
            'portal_url'        => isset( $result['portal_url'] ) ? $result['portal_url'] : '',
            'theme_builder_url' => isset( $result['theme_builder_url'] ) ? $result['theme_builder_url'] : '',
            'data'              => $result 
        )
    ), JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
