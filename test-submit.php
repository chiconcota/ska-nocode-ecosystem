<?php
$payload = [
    'ska_form_id' => 'simplenodetest',
    'data' => [
        'ten' => 'Tester Final',
        'nam_sinh' => 2000
    ]
];

$ch = curl_init('http://ska-core-builder.local/wp-json/ska-logic/v1/submit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

echo "Response: " . $response . "\n";
