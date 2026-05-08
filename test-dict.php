<?php require 'wp-load.php'; $dict = get_option('ska_data_dictionary', []); echo wp_json_encode($dict['wp_ska_data_sys_organisms']['__table_info'] ?? []);
