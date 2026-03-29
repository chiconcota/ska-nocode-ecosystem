<?php
/**
 * Logic Engine Module
 *
 * Handles conditional logic and loops for dynamic templates.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Logic;

defined( 'ABSPATH' ) || exit;

// Constants
define( 'SKA_LOGIC_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKA_LOGIC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Logic Engine classes.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'Ska\\Builder\\Logic\\';
	$base_dir = SKA_LOGIC_PATH;

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
	if ( class_exists( 'Ska\\Builder\\Logic\\Core' ) ) {
		\Ska\Builder\Logic\Core::instance();
	}
} );
