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
        // [Legacy] Quét chữ Mustache {{...}} trong hàm in nội dung chuẩn của WordPress. 
        // Trọng số 90 để chạy sau khi Gutenberg render khối.
        add_filter( 'the_content', [ $this, 'parse_dynamic_tags' ], 90 );

        // [Ska V2] Đánh chặn luồng Render Block chuẩn của Universal Dynamic Binding 
        add_filter( 'render_block', [ $this, 'parse_ska_blocks' ], 10, 2 );
    }

    /**
     * Máy Nghiền & Gắn Dữ Liệu NoCode SkaFX V2
     */
    public function parse_ska_blocks( $block_content, $block ) {
        // Chỉ xử lý ngoài Frontend 
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $block_content;
        }

        // Bỏ qua nếu không phải Block của nhà Ska
        if ( strpos( $block['blockName'], 'ska-builder/' ) !== 0 ) {
            return $block_content;
        }

        // Hút Sinh lực từ Universal Dynamic Binding
        if ( isset( $block['attrs']['skaDynamicBinding']['script'] ) && ! empty( $block['attrs']['skaDynamicBinding']['script'] ) ) {
            $script = $block['attrs']['skaDynamicBinding']['script'];

            // Lấy con trỏ ngữ cảnh: Phổ biến nhất là $_GET['id'] cho các trang Chi tiết (Detail Portal)
            $record_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
            // Cho phép Frontend hiểu ngữ cảnh bảng hiện tại
            $table_alias = isset( $_GET['table'] ) ? sanitize_text_field( $_GET['table'] ) : '';
            
            // Cung cấp GLOBAL_ID cho Phân giải AST
            $context = [ 
                'GLOBAL_ID' => $record_id,
                'GLOBAL_TABLE' => $table_alias 
            ]; 

            if ( class_exists( '\Ska\Logic\SkaFX\SkaFX_Engine' ) ) {
                $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $script, $context );
                $symbols = $result['symbols'];

                // --- 1. Sát thủ Ẩn Hiện (Visibility Check) ---
                // Chấp nhận biến `visible = true` HOẶC nếu User chỉ gõ điều kiện cụt lủn `[tuoi] > 18` (trả về Boolean thuần túy)
                $is_visible = true;
                if ( isset( $symbols['visible'] ) ) {
                    $is_visible = (bool) $symbols['visible'];
                } elseif ( isset( $result['last_val'] ) && is_bool( $result['last_val'] ) ) {
                    $is_visible = $result['last_val'];
                }

                if ( ! $is_visible ) {
                    // Máy chém cắt đứt hoàn toàn Block ra khỏi giao diện HTML mà không cần CSS Display None!
                    return ''; 
                }

                // --- 2. Bơm Máu Giao Diện (Data Hydration) ---
                // Ưu tiên đọc biến `var data = ...` trước. Nếu User lười chỉ gõ cụt lủn `[app.model.col]` thì ta lấy kết quả tính toán cuối cùng (last_val).
                $new_data = null;
                if ( isset( $symbols['data'] ) ) {
                    $new_data = $symbols['data'];
                } elseif ( isset( $result['last_val'] ) && $result['last_val'] !== true && $result['last_val'] !== false && $result['last_val'] !== null ) {
                    $new_data = $result['last_val'];
                }

                if ( $new_data !== null ) {
                    
                    if ( $block['blockName'] === 'ska-builder/text' ) {
                        // Regex cực mạnh thay thế nội dung TEXT nằm giữa 2 thẻ Wrapper rỗng lõi của Gutenberg (Tạm thời không hỗ trợ Nested bên trong)
                        $block_content = preg_replace( '/(<[a-zA-Z0-9]+[^>]*>)(.*?)(<\/[a-zA-Z0-9]+>\s*)$/s', '${1}' . esc_html( $new_data ) . '${3}', $block_content );
                    }
                    else if ( $block['blockName'] === 'ska-builder/image' ) {
                         // Dự báo Logic tương lai: Thúc thẳng vảo thẻ <img src="...">
                         // Tạm thời chưa build vì hệ thống SkaFX cần trả object cho Ảnh.
                    }
                }
            }
        }
        
        return $block_content;
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

        // Nâng cấp Regex để hỗ trợ linh hoạt 1 phần, 2 phần, hoặc 3 phần
        // Chấp nhận: {{name}}, {{doctors.name}}, {{app.doctors.name}}
        $pattern = '/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/';
        
        // Gọi callback để tráo dòng
        $content = preg_replace_callback( $pattern, [ $this, 'hydration_callback' ], $content );

        return $content;
    }

    /**
     * Dịch từng thẻ Template Tag thành giá trị thực
     */
    private function hydration_callback( $matches ) {
        $full_tag     = $matches[0]; // Ký tự đầy đủ {{...}}
        $var_name     = trim( $matches[1] );

        // Lấy con trỏ ngữ cảnh
        $record_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        $table_alias = isset( $_GET['table'] ) ? sanitize_text_field( $_GET['table'] ) : '';

        // Nếu không có mốc tham lượng, trả về chữ gốc chứ không báo lỗi làm gãy font của frontend
        if ( empty( $record_id ) ) {
            return $full_tag; 
        }

        if ( ! class_exists( '\Ska\Logic\SkaFX\SkaFX_Context_Resolver' ) ) {
            return $full_tag; // Chưa nạp file hoặc hệ thống chưa sẵn sàng
        }

        $context = [
            'GLOBAL_ID'    => $record_id,
            'GLOBAL_TABLE' => $table_alias
        ];

        try {
            // Mượn Cỗ máy Resolver mới để phân giải
            $resolved_context = \Ska\Logic\SkaFX\SkaFX_Context_Resolver::resolve( $var_name, $context );
        } catch ( \Exception $e ) {
            // NẾU CÓ XUNG ĐỘT (Collision), VÀ LÀ ADMIN ĐANG XEM THÌ BÁO CHỮ ĐỎ
            $dev_mode = get_option( 'ska_system_dev_mode', '1' );
            if ( current_user_can('manage_options') && $dev_mode === '1' ) {
                return '<span style="color:red; font-weight:bold; background:#fee2e2; padding:2px 4px; border-radius:4px; font-size:12px;">[' . esc_html($e->getMessage()) . ']</span>';
            }
            return ''; // Khách ngoài hoặc đã tắt Dev Mode thì nuốt lỗi giấu đi
        }

        if ( ! $resolved_context ) {
            return $full_tag; // Không giải mã được (ví dụ do thiếu _GET['table'] cho biến Mức 1)
        }

        $table_name  = $resolved_context['table_name'];
        $column_name = $resolved_context['column'];

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
