<?php
require 'wp-load.php';

try {
    $engine = \Ska\Data\Core\Database_Engine::get_instance();
    $table = 'sys_tests';

    // Tạo bảng
    $engine->create_table($table, array(
        array('name' => 'title', 'type' => 'text'),
        array('name' => 'content', 'type' => 'long_text')
    ));

    $result = $engine->generate_portal_ui('wp_ska_data_sys_tests', array('add_gutenberg' => true));
    if (is_wp_error($result)) {
        echo "Error: " . $result->get_error_message();
    } else {
        echo "Success: "; print_r($result);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
}
