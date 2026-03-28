<?php
/**
 * Data Engine Helper Functions
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Bind data to a content string.
 *
 * @param string $content Content with placeholders.
 * @param array  $context Optional context.
 * @return string
 */
function bind( $content, $context = array() ) {
	return apply_filters( 'ska_bind_data', $content, $context );
}

/**
 * Get field value directly.
 *
 * @param string $key Field key.
 * @param string $provider Provider slug.
 * @return mixed
 */
function get_field( $key, $provider = 'post' ) {
	if ( class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
		return \Ska\Builder\Data\Core::instance()->get_field_value( null, $key, $provider );
	}
	return null;
}
