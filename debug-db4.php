<?php
require 'wp-load.php';

$engine = \Ska\Data\Core\Database_Engine::get_instance();
$table = 'ska_data_sys_tests';

// Create a dummy table first
$engine->create_table('sys_tests', array(
    array('name' => 'title', 'type' => 'text'),
    array('name' => 'content', 'type' => 'long_text')
));

$result = $engine->generate_portal_ui('wp_ska_data_sys_tests', array('add_gutenberg' => true));
if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message();
} else {
    echo "Success: "; print_r($result);
}
