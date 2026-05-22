<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

global $wpdb;
$table = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_phong_khach_san';
$row = $wpdb->get_row("SELECT * FROM `$table` WHERE id = 1", ARRAY_A);

header('Content-Type: text/plain; charset=UTF-8');
if ($row) {
    echo "Row ID 1:\n";
    print_r($row);
    
    $val = $row['phong_dat'];
    echo "\nRaw phong_dat: " . var_export($val, true) . "\n";
    echo "strlen: " . strlen($val) . "\n";
    
    $stripslashes_val = stripslashes($val);
    echo "stripslashes: " . var_export($stripslashes_val, true) . "\n";
    
    $val_clean = trim($stripslashes_val);
    echo "starts_with_bracket_brace: " . (str_starts_with($val_clean, '[{') ? 'yes' : 'no') . "\n";
    
    $decoded = json_decode($val_clean, true);
    echo "json_decode: " . var_export($decoded, true) . "\n";
    echo "json_last_error: " . json_last_error() . " (" . json_last_error_msg() . ")\n";
} else {
    echo "No row found with ID 1 in table $table\n";
}
