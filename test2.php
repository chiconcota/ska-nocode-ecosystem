<?php
require 'wp-load.php';

$result = apply_filters('ska_design_generate_portal_assets', false, 'wp_ska_data_app_quan_ly_dat_phong_khach_san_phong_khach_san', array(
    'slug' => 'testslug',
    'roles' => 'public',
    'view_mode' => 'public'
));

echo json_encode($result, JSON_PRETTY_PRINT);
