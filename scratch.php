<?php
require 'wp-load.php';
$dic = get_option('ska_data_dictionary', array());
foreach ($dic as $t => $s) {
    if (!empty($s['__table_info']['portal_settings'])) {
        echo "Table: $t\n";
        print_r($s['__table_info']['portal_settings']);
    }
}
