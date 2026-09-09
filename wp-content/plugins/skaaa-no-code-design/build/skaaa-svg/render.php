<?php
/**
 * Render: Skaaa SVG
 * 
 * Flat DOM rendering for native inline SVG vector graphics.
 * Injects get_block_wrapper_attributes() directly into the root <svg> element.
 * 
 * @package Skaaa_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

$svg_code             = $attributes['svgCode'] ?? '';
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

// 1. Conditional Logic evaluation
if ( ! empty( $attributes['logic']['enabled'] ) ) {
	if ( class_exists( '\Skaaa\Builder\Logic\Core' ) ) {
		$engine = \Skaaa\Builder\Logic\Core::instance();
		if ( ! $engine->should_render( $attributes['logic'] ) ) {
			return '';
		}
	}
}

if ( empty( trim( $svg_code ) ) ) {
	return '';
}

// 2. Prepare block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'skaaa-svg-block ' . esc_attr( $user_tailwindClasses ),
) );

$trimmed_svg = trim( $svg_code );

// 3. Flat DOM injection directly into the root <svg> tag
if ( preg_match( '/^<svg\b([^>]*)>/i', $trimmed_svg, $matches ) ) {
	$existing_attrs = $matches[1];

	// Remove existing class attribute from SVG if present so wrapper attributes are single source of truth
	$cleaned_attrs = preg_replace( '/\bclass=["\'][^"\']*["\']/i', '', $existing_attrs );

	// Replace the opening <svg ...> tag with <svg $wrapper_attributes $cleaned_attrs>
	$rendered_svg = preg_replace(
		'/^<svg\b[^>]*>/i',
		'<svg ' . $wrapper_attributes . ' ' . trim( $cleaned_attrs ) . '>',
		$trimmed_svg,
		1
	);

	echo $rendered_svg;
} else {
	// Fallback for inner shapes provided without <svg> wrapper
	printf(
		'<svg %1$s viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">%2$s</svg>',
		$wrapper_attributes,
		$trimmed_svg
	);
}
