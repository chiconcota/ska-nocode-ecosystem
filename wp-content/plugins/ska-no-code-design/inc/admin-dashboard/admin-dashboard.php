<?php
/**
 * Module: Admin Dashboard
 * 
 * @package Ska_Builder_Core\Admin_Dashboard
 */

namespace Ska_Builder_Core\Admin_Dashboard;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-admin-menu.php';

/**
 * Initialize the Admin Dashboard module.
 */
function init() {
	if ( ! is_admin() ) {
		return;
	}

    // Handle settings saving
    if ( isset( $_POST['ska_save_settings'] ) && check_admin_referer( 'ska_save_dashboard_settings' ) ) {
        $bridge_enabled = isset( $_POST['ska_bridge_enabled'] ) ? 'yes' : 'no';
        update_option( 'ska_bridge_enabled', $bridge_enabled );
        
        add_action( 'admin_notices', function() {
            echo '<div class="updated"><p>' . esc_html__( 'Settings saved successfully.', 'ska-builder-core' ) . '</p></div>';
        } );
    }

    // Handle custom colors saving
    if ( isset( $_POST['ska_save_colors'] ) && check_admin_referer( 'ska_save_custom_colors' ) ) {
        $names  = isset( $_POST['ska_color_names'] ) ? array_map( 'sanitize_text_field', $_POST['ska_color_names'] ) : array();
        $values = isset( $_POST['ska_color_values'] ) ? array_map( 'sanitize_text_field', $_POST['ska_color_values'] ) : array();

        $colors = array();
        foreach ( $names as $i => $name ) {
            $name = sanitize_key( $name );
            $hex  = trim( $values[ $i ] ?? '' );

            // Tự động thêm # nếu user quên nhập
            if ( $hex && '#' !== $hex[0] ) {
                $hex = '#' . $hex;
            }

            // Validate hex format: #RGB hoặc #RRGGBB
            if ( $name && $hex && preg_match( '/^#([0-9a-fA-F]{3}){1,2}$/', $hex ) ) {
                $colors[ $name ] = strtolower( $hex );
            }
        }
        update_option( 'ska_custom_colors', $colors );

        // PRG Redirect để tránh re-submit và đảm bảo UI load đúng
        wp_safe_redirect( admin_url( 'admin.php?page=ska-builder-core&ska_colors_saved=1' ) );
        exit;
    }

    // Hiển thị thông báo sau redirect
    if ( isset( $_GET['ska_colors_saved'] ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="updated"><p>' . esc_html__( 'Brand Colors saved.', 'ska-builder-core' ) . '</p></div>';
        } );
    }
	
	$admin_menu = new Admin_Menu();
	$admin_menu->init();
}

add_action( 'init', __NAMESPACE__ . '\init' );
