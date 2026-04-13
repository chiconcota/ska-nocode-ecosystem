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
        add_action( 'ska_system_dashboard_modules', [ $this, 'render_dashboard_card' ] );
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
		add_submenu_page(
			'ska-system-framework',
			__( 'Ska Data', 'ska-data-pro' ),
			__( 'Ska Data', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'ska-system-framework',
			__( 'Workspace', 'ska-data-pro' ),
			__( 'Workspace', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro-manage',
			[ $this, 'render_manage_page' ]
		);
	}

    public function render_dashboard_card() {
        ?>
        <!-- Module: Data Pro -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row gap-5 relative overflow-hidden mt-6">
            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-blue-100">
                <span class="material-symbols-outlined text-[28px]">database</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Ska Data Pro</h3>
                        <p class="text-sm text-slate-500 mt-1">Quản trị Flat Tables (ska_data_*), Schema Manager và Strategy Pattern DataGrid. Thay thế hoàn toàn wp_postmeta.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Đang hoạt động
                    </span>
                </div>
                <div class="mt-4 flex gap-3 text-sm">
                    <a href="?page=ska-data-pro" class="text-indigo-600 font-medium hover:text-indigo-800 hover:underline">Truy cập Database</a>
                    <span class="text-slate-300">|</span>
                    <a href="?page=ska-data-pro-manage" class="text-slate-600 hover:text-slate-900">Quản lý Semantic IDs</a>
                </div>
            </div>
        </div>
        <?php
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
