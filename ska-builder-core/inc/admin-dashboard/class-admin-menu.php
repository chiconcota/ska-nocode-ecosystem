<?php
/**
 * Class: Admin Menu
 * 
 * @package Ska_Builder_Core\Admin_Dashboard
 */

namespace Ska_Builder_Core\Admin_Dashboard;

defined( 'ABSPATH' ) || exit;

class Admin_Menu {

	/**
	 * Init logic.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register Admin Menu Page
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Ska Builder', 'ska-builder-core' ),
			__( 'Ska Builder', 'ska-builder-core' ),
			'manage_options',
			'ska-builder-core',
			array( $this, 'render_dashboard' ),
			'dashicons-layout',
			30
		);
	}

	/**
	 * Enqueue Assets for the admin page
	 * 
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_ska-builder-core' !== $hook ) {
			return;
		}

		// Enqueue Tailwind build or custom styles if available.
		// wp_enqueue_style( 'ska-admin-css', SKA_CORE_URL . 'assets/css/admin.css', array(), '1.0.0' );
		
		// Optional: enqueue wp-util if we need JS templating
		wp_enqueue_script( 'wp-util' );
	}

	/**
	 * Render the Dashboard Template
	 */
	public function render_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		do_action( 'ska_admin_dashboard_loaded' );
		
		require_once __DIR__ . '/views/dashboard.php';
	}
}
