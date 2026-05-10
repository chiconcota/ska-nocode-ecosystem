<?php
require_once 'wp-load.php';

$core = \Ska\Builder\Design\Core::instance();

global $wpdb;
$table = $wpdb->prefix . 'ska_data_sys_organisms';
$rows = $wpdb->get_results("SELECT id, name, html_content FROM {$table}");
foreach($rows as $row) {
    if (strpos($row->html_content, 'ska-builder/loop') !== false) {
        echo "\n====================\n";
        echo "Org {$row->id} ({$row->name}) HAS LOOP BLOCK!\n";
        
        $blocks = parse_blocks($row->html_content);
        $classes = [];
        
        // Let's manually run the exact logic
        foreach ($blocks as $block) {
            if (isset($block['blockName']) && $block['blockName'] === 'ska-builder/loop') {
                echo "Loop block found at root!\n";
                if (!empty($block['attrs']['slots'])) {
                    echo "Slots: " . json_encode($block['attrs']['slots']) . "\n";
                    foreach($block['attrs']['slots'] as $slot) {
                        if (!empty($slot['organismId'])) {
                            echo "Slot Organism ID: " . $slot['organismId'] . "\n";
                            $org_classes = $core->style_manager->scan_organism_classes($slot['organismId']);
                            echo "Scanned classes for slot: " . $org_classes . "\n";
                        }
                    }
                }
            }
            if (!empty($block['innerBlocks'])) {
                foreach ($block['innerBlocks'] as $inner) {
                    if (isset($inner['blockName']) && $inner['blockName'] === 'ska-builder/loop') {
                        echo "Loop block found in innerBlocks!\n";
                        if (!empty($inner['attrs']['slots'])) {
                            echo "Slots: " . json_encode($inner['attrs']['slots']) . "\n";
                            foreach($inner['attrs']['slots'] as $slot) {
                                if (!empty($slot['organismId'])) {
                                    echo "Slot Organism ID: " . $slot['organismId'] . "\n";
                                    // Make sure it scans!
                                    // reset the scanning array so it can rescan
                                    $ref = new ReflectionProperty($core->style_manager, 'scanning_organisms');
                                    $ref->setAccessible(true);
                                    $ref->setValue($core->style_manager, []);
                                    
                                    $org_classes = $core->style_manager->scan_organism_classes($slot['organismId']);
                                    echo "Scanned classes for slot: " . substr($org_classes, 0, 100) . "...\n";
                                }
                            }
                        } else {
                            echo "No slots found in loop attrs!\n";
                        }
                    }
                }
            }
        }
        
        // Reset scanning organisms for the big test
        $ref = new ReflectionProperty($core->style_manager, 'scanning_organisms');
        $ref->setAccessible(true);
        $ref->setValue($core->style_manager, []);
        
        $all_classes = $core->style_manager->scan_organism_classes($row->id);
        echo "ALL EXTRACTED CLASSES for Org {$row->id}: " . substr($all_classes, 0, 200) . "...\n";
    }
}
