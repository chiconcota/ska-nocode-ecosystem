<?php
require_once 'wp-load.php';

// Mock payload
$payload = [
    'trigger' => [
        'name' => 'khach hang a',
        'email' => 'test@gmail.com',
        'total' => 500
    ]
];

// Mock DAG Graph
$graph = [
    'nodes' => [
        [
            'id' => 'node_trigger',
            'type' => 'TriggerNode',
            'class' => 'Ska_Logic_Trigger_Node',
            'config' => [
                'triggerType' => 'webhook'
            ]
        ],
        [
            'id' => 'node_set_data',
            'type' => 'SetDataNode',
            'class' => 'Ska_Logic_Set_Data',
            'config' => [
                'assignments' => [
                    ['key' => 'status', 'value' => 'vip'],
                    ['key' => 'formatted_name', 'value' => '{{ strtoupper(trigger.name) }}'],
                    ['key' => 'discount', 'value' => '{{ trigger.total * 0.1 }}']
                ]
            ]
        ]
    ],
    'edges' => [
        [
            'id' => 'edge_1',
            'source' => 'node_trigger',
            'target' => 'node_set_data',
            'sourceHandle' => 'main'
        ]
    ]
];

// Run Pipeline
echo "==== TRƯỚC KHI CHẠY ====\n";
print_r($payload);

echo "\n==== ĐANG CHẠY PIPELINE ====\n";
$final_payload = Ska_Workflow_Runner::execute($payload, $graph);

echo "\n==== SAU KHI CHẠY ====\n";
print_r($final_payload);
