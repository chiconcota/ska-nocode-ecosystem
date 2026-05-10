<?php
require_once 'wp-load.php';

global $wpdb;
$rows = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_content LIKE '%ska-builder/loop%' AND post_status = 'publish'");
echo "Found loop in posts:\n";
foreach($rows as $row) {
    echo "Post {$row->ID}: {$row->post_title}\n";
    $post = get_post($row->ID);
    $blocks = parse_blocks($post->post_content);
    foreach($blocks as $block) {
        if (!empty($block['blockName']) && $block['blockName'] === 'ska-builder/loop') {
            echo "  --> Loop slots in root: " . json_encode($block['attrs']['slots'] ?? []) . "\n";
        }
        if (!empty($block['innerBlocks'])) {
            foreach($block['innerBlocks'] as $inner) {
                if (!empty($inner['blockName']) && $inner['blockName'] === 'ska-builder/loop') {
                    echo "  --> Loop slots in innerBlocks: " . json_encode($inner['attrs']['slots'] ?? []) . "\n";
                }
            }
        }
    }
}
