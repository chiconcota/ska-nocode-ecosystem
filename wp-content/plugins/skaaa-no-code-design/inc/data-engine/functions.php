<?php
/**
 * Data Engine Helper Functions
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Bind data to a content string.
 *
 * @param string $content Content with placeholders.
 * @param array  $context Optional context.
 * @return string
 */
function bind( $content, $context = array() ) {
	return apply_filters( 'skaaa_bind_data', $content, $context );
}

/**
 * Get field value directly.
 *
 * @param string $key Field key.
 * @param string $provider Provider slug.
 * @return mixed
 */
function get_field( $key, $provider = 'post' ) {
	if ( class_exists( 'Skaaa\\Builder\\Data\\Core' ) ) {
		return \Skaaa\Builder\Data\Core::instance()->get_field_value( null, $key, $provider );
	}
	return null;
}
