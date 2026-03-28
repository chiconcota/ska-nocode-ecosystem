<?php
/**
 * Data Engine Module
 *
 * Handles dynamic data binding, context management, and data retrieval.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data;

defined( 'ABSPATH' ) || exit;

// Constants.
define( 'SKA_DATA_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKA_DATA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Data Engine classes.
 *
 * @param string $class Class name.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'Ska\\Builder\\Data\\';
	$base_dir = SKA_DATA_PATH;

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Handle sub-namespaces (e.g. Providers\) mapping to subdirectories.
	// Replace namespace separators with directory separators.
	$file_path = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class );

	// Convert CamelCase to kebab-case for file names.
	// We need to handle directory structure carefully.
	// Example: Providers\WP_Post_Provider -> providers/class-wp-post-provider.php
	
	$parts = explode( DIRECTORY_SEPARATOR, $file_path );
	$file_name = array_pop( $parts );
	
	// Convert parts to lowercase (directories)
	$parts = array_map( 'strtolower', $parts );
	
	// Convert filename to kebab-case and prepend 'class-'
	$file_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', str_replace( '_', '-', $file_name ) ) );
	$file_name = 'class-' . $file_name . '.php';

	// Rebuild path
	$full_path = $base_dir . ( ! empty( $parts ) ? implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR : '' ) . $file_name;

	// If the file exists, require it.
	if ( file_exists( $full_path ) ) {
		require $full_path;
	}
} );

// Helper functions.
require_once SKA_DATA_PATH . 'functions.php';

// Interfaces.
require_once SKA_DATA_PATH . 'interface-provider.php';

// Initialize the Core class.
add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
		\Ska\Builder\Data\Core::instance();
	}
} );
