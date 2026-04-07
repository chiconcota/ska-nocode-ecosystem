<?php

defined( 'ABSPATH' ) || exit;

/**
 * Lõi phân tích Ngữ cảnh & Dữ liệu Hiển thị Frontend (Dynamic Content Engine)
 * Đóng vai trò là Máy Hydration kết nối giữa Ska Data Pro và Khối thiết kế tĩnh.
 */
class Ska_Dynamic_Content {

    private static $instance = null;
    
    // RAM Cache để tránh gọi CSDL nhiều lần cho cùng 1 Bản ghi
    private $memory_cache = [];

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Cài đặt bộ đánh chặn khi render block/content
        $this->init_hooks();
    }

    private function init_hooks() {
        // Quét chữ trong hàm in nội dung chuẩn của WordPress. 
        // Trọng số 90 để chạy sau khi Gutenberg render khối.
        add_filter( 'the_content', [ $this, 'parse_dynamic_tags' ], 90 );
    }

    /**
     * Thuật toán Máy dò & Máy chém Frontend
     */
    public function parse_dynamic_tags( $content ) {
        // Tránh chạy trong khu vực wp-admin hoặc lúc truy vấn loop phụ
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $content;
        }
        
        // Thoát sớm để tối ưu hiệu năng nếu không hề tìm thấy dấu hiệu liên kết động
        if ( strpos( $content, '{{' ) === false ) {
            return $content;
        }

        // Định dạng Bắt buộc: {{bang_du_lieu.cot_du_lieu}} (Ví dụ: {{ska_data_doctors.name}})
        $pattern = '/\{\{\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*\}\}/';
        
        // Gọi callback để tráo dòng
        $content = preg_replace_callback( $pattern, [ $this, 'hydration_callback' ], $content );

        return $content;
    }

    /**
     * Dịch từng thẻ Template Tag thành giá trị thực
     */
    private function hydration_callback( $matches ) {
        $full_tag     = $matches[0]; // Ký tự đầy đủ {{table.column}}
        $table_name   = trim( $matches[1] );
        $column_name  = trim( $matches[2] );

        // Lấy con trỏ ngữ cảnh: Phỏ biến nhất là $_GET['id'] cho các trang Chi tiết (Detail Portal)
        $record_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // Nếu không có mốc tham lượng, trả về chữ gốc chứ không báo lỗi làm gãy font của frontend
        if ( empty( $record_id ) ) {
            return $full_tag; 
        }

        // Tự động bồi đắp tiền tố bảo mật nếu bảng gõ tắt (Vd: gõ `doctors` thành `wp_ska_data_doctors`)
        global $wpdb;
        $prefix = $wpdb->prefix;
        if ( strpos( $table_name, $prefix . 'ska_data_' ) !== 0 ) {
            // Xem có chữ 'ska_data_' chưa
            if ( strpos( $table_name, 'ska_data_' ) !== 0 ) {
                $table_name = $prefix . 'ska_data_' . $table_name;
            } else {
                $table_name = $prefix . $table_name;
            }
        }

        // Kiểm tra RAM
        $cache_key = $table_name . '_' . $record_id;
        
        if ( ! isset( $this->memory_cache[ $cache_key ] ) ) {
            // Móc sang Data Pro nếu tồn tại Cỗ máy lấy dữ liệu
            if ( class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
                $args = [
                    'filter_field' => 'id',
                    'filter_val'   => $record_id,
                    'filter_op'    => 'eq'
                ];
                
                // Fetch đúng 1 row
                $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, $args, 1 );
                
                if ( ! empty( $rows ) && is_array( $rows ) ) {
                    $this->memory_cache[ $cache_key ] = $rows[0]; // Lưu túi thần cất xài dần
                } else {
                    $this->memory_cache[ $cache_key ] = false; // Đánh dấu là NULL để khỏi Query DB lại
                }
            } else {
                // Ska Data Pro chưa kích hoạt
                return $full_tag;
            }
        }

        // Đọc RAM
        $record_data = $this->memory_cache[ $cache_key ];

        // Nếu DB không có bản ghi đó
        if ( empty( $record_data ) ) {
            return ''; 
        }

        // Trả kết quả nếu Tên cột khớp. Nếu không thì rỗng.
        return isset( $record_data[ $column_name ] ) ? esc_html( (string) $record_data[ $column_name ] ) : '';
    }
}
