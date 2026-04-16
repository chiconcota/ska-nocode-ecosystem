<?php
// Tải môi trường WordPress
require_once 'wp-load.php';

global $wpdb;

echo "Bat dau qua trinh ket chuyen (Migration) cac cot cu sang JSON...\n";

// Lay Option Dictionary tu DB
$dictionary = get_option('ska_data_dictionary', array());

if (empty($dictionary)) {
    die("Khong tim thay Dictionary!\n");
}

$tables_altered = 0;
$cols_altered = 0;
$global_prefix = $wpdb->prefix . 'ska_data_';

foreach ($dictionary as $clean_table_name => $table_config) {
    $table_name = $global_prefix . $clean_table_name;

    // Check if table exists
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if ($exists !== $table_name) {
        continue;
    }

    $alter_queries = array();
    foreach ($table_config as $col_slug => $col_meta) {
        if ($col_slug === '__table_info') {
            continue;
        }

        $type = isset($col_meta['type']) ? $col_meta['type'] : '';

        // Neu field la cac loai mang array
        if (in_array($type, array('multi_select', 'relation', 'rollup'))) {
            $alter_queries[] = "MODIFY COLUMN `{$col_slug}` JSON DEFAULT NULL";
            $cols_altered++;
        }
    }

    if (!empty($alter_queries)) {
        $sql = "ALTER TABLE `{$table_name}` " . implode(', ', $alter_queries);
        $result = $wpdb->query($sql);
        if (false === $result) {
            echo "Loi alter table {$table_name}: " . $wpdb->last_error . "\n";
        } else {
            echo "Da alter thanh cong {$table_name} (" . count($alter_queries) . " cot)\n";
            $tables_altered++;
        }
    }
}

echo "\nHoan tat! Da thay doi {$tables_altered} bang, voi tong cong {$cols_altered} cot.";
