<?php
/**
 * Term Provider
 *
 * Handles data retrieval for Taxonomy Terms.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data\Providers;

use Ska\Builder\Data\Provider;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Class Term_Provider
 */
class Term_Provider implements Provider {

	/**
	 * Get provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'term';
	}

	/**
	 * Get field value.
	 *
	 * @param string $key     Field key.
	 * @param int    $id      Term ID.
	 * @param array  $context Context.
	 * @return mixed
	 */
	public function get_field( string $key, int $id, array $context ) {
		$term = get_term( $id );

		if ( ! $term || is_wp_error( $term ) ) {
			return '';
		}

		switch ( $key ) {
			case 'name':
			case 'title':
				return $term->name;

			case 'slug':
				return $term->slug;

			case 'description':
			case 'desc':
				return term_description( $id );

			case 'count':
				return $term->count;

			case 'url':
			case 'permalink':
			case 'link':
				return get_term_link( $term );

			case 'id':
			case 'ID':
				return $term->term_id;

			case 'taxonomy':
				return $term->taxonomy;

			case 'parent':
				return $term->parent;
			
			default:
				// Fallback to term meta.
				return get_term_meta( $id, $key, true );
		}
	}
}
