<?php
require_once 'wp-load.php';

// If Virtual App Wrapper exists, get the current template
if ( class_exists( '\Ska\Builder\Design\Virtual_App_Wrapper' ) ) {
    $wrapper = \Ska\Builder\Design\Virtual_App_Wrapper::instance();
    $templates = get_option( 'ska_theme_templates', [] );
    echo "Templates: " . json_encode($templates) . "\n";
    
    // Simulate what the wrapper does for front-page
    $template = $templates['front-page'] ?? null;
    echo "Front-page Template Organism ID: " . $template . "\n";
    
    if ( $template ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ska_data_sys_organisms';
        $html = $wpdb->get_var( $wpdb->prepare( "SELECT html_content FROM {$table} WHERE id = %d", $template ) );
        if ($html) {
            echo "HTML LENGTH: " . strlen($html) . "\n";
            echo "HTML: " . substr($html, 0, 500) . "...\n";
            // parse blocks
            $blocks = parse_blocks($html);
            echo "BLOCKS:\n";
            foreach($blocks as $b) {
                echo " - " . $b['blockName'] . "\n";
            }
        }
    }
} else {
    echo "Virtual App Wrapper not found.\n";
}
