<?php
namespace Ska\Data\Admin;

defined( 'ABSPATH' ) || exit;

class Admin_Menu {
	/**
	 * Instance singleton.
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets( $hook ) {
		// Chỉ nhúng khi đang ở trang Quản lý Data
		if ( strpos( $hook, 'ska-data-pro' ) !== false ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Đăng ký Admin Menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Ska Data', 'ska-data-pro' ),
			__( 'Ska Data', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro',
			[ $this, 'render_dashboard' ],
			'dashicons-database', // Icon csdl
			31 // Vị trí bên dưới cài đặt
		);

		// Thay vì Menu ẩn, giờ ta cho hiện Menu con là Thư viện Mẫu (Dashboard)
		add_submenu_page(
			'ska-data-pro',
			__( 'Template Library', 'ska-data-pro' ),
			__( 'Template Library', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro',
			[ $this, 'render_dashboard' ]
		);

		// Menu con Workspace (Quản lý dữ liệu cài đặt)
		add_submenu_page(
			'ska-data-pro',
			__( 'Workspace', 'ska-data-pro' ),
			__( 'Workspace', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro-manage',
			[ $this, 'render_manage_page' ]
		);
	}

	/**
	 * Render trang UI Dashboard gốc.
	 */
	public function render_dashboard() {
		require_once SKA_DATA_PRO_PATH . 'inc/admin/views/dashboard.php';
	}

	/**
	 * Render trang Quản lý Bảng dữ liệu (Airtable Clone).
	 */
	public function render_manage_page() {
		require_once SKA_DATA_PRO_PATH . 'inc/admin/views/manage.php';
	}
}
