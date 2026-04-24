<?php
require_once 'wp-load.php';
$dict = get_option('ska_data_dictionary', array());
echo json_encode(array_keys($dict));
foreach($dict as $key => $table) {
    if(!isset($table['__table_info'])) {
        echo "\nTable $key is MISSING __table_info!";
    } else {
        echo "\nTable $key has __table_info: " . $table['__table_info']['name'];
    }
}
