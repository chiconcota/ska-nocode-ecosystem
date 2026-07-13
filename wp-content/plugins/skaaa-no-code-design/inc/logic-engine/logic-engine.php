<?php
/**
 * Logic Engine Module
 *
 * Handles conditional logic and loops for dynamic templates.
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Logic;

defined( 'ABSPATH' ) || exit;

// Constants
define( 'SKAAA_LOGIC_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKAAA_LOGIC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Logic Engine classes.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'Skaaa\\Builder\\Logic\\';
	$base_dir = SKAAA_LOGIC_PATH;

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Initialize Core
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Skaaa\\Builder\\Logic\\Core' ) ) {
		\Skaaa\Builder\Logic\Core::instance();
	}
} );
