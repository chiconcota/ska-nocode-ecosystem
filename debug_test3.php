<?php
require_once 'wp-load.php';

$rows = [
    [
        'ID' => 1,
        'Name' => 'Doctor A',
        'Experience' => 3
    ],
    [
        'ID' => 2,
        'Name' => 'Doctor B',
        'Experience' => 5
    ]
];

foreach ($rows as $row) {
    $context = $row;
    $context['$item'] = $row;
    
    $condition = '$item.experience == 5';
    
    echo "Row: {$row['Name']} (Exp: {$row['Experience']})\n";
    echo "Evaluating: $condition\n";
    $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute($condition, $context);
    print_r($result);
    echo "--------------------------\n";
}
