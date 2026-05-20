<?php
require 'wp-load.php';

$dictionary = get_option('ska_data_dictionary', array());
$clean_table = 'app_quan_ly_dat_phong_khach_san_phong_khach_san';

if (isset($dictionary[$clean_table])) {
    foreach ($dictionary[$clean_table] as $col_slug => $col_data) {
        if ($col_slug !== '__table_info') {
            echo "Col: $col_slug - Type: " . (isset($col_data['type']) ? $col_data['type'] : 'N/A') . "\n";
        }
    }
}
