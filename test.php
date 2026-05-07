<?php
require 'wp-load.php';
global $wpdb;

function convert_client_block_to_server_block($client_block) {
    if (!is_array($client_block)) return null;
    
    $server_block = [
        'blockName'    => $client_block['name'] ?? null,
        'attrs'        => $client_block['attributes'] ?? [],
        'innerBlocks'  => [],
        'innerHTML'    => '',
        'innerContent' => []
    ];
    
    if (isset($client_block['innerBlocks']) && is_array($client_block['innerBlocks'])) {
        foreach ($client_block['innerBlocks'] as $child) {
            $converted = convert_client_block_to_server_block($child);
            if ($converted) {
                $server_block['innerBlocks'][] = $converted;
                $server_block['innerContent'][] = null; // null represents an inner block placeholder
            }
        }
    }
    
    return $server_block;
}

$organism = $wpdb->get_row('SELECT * FROM wp_ska_data_sys_organisms WHERE id = 2', ARRAY_A);
if ($organism) {
    $json = $organism['json_content'];
    $blocks = json_decode($json, true);
    
    // Sometimes it's a single object, sometimes an array of objects
    $client_blocks = isset($blocks['clientId']) ? [$blocks] : $blocks;
    
    $server_blocks = [];
    foreach ($client_blocks as $cb) {
        $converted = convert_client_block_to_server_block($cb);
        if ($converted) {
            $server_blocks[] = $converted;
        }
    }
    
    $html = serialize_blocks($server_blocks);
    echo "HTML generated:\n";
    echo $html;
}
