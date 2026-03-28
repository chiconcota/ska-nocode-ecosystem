<?php
/**
 * Data Provider Interface
 *
 * Contract for all data sources (Post, User, SCF, etc.)
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Provider
 */
interface Provider {

	/**
	 * Get the provider slug (e.g., 'post', 'user', 'scf').
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Get a field value from the provider.
	 *
	 * @param string $key     The field key (e.g., 'title', 'price').
	 * @param int    $id      Object ID.
	 * @param array  $context Full context array.
	 * @return mixed
	 */
	public function get_field( string $key, int $id, array $context );
}
