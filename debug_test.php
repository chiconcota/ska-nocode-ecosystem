<?php
require_once 'wp-load.php';

$tables = \Ska\Data\Core\Schema_Manager::get_all_schemas();
print_r($tables);

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( 'ska_data_bang_bac_si', [], 10 );
if(empty($rows)) {
    // maybe trying another table name
    $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( 'ska_data_bac_si', [], 10 );
}
print_r($rows);
