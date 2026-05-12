<?php
require_once 'wp-load.php';

global $wpdb;
$table_name = $wpdb->prefix . 'ska_data_sys_presets';

// Delete all duplicate keys from DB
$wpdb->query( "DELETE FROM {$table_name} WHERE name = 'Blockgap' OR name = 'Contentpadding' OR name = 'Borderradius' OR name = 'Boxshadow' OR name = 'Containerwidth' OR name = 'Transitionduration'" );

// Compile tokens
if ( class_exists( '\Ska\Design\Api\Design_Tokens_Compiler' ) ) {
    \Ska\Design\Api\Design_Tokens_Compiler::get_instance()->compile_tokens_to_json();
    echo "Tokens re-compiled successfully.\n";
} else {
    echo "Compiler class not found.\n";
}
