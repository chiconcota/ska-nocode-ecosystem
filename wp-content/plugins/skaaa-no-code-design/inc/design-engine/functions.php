<?php
/**
 * Design Engine Helper Functions
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Get current theme colors from Style Manager.
 *
 * @return array
 */
function get_current_theme_colors(): array {
	if ( class_exists( 'Skaaa\\Builder\\Design\\Style_Manager' ) ) {
		return \Skaaa\Builder\Design\Style_Manager::get_theme_colors();
	}
	return array();
}

/**
 * Compile Tailwind classes to CSS.
 *
 * @param string $classes Space-separated class names.
 * @return string
 */
function compile_tailwind( string $classes ): string {
	if ( class_exists( 'Skaaa\\Builder\\Design\\Tailwind_Compiler' ) ) {
		return \Skaaa\Builder\Design\Tailwind_Compiler::compile( $classes );
	}
	return '';
}
