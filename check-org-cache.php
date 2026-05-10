<?php 
$cache_file = __DIR__ . '/wp-content/uploads/ska-data/organisms.json';
$organisms = json_decode(file_get_contents($cache_file), true);
if (is_array($organisms)) {
    foreach ($organisms as $org) {
        echo "ID: " . $org['id'] . ", Name: " . $org['name'] . "\n";
    }
}
