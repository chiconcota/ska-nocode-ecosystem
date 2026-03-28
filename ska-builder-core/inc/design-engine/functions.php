<?php
/**
 * Design Engine Helper Functions
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Get current theme colors from Style Manager.
 *
 * @return array
 */
function get_current_theme_colors(): array {
	if ( class_exists( 'Ska\\Builder\\Design\\Style_Manager' ) ) {
		return \Ska\Builder\Design\Style_Manager::get_theme_colors();
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
	if ( class_exists( 'Ska\\Builder\\Design\\Tailwind_Compiler' ) ) {
		return \Ska\Builder\Design\Tailwind_Compiler::compile( $classes );
	}
	return '';
}
