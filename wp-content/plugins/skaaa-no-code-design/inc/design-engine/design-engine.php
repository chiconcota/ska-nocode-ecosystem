<?php
/**
 * Design Engine Module
 *
 * Handles Tailwind CSS JIT compilation, Style Management, and CSS Scoping.
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Design;

defined( 'ABSPATH' ) || exit;

// Constants.
define( 'SKAAA_DESIGN_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKAAA_DESIGN_ENGINE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Design Engine classes.
 *
 * @param string $class Class name.
 */
spl_autoload_register( function ( $class ) {
	$prefix   = 'Skaaa\\Builder\\Design\\';
	$base_dir = SKAAA_DESIGN_ENGINE_PATH;

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php.
	// Also convert CamelCase to kebab-case for file names.
	$file_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', str_replace( '_', '-', $relative_class ) ) );
	$file      = $base_dir . 'class-' . $file_name . '.php';

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Helper functions.
require_once SKAAA_DESIGN_ENGINE_PATH . 'functions.php';

// Initialize the Core class.
if ( class_exists( 'Skaaa\\Builder\\Design\\Core' ) ) {
	\Skaaa\Builder\Design\Core::instance();
}

// Initialize Theme Builder Components (with Graceful Fallback).
if ( class_exists( 'Skaaa\\Builder\\Design\\Skaaa_Template_Router' ) && class_exists( '\\Skaaa_System_Framework\\Dependency_Manager' ) ) {
    if ( \Skaaa_System_Framework\Dependency_Manager::is_theme_builder_supported() ) {
        \Skaaa\Builder\Design\Skaaa_Template_Router::instance();
    }
}
