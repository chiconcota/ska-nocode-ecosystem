<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

if ( ! class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
    echo 'Ska Data Pro not active.';
    exit;
}

$actual_table_name = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_phong_khach_san';
$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $actual_table_name, [], 10 );

header('Content-Type: text/plain; charset=UTF-8');
echo "Rows returned by Data_Fetcher::get_table_rows:\n";
print_r($rows);

foreach ($rows as $row) {
    if (isset($row['phong_dat'])) {
        $val = $row['phong_dat'];
        echo "\nField: phong_dat\n";
        echo "Type: " . gettype($val) . "\n";
        echo "Raw Value: " . var_export($val, true) . "\n";
        
        $val_clean = trim( stripslashes( $val ) );
        echo "Cleaned Value: " . var_export($val_clean, true) . "\n";
        echo "Starts with '[{': " . (str_starts_with($val_clean, '[{') ? 'yes' : 'no') . "\n";
        
        $decoded = json_decode($val_clean, true);
        echo "json_decode: " . var_export($decoded, true) . "\n";
        echo "json_error: " . json_last_error_msg() . "\n";
    }
}
