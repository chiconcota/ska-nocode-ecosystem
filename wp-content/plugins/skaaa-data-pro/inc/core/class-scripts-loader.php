<?php
namespace Skaaa\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Scripts_Loader
 * Quản lý khởi tạo database, nạp và thực thi các script ngoài frontend theo cấu hình.
 * 
 * @package Skaaa\Data\Core
 */
class Scripts_Loader {

	/**
	 * Instance duy nhất (Singleton)
	 *
	 * @var Scripts_Loader
	 */
	private static $instance = null;

	/**
	 * Danh sách các script được gọi nạp chủ động từ bên ngoài (ép nạp)
	 *
	 * @var array
	 */
	private static $enqueued_scripts = [];

	/**
	 * Danh sách các script ID đã được in ra trang (để tránh in lặp chéo)
	 *
	 * @var array
	 */
	private static $rendered_script_ids = [];

	/**
	 * Lấy instance duy nhất của class
	 *
	 * @return Scripts_Loader
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Khởi tạo hooks
	 */
	private function __construct() {
		// Bảo vệ bảng phẳng hệ thống khỏi bị xóa/sửa cấu trúc
		add_filter( 'skaaa_data_protected_tables', [ $this, 'protect_scripts_table' ] );

		// Hook nạp script ngoài frontend
		if ( ! is_admin() ) {
			add_action( 'wp_head', [ $this, 'load_header_scripts' ], 100 );
			add_action( 'wp_footer', [ $this, 'load_footer_scripts' ], 100 );
		}

		// Hook cho các plugin khác/block Gutenberg gọi nạp script decoupled
		add_action( 'skaaa_enqueue_custom_script', [ __CLASS__, 'enqueue_custom_script' ] );
	}

	/**
	 * Đăng ký bảo vệ bảng trong hệ thống
	 *
	 * @param array $protected_tables
	 * @return array
	 */
	public function protect_scripts_table( $protected_tables ) {
		global $wpdb;
		$protected_tables[] = $wpdb->prefix . 'skaaa_data_sys_scripts';
		return $protected_tables;
	}

	/**
	 * Khởi tạo bảng phẳng mysql cho thư viện scripts nếu chưa tồn tại
	 */
	public static function maybe_create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_scripts';

		// Kiểm tra bảng đã tồn tại chưa
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table_name}` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`script_id` varchar(100) NOT NULL,
			`name` varchar(255) NOT NULL,
			`type` varchar(50) NOT NULL,
			`content` longtext DEFAULT NULL,
			`location` varchar(20) NOT NULL DEFAULT 'footer',
			`load_condition` varchar(50) NOT NULL DEFAULT 'global',
			`conditions` longtext DEFAULT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `script_id` (`script_id`)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Cập nhật Dictionary để lưu thông tin bảng hệ thống
		$dictionary = get_option( 'skaaa_data_dictionary', [] );
		if ( ! is_array( $dictionary ) ) {
			$dictionary = [];
		}

		$dictionary[ $table_name ] = [
			'__table_info' => [
				'name'   => 'Scripts Library',
				'icon'   => 'dashicons-code-standards',
				'app_id' => 'skaaa_system'
			]
		];
		update_option( 'skaaa_data_dictionary', $dictionary );
	}

	/**
	 * Đăng ký nạp chủ động một script từ bên ngoài (ví dụ: từ block Gutenberg)
	 *
	 * @param string $script_id
	 */
	public static function enqueue_custom_script( $script_id ) {
		$script_id = sanitize_key( $script_id );
		if ( ! in_array( $script_id, self::$enqueued_scripts, true ) ) {
			self::$enqueued_scripts[] = $script_id;
		}
	}

	/**
	 * Nạp các script cấu hình chạy ở Header
	 */
	public function load_header_scripts() {
		$this->render_scripts( 'header' );
	}

	/**
	 * Nạp các script cấu hình chạy ở Footer
	 */
	public function load_footer_scripts() {
		$this->render_scripts( 'footer' );
	}

	/**
	 * Truy vấn và in ra mã script ngoài Frontend
	 *
	 * @param string $location 'header' hoặc 'footer'
	 */
	private function render_scripts( $location ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_scripts';

		// Kiểm tra bảng tồn tại trước khi truy vấn để tránh crash khi chưa chạy migration
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
			return;
		}

		// Lấy danh sách script đang active tại vị trí được yêu cầu (lọc cứng theo location của database)
		$scripts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$table_name}` WHERE `status` = 1 AND `location` = %s ORDER BY `id` ASC",
				$location
			)
		);

		if ( empty( $scripts ) && empty( self::$enqueued_scripts ) ) {
			return;
		}

		// Nếu có script được enqueue chủ động, truy vấn thêm các script đó
		if ( ! empty( self::$enqueued_scripts ) ) {
			$placeholders = implode( ',', array_fill( 0, count( self::$enqueued_scripts ), '%s' ) );
			$extra_scripts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$table_name}` WHERE `status` = 1 AND `script_id` IN ($placeholders) ORDER BY `id` ASC",
					self::$enqueued_scripts
				)
			);
			
			// Lọc lại các script enqueue động tùy thuộc vào vị trí và trạng thái in
			$filtered_extras = [];
			foreach ( $extra_scripts as $s ) {
				if ( 'header' === $location ) {
					// Ở header: chỉ in các script enqueue động có cấu hình location là header
					if ( 'header' === $s->location ) {
						$filtered_extras[] = $s;
					}
				} else {
					// Ở footer: in tất cả các script enqueue động chưa được in ở header
					if ( ! in_array( $s->script_id, self::$rendered_script_ids, true ) ) {
						$filtered_extras[] = $s;
					}
				}
			}
			$extra_scripts = $filtered_extras;

			// Hợp nhất và loại bỏ script trùng lặp
			$merged = [];
			$seen_ids = [];
			foreach ( array_merge( $scripts, $extra_scripts ) as $s ) {
				if ( ! in_array( $s->id, $seen_ids, true ) ) {
					$merged[] = $s;
					$seen_ids[] = $s->id;
				}
			}
			$scripts = $merged;
		}

		// In ra mã nguồn ngoài frontend
		foreach ( $scripts as $script ) {
			if ( ! $this->should_load_script( $script ) ) {
				continue;
			}

			$content = $script->content;
			if ( empty( $content ) ) {
				continue;
			}

			// Đánh dấu script đã được rendered
			if ( ! in_array( $script->script_id, self::$rendered_script_ids, true ) ) {
				self::$rendered_script_ids[] = $script->script_id;
			}

			// In HTML comment để hỗ trợ debug và xác minh E2E
			echo '<!-- Skaaa Script: ' . esc_html( $script->script_id ) . " -->\n";

			switch ( $script->type ) {
				case 'js_file':
					printf( "<script src=\"%s\"></script>\n", esc_url( $content ) );
					break;

				case 'css_file':
					printf( "<link rel=\"stylesheet\" href=\"%s\">\n", esc_url( $content ) );
					break;

				case 'js_inline':
					// Không escape HTML cho script inline để giữ nguyên mã nguồn JS tùy biến
					printf( "<script>\n%s\n</script>\n", $content );
					break;

				case 'css_inline':
					// Không escape HTML cho style inline để giữ nguyên mã nguồn CSS tùy biến
					printf( "<style>\n%s\n</style>\n", $content );
					break;
			}
		}
	}

	/**
	 * Kiểm tra xem script có thỏa mãn điều kiện nạp của trang hiện tại hay không
	 *
	 * @param object $script
	 * @return bool
	 */
	private function should_load_script( $script ) {
		// Nếu script được gọi nạp chủ động từ bên ngoài (ép nạp), bỏ qua kiểm tra điều kiện
		if ( in_array( $script->script_id, self::$enqueued_scripts, true ) ) {
			return true;
		}

		// Global: nạp toàn trang
		if ( 'global' === $script->load_condition ) {
			return true;
		}

		// Conditional: kiểm tra điều kiện chi tiết
		if ( 'conditional' === $script->load_condition && ! empty( $script->conditions ) ) {
			$conditions = json_decode( $script->conditions, true );
			if ( ! is_array( $conditions ) ) {
				return false;
			}

			// 1. Kiểm tra điều kiện Trang / Bài viết (Pages)
			if ( ! empty( $conditions['pages'] ) ) {
				$current_page_id = get_queried_object_id();
				if ( in_array( $current_page_id, array_map( 'intval', $conditions['pages'] ), true ) ) {
					return true;
				}
			}

			// 2. Kiểm tra điều kiện App Workspace (Apps)
			if ( ! empty( $conditions['apps'] ) ) {
				// Lấy App ID/Workspace ID hiện tại từ query var hoặc global state (nếu có)
				$current_app_id = get_query_var( 'skaaa_app_id' );
				if ( empty( $current_app_id ) && isset( $_GET['app'] ) ) {
					$current_app_id = sanitize_key( $_GET['app'] );
				}
				
				if ( ! empty( $current_app_id ) && in_array( $current_app_id, $conditions['apps'], true ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
