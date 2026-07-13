<?php
/**
 * Class Skaaa_Code_Block_Queue
 *
 * Quản lý hàng đợi và tự động quét các block Skaaa Code trên trang để nạp ở Header/Footer chính xác, tránh lỡ nhịp wp_head.
 *
 * @package Skaaa_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Skaaa_Code_Block_Queue' ) ) {
	class Skaaa_Code_Block_Queue {

		/**
		 * Hàng đợi code in ở Header
		 *
		 * @var array
		 */
		private static $header_queue = [];

		/**
		 * Hàng đợi code in ở Footer
		 *
		 * @var array
		 */
		private static $footer_queue = [];

		/**
		 * Cờ đánh dấu đã quét trang hiện tại chưa
		 *
		 * @var bool
		 */
		private static $scanned = false;

		/**
		 * Cờ đánh dấu đã đăng ký hook hiển thị với WordPress chưa
		 *
		 * @var bool
		 */
		private static $hooks_registered = false;

		/**
		 * Khởi tạo sớm cho frontend để quét bài viết/trang trước khi wp_head chạy
		 */
		public static function init() {
			if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				return;
			}

			// Đăng ký hook 'wp_head' độ ưu tiên 1 để quét blocks sớm ngay khi bắt đầu wp_head.
			// Lúc này, cả query chính ($post) và Theme Builder active organisms đều đã sẵn sàng.
			add_action( 'wp_head', [ __CLASS__, 'scan_current_page_blocks' ], 1 );

			// Đăng ký sẵn hooks render ở head/footer
			self::register_hooks();
		}

		/**
		 * Đẩy một đoạn mã inline vào hàng đợi
		 *
		 * @param string $code Đoạn mã nguồn.
		 * @param string $location Vị trí nạp 'header' hoặc 'footer'.
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
		 * Quét bài viết/trang hiện tại để tìm các block Skaaa Code cần nạp sớm
		 */
		public static function scan_current_page_blocks() {
			if ( self::$scanned ) {
				return;
			}

			$posts_to_scan = [];

			// 1. Xác định các posts cần quét dựa trên ngữ cảnh trang hiện tại
			if ( is_home() || is_archive() || is_front_page() || is_search() ) {
				global $wp_query;
				if ( ! empty( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
					$posts_to_scan = $wp_query->posts;
				}
			} else {
				$post = get_post();
				if ( $post ) {
					$posts_to_scan[] = $post;
				}
			}

			// 2. Quét content của các bài viết tìm thấy
			foreach ( $posts_to_scan as $p ) {
				if ( ! empty( $p->post_content ) ) {
					$blocks = parse_blocks( $p->post_content );
					self::scan_blocks( $blocks );
				}
			}

			// 3. Quét các Organisms (Symbols) đang active (nếu được load qua Theme Builder)
			global $skaaa_active_theme_organisms;
			if ( ! empty( $skaaa_active_theme_organisms ) && is_array( $skaaa_active_theme_organisms ) ) {
				foreach ( $skaaa_active_theme_organisms as $org_id ) {
					$org_post = get_post( $org_id );
					if ( $org_post && ! empty( $org_post->post_content ) ) {
						$blocks = parse_blocks( $org_post->post_content );
						self::scan_blocks( $blocks );
					}
				}
			}

			self::$scanned = true;
		}

		/**
		 * Đệ quy duyệt cây block tìm block 'skaaaaa-builder/code'
		 *
		 * @param array $blocks Mảng các block parsed.
		 */
		public static function scan_blocks( $blocks ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return;
			}

			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'skaaaaa-builder/code' === $block['blockName'] ) {
					$attrs             = isset( $block['attrs'] ) ? $block['attrs'] : [];
					$code_type         = isset( $attrs['codeType'] ) ? $attrs['codeType'] : 'inline';
					$location          = isset( $attrs['location'] ) ? $attrs['location'] : 'inline';
					$inline_code       = isset( $attrs['inlineCode'] ) ? $attrs['inlineCode'] : '';
					$library_script_id = isset( $attrs['libraryScriptId'] ) ? $attrs['libraryScriptId'] : '';

					if ( 'inline' === $code_type ) {
						if ( 'inline' !== $location && ! empty( $inline_code ) ) {
							self::add( $inline_code, $location );
						}
					} elseif ( 'library' === $code_type && ! empty( $library_script_id ) ) {
						if ( has_action( 'skaaa_enqueue_custom_script' ) ) {
							do_action( 'skaaa_enqueue_custom_script', $library_script_id );
						}
					}
				}

				// Đệ quy quét inner blocks (ví dụ Skaaa Code lồng trong Skaaa Container)
				if ( ! empty( $block['innerBlocks'] ) ) {
					self::scan_blocks( $block['innerBlocks'] );
				}
			}
		}

		/**
		 * Render toàn bộ code trong hàng đợi Header
		 */
		public static function render_header() {
			if ( ! empty( self::$header_queue ) ) {
				echo "\n<!-- Skaaa Code Block: Header Inline Scripts -->\n";
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
				echo "\n<!-- Skaaa Code Block: Footer Inline Scripts -->\n";
				foreach ( self::$footer_queue as $code ) {
					echo $code . "\n";
				}
			}
		}
	}
}
