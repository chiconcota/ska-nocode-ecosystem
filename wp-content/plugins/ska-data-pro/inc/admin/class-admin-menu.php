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
		add_action( 'admin_menu', [ $this, 'register_menu' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'ska_system_dashboard_modules', [ $this, 'render_dashboard_card' ] );
		add_action( 'ska_system_dashboard_extensions', [ $this, 'render_extensions' ] );
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
			'ska-system-dashboard',
			__( 'Ska Data', 'ska-data-pro' ),
			__( 'Ska Data', 'ska-data-pro' ),
			'manage_options',
			'ska-data-pro',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'ska-system-dashboard',
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
        <div class="module-card rounded-2xl p-6 flex flex-col sm:flex-row gap-6 relative overflow-hidden mt-4 group">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-blue-400 to-indigo-600 opacity-80 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 border border-blue-100 shadow-inner group-hover:scale-105 transition-transform duration-300">
                <span class="material-symbols-outlined text-[32px]">database</span>
            </div>
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg m-0 p-0">Ska Data Pro</h3>
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed">Quản trị Flat Tables (ska_data_*), Schema Manager và Strategy Pattern DataGrid. Thay thế hoàn toàn wp_postmeta.</p>
                    </div>
                </div>
                <div class="mt-5 flex gap-4 text-sm">
                    <a href="?page=ska-data-pro" class="inline-flex items-center gap-1 text-indigo-600 font-semibold hover:text-indigo-800 transition-colors bg-indigo-50 px-3 py-1.5 rounded-lg no-underline">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span> Truy cập Database
                    </a>
                    <a href="?page=ska-data-pro-manage" class="inline-flex items-center gap-1 text-slate-600 font-medium hover:text-slate-900 transition-colors px-3 py-1.5 hover:bg-slate-50 rounded-lg no-underline">
                        <span class="material-symbols-outlined text-[18px]">settings</span> Quản lý Semantic IDs
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

	public function render_extensions() {
		?>
		<a href="?page=ska-data-pro-manage&app=ska_system" class="block bg-white/80 backdrop-blur rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:border-indigo-300 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group no-underline text-slate-800 relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
			<div class="w-12 h-12 bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4 border border-indigo-100 shadow-sm relative z-10 group-hover:scale-110 transition-transform">
				<span class="material-symbols-outlined text-[24px]">web_stories</span>
			</div>
			<h3 class="font-bold text-base m-0 p-0 border-0 mb-2 group-hover:text-indigo-600 transition-colors relative z-10">Site Blueprint</h3>
			<p class="text-xs text-slate-500 m-0 leading-relaxed relative z-10">Quản lý Theme Templates, Organisms & Tokens cho Web App.</p>
		</a>
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
