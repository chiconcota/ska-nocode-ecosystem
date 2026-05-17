<?php
/**
 * Ska Theme Builder Module
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined( 'ABSPATH' ) || exit;

class Ska_Theme_Builder {

	/**
	 * Module initialization
	 */
	public function init() {
		// Load API Controller
		require_once plugin_dir_path( __FILE__ ) . 'class-ska-theme-builder-api.php';
		if ( class_exists( '\Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder_API' ) ) {
			\Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder_API::get_instance();
		}

		// Load Editor Wrapper
		require_once plugin_dir_path( __FILE__ ) . 'class-ska-theme-builder-editor.php';
		if ( class_exists( '\Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder_Editor' ) ) {
			\Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder_Editor::get_instance();
		}

		// Load Smart Virtual Wrapper
		require_once plugin_dir_path( __FILE__ ) . 'class-ska-virtual-wrapper.php';
		if ( class_exists( '\Ska_No_Code_Design\Theme_Builder\Ska_Virtual_Wrapper' ) ) {
			\Ska_No_Code_Design\Theme_Builder\Ska_Virtual_Wrapper::get_instance();
		}

		// Load App Router (Virtual Router & Security Layer)
		require_once plugin_dir_path( __FILE__ ) . 'class-ska-app-router.php';
		if ( class_exists( '\Ska_No_Code_Design\Theme_Builder\Ska_App_Router' ) ) {
			\Ska_No_Code_Design\Theme_Builder\Ska_App_Router::get_instance();
		}

		// Register Admin Menu (Submenu of Ska Dashboard)
		add_action( 'admin_menu', array( $this, 'register_menu' ), 20 );

		// Register AJAX Action for Folder sync
		add_action( 'wp_ajax_ska_theme_builder_save_folders', array( $this, 'ajax_save_folders' ) );
	}

	/**
	 * Register the submenu page for the Theme Builder
	 */
	public function register_menu() {
		add_submenu_page(
			'ska-system-dashboard', // Parent slug
			'Theme Builder',        // Page title
			'Theme Builder',        // Menu title
			'manage_options',       // Capability
			'ska-theme-builder',    // Menu slug
			array( $this, 'render_admin_panel' ), // Callback function
			2 // Position
		);
	}

	/**
	 * Render the Theme Builder Admin Panel (Vue/Alpine.js + Tailwind)
	 */
	public function render_admin_panel() {
		// Enqueue necessary scripts/styles for the Theme Builder UI
		// We'll assume Tailwind and Alpine are either loaded globally in admin or we load them here.
		// Including the view file.
		$view_path = plugin_dir_path( __FILE__ ) . 'views/admin-panel.php';
		if ( file_exists( $view_path ) ) {
			require_once $view_path;
		} else {
			echo '<div class="wrap"><h2>Lỗi: Không tìm thấy giao diện Theme Builder.</h2></div>';
		}
	}

	/**
	 * AJAX Handler to save the Virtual Folders JSON
	 */
	public function ajax_save_folders() {
		check_ajax_referer( 'ska_theme_builder_folders_nonce', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$folders_json = isset( $_POST['folders'] ) ? wp_unslash( $_POST['folders'] ) : '[]';
		
		// Validate JSON
		$decoded = json_decode( $folders_json, true );
		if ( ! is_array( $decoded ) ) {
			wp_send_json_error( 'Invalid JSON format' );
		}

		// Save the option with autoload = false (No-Trash Policy)
		update_option( 'ska_theme_builder_folders', wp_json_encode( $decoded ), false );

		wp_send_json_success( 'Folders saved successfully' );
	}
}
