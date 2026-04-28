<?php
require_once('wp-load.php');
global $wpdb;

$table_name = $wpdb->prefix . 'ska_data_sys_organisms';
echo "Checking table: $table_name\n";

$check_table = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
if (!$check_table) {
    echo "Table does not exist.\n";
    exit;
}

$check = $wpdb->get_var("SELECT id FROM $table_name WHERE name = 'org_welcome_email'");
if (!$check) {
    $wpdb->insert($table_name, [
        'name' => 'org_welcome_email',
        'html_content' => '<div class="p-4 bg-green-50 border border-green-200 rounded-md">
    <h3 class="text-green-800 font-bold text-lg">Chào mừng {{ user.full_name }}!</h3>
    <p class="text-green-700 mt-2">Email của bạn là: {{ user.email }}</p>
    <p class="text-green-600 text-sm mt-4">Thông điệp này được render từ Template Engine.</p>
</div>'
    ]);
    echo "Inserted org_welcome_email template!\n";
} else {
    echo "Template org_welcome_email already exists with id: $check\n";
}
