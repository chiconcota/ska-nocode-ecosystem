<?php
namespace Ska_System_Framework;

defined('ABSPATH') || exit;

/**
 * Class System_Cache
 * Quản lý Chiến lược Tối ưu Bộ nhớ (Caching Strategy) cho các bảng Hệ thống (sys_*).
 * 
 * - Lớp 1: RAM (WP Object Cache / Transients).
 * - Lớp 2: Physical JSON Fallback (Dành cho Server không hỗ trợ Object Cache, hoặc dùng làm Single Source of Truth cho JIT).
 */
class System_Cache
{
    private static $instance = null;

    /**
     * @return System_Cache
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Lắng nghe các thay đổi CRUD trên DataGrid từ Ska Data Pro
        add_action('ska_data_row_created', array($this, 'handle_data_mutation'), 10, 2);
        add_action('ska_data_cell_updated', array($this, 'handle_data_mutation'), 10, 4);
        add_action('ska_data_row_deleted', array($this, 'handle_data_mutation'), 10, 2);
    }

    /**
     * Khi có thay đổi dữ liệu bảng, chúng ta sẽ xóa cache hiện tại và ghi đè file vật lý.
     * 
     * @param string $table_name Tên bảng đã có prefix (vd: wp_ska_data_sys_presets)
     */
    public function handle_data_mutation($table_name, $row_id, $column_name = '', $value = '')
    {
        global $wpdb;
        $sys_prefix = $wpdb->prefix . 'ska_data_sys_';
        
        // Chỉ quan tâm đến các thay đổi thuôc về Ska System
        if (strpos($table_name, $sys_prefix) !== 0) {
            return;
        }

        // Tách lấy Table Slug thật 
        $slug = str_replace($sys_prefix, '', $table_name);

        // Clear RAM Cache (Object Cache / Transient)
        $cache_key = 'ska_sys_cache_' . $slug;
        delete_transient($cache_key);

        // Async build lại File JSON Fallback (Physical disk) tránh làm nghẽn TTFB của User khi Save Form
        // Do PHP chạy đồng bộ nên mình build ngay luôn vì bảng này cũng không quá chục ngàn dòng.
        $this->build_physical_json_fallback($table_name, $slug);
    }

    /**
     * Kết xuất (Export) dữ liệu ra File .json
     */
    private function build_physical_json_fallback($table_name, $slug)
    {
        global $wpdb;

        // Kéo toàn bộ cấu trúc row
        $rows = $wpdb->get_results("SELECT * FROM `{$table_name}` ORDER BY id ASC", ARRAY_A);
        if (!$rows) {
            $rows = array();
        }

        // Thư mục lưu trữ: wp-content/uploads/ska-data/
        $upload_dir = wp_upload_dir();
        $cache_dir = trailingslashit($upload_dir['basedir']) . 'ska-data';

        if (!wp_mkdir_p($cache_dir)) {
            error_log('Ska System Cache: Không thể tạo thư mục lưu trữ System Cache => ' . $cache_dir);
            return;
        }

        $file_path = $cache_dir . '/' . $slug . '.json';
        
        // Cố tình đẩy qua json_encode cho chuẩn cấu trúc
        $json_data = wp_json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        if (false === $json_data) {
            error_log('Ska System Cache: JSON Encode thất bại cho Table ' . $slug);
            return;
        }

        file_put_contents($file_path, $json_data);
    }

    /**
     * Lấy Dữ Liệu Tối Ưu (Zero-Query if Cached)
     * Trả về mảng dữ liệu.
     * 
     * @param string $slug Ví dụ: 'presets', 'organisms'
     * @return array
     */
    public function get_system_data($slug)
    {
        $cache_key = 'ska_sys_cache_' . $slug;
        
        // 1. Lớp RAM: Kiểm tra Transient
        $cached_data = get_transient($cache_key);
        if (false !== $cached_data) {
            return $cached_data;
        }

        // 2. Lớp Disk: Fallback quét file JSON physical
        $upload_dir = wp_upload_dir();
        $file_path = trailingslashit($upload_dir['basedir']) . 'ska-data/' . $slug . '.json';

        if (file_exists($file_path)) {
            $json_data = file_get_contents($file_path);
            $parsed = json_decode($json_data, true);
            if (is_array($parsed)) {
                // Set lại vào RAM cache để lần sau khỏi file_get_contents
                set_transient($cache_key, $parsed, DAY_IN_SECONDS * 30);
                return $parsed;
            }
        }

        // 3. Fallback Khủng hoảng: Quét Database
        global $wpdb;
        $table_name = $wpdb->prefix . 'ska_data_sys_' . $slug;
        $rows = $wpdb->get_results("SELECT * FROM `{$table_name}` ORDER BY id ASC", ARRAY_A);
        
        if (empty($rows)) {
            $rows = array();
        }

        // Setup lại cache (Vì chưa có mà)
        set_transient($cache_key, $rows, DAY_IN_SECONDS * 30);
        
        return $rows;
    }
}
