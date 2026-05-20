<?php
require 'wp-load.php';

try {
    $engine = \Ska\Data\Core\Database_Engine::get_instance();
    
    // Test the generator directly
    $result = $engine->generate_portal_ui('wp_ska_data_sys_organisms', array('add_gutenberg' => true));
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
