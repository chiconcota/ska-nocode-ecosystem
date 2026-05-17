<?php
require 'wp-load.php';
$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows('wp_ska_data_sys_theme_templates', [], 100);
foreach($rows as $r) {
    if (strpos($r['conditions'], 'khoa-hoc') !== false) {
        echo "ID: " . $r['id'] . "\n";
        echo "Content: \n" . $r['content'] . "\n\n";
    }
}
