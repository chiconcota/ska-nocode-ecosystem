<?php
/**
 * Ska Code Block Frontend Render
 * 
 * @package Ska_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Ska_Code_Block_Queue
 * Quản lý hàng đợi và in các đoạn mã inline code ở header/footer không trùng lặp.
 */
if ( ! class_exists( 'Ska_Code_Block_Queue' ) ) {
    class Ska_Code_Block_Queue {
        /**
         * Hàng đợi code in ở Header
         * @var array
         */
        private static $header_queue = [];

        /**
         * Hàng đợi code in ở Footer
         * @var array
         */
        private static $footer_queue = [];

        /**
         * Cờ đánh dấu đã đăng ký hook với WordPress chưa
         * @var bool
         */
        private static $hooks_registered = false;

        /**
         * Đẩy một đoạn mã inline vào hàng đợi
         *
         * @param string $code
         * @param string $location 'header' hoặc 'footer'
         */
        public static function add( $code, $location ) {
            if ( empty( $code ) ) {
                return;
            }
            
            // Băm MD5 nội dung code để chống in trùng lặp
            $hash = md5( $code );
            
            if ( 'header' === $location ) {
                self::$header_queue[ $hash ] = $code;
            } else {
                self::$footer_queue[ $hash ] = $code;
            }

            self::register_hooks();
        }

        /**
         * Đăng ký hook hiển thị với WordPress
         */
        private static function register_hooks() {
            if ( self::$hooks_registered ) {
                return;
            }

            // In ra ở mức ưu tiên muộn (101) sau khi các style/script mặc định đã load
            add_action( 'wp_head', [ __CLASS__, 'render_header' ], 101 );
            add_action( 'wp_footer', [ __CLASS__, 'render_footer' ], 101 );
            
            self::$hooks_registered = true;
        }

        /**
         * Render toàn bộ code trong hàng đợi Header
         */
        public static function render_header() {
            if ( ! empty( self::$header_queue ) ) {
                echo "\n<!-- Ska Code Block: Header Inline Scripts -->\n";
                foreach ( self::$header_queue as $code ) {
                    echo $code . "\n";
                }
            }
        }

        /**
         * Render toàn bộ code trong hàng đợi Footer
         */
        public static function render_footer() {
            if ( ! empty( self::$footer_queue ) ) {
                echo "\n<!-- Ska Code Block: Footer Inline Scripts -->\n";
                foreach ( self::$footer_queue as $code ) {
                    echo $code . "\n";
                }
            }
        }
    }
}

// Trích xuất các thuộc tính của block
$code_type         = isset( $attributes['codeType'] ) ? $attributes['codeType'] : 'inline';
$library_script_id = isset( $attributes['libraryScriptId'] ) ? $attributes['libraryScriptId'] : '';
$inline_code       = isset( $attributes['inlineCode'] ) ? $attributes['inlineCode'] : '';
$location          = isset( $attributes['location'] ) ? $attributes['location'] : 'inline';

// 1. Trường hợp nạp từ Thư viện Scripts
if ( 'library' === $code_type && ! empty( $library_script_id ) ) {
    // Gọi action decoupled gửi sang Ska Data Pro để load script và khử trùng lặp
    if ( has_action( 'ska_enqueue_custom_script' ) ) {
        do_action( 'ska_enqueue_custom_script', $library_script_id );
    } else {
        echo "<!-- Ska Code: Script Library is unavailable because Ska Data Pro is inactive. -->";
    }
} 
// 2. Trường hợp viết Inline trực tiếp
elseif ( 'inline' === $code_type && ! empty( $inline_code ) ) {
    if ( 'inline' === $location ) {
        // In trực tiếp tại chỗ (giống Custom HTML của WordPress)
        echo $inline_code;
    } else {
        // Đẩy vào hàng đợi để in ở Head/Footer không trùng lặp
        Ska_Code_Block_Queue::add( $inline_code, $location );
    }
}
