<?php
require_once('wp-load.php');

$dict = get_option('ska_data_dictionary', []);
foreach ($dict as $table => $schema) {
    if (isset($schema['__table_info']['portal_settings'])) {
        echo "Table: $table\n";
        print_r($schema['__table_info']['portal_settings']);
        echo "------------------\n";
    }
}
