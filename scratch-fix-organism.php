<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

global $wpdb;
$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';

echo "=== SỬA LỖI HTML CONTENT CỦA ROW TEMPLATES ===\n";

$rows = $wpdb->get_results("SELECT id, name, json_content, html_content FROM $table_organisms WHERE name LIKE 'Row: %'");

if (empty($rows)) {
    echo "Không tìm thấy Row Template nào.\n";
} else {
    $updated = 0;
    foreach ($rows as $row) {
        $html = $row->html_content;
        
        // Xóa sạch tất cả các thẻ div và thẻ p
        $html = preg_replace('/<div[^>]*>/', '', $html);
        $html = str_replace('</div>', '', $html);
        $html = preg_replace('/<p[^>]*>/', '', $html);
        $html = str_replace('</p>', '', $html);
        
        // Đảm bảo loại bỏ các khoảng trắng thừa
        $html = preg_replace('/^\s*[\r\n]/m', '', $html);

        if ($html !== $row->html_content) {
            $wpdb->update(
                $table_organisms,
                array('html_content' => trim($html)),
                array('id' => $row->id),
                array('%s'),
                array('%d')
            );
            $updated++;
            echo "Đã sửa HTML cho Organism ID {$row->id} ({$row->name}).\n";
        }
    }
    
    echo "Hoàn thành! Đã sửa $updated Row Templates.\n";
    
    // Flush cache
    if (class_exists('\Ska\Design\Api\Organisms_API')) {
        \Ska\Design\Api\Organisms_API::get_instance()->export_physical_cache();
        echo "Đã làm mới JSON Cache.\n";
    }
}
