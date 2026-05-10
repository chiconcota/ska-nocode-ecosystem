<?php
require_once 'wp-load.php';

// Check which organisms are parsed by Tailwind Compiler for home page!
$post_id = get_option( 'page_on_front' );
if ( ! $post_id ) {
    $post_id = get_option( 'page_for_posts' );
}

echo "Front Page Post ID: " . $post_id . "\n";

global $ska_active_theme_organisms;
echo "Active Theme Organisms Array: " . json_encode($ska_active_theme_organisms) . "\n";

// Let's manually trigger the scan and dump what we get
$core = \Ska\Builder\Design\Core::instance();
$classes = $core->style_manager->scan_post_classes($post_id);

echo "Scanned Post Classes: " . $classes . "\n";

// Also fetch all organisms in DB to see what exists
global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
    $rows = $wpdb->get_results("SELECT id, name, html_content FROM {$table}");
    foreach($rows as $row) {
        echo "Org {$row->id} ({$row->name}):\n";
        // Check if there is a loop block
        if (strpos($row->html_content, 'ska-builder/loop') !== false) {
            echo "  --> HAS LOOP BLOCK!\n";
            $blocks = parse_blocks($row->html_content);
            foreach ($blocks as $block) {
                if ($block['blockName'] === 'ska-builder/loop') {
                    echo "  --> LOOP SLOTS: " . json_encode($block['attrs']['slots'] ?? []) . "\n";
                } else if (!empty($block['innerBlocks'])) {
                    foreach ($block['innerBlocks'] as $inner) {
                        if ($inner['blockName'] === 'ska-builder/loop') {
                            echo "  --> INNER LOOP SLOTS: " . json_encode($inner['attrs']['slots'] ?? []) . "\n";
                        }
                    }
                }
            }
        }
    }
}
