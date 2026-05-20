<?php
require_once 'wp-load.php';
$table_slug = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_lich_dat_phong';
$table_label = 'Test';
$res = \Ska\Data\Core\Database_Engine::generate_portal_ui( $table_slug, $table_label );
if (is_wp_error($res)) {
    echo "ERROR: " . $res->get_error_message();
} else {
    echo "SUCCESS\n";
    print_r($res);
}
