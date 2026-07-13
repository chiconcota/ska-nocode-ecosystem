<?php
/**
 * WP Post Provider
 *
 * Handles data retrieval for standard WordPress Posts.
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Data\Providers;

use Skaaa\Builder\Data\Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_Post_Provider
 */
class WP_Post_Provider implements Provider {

	/**
	 * Get provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'post';
	}

	/**
	 * Get field value.
	 *
	 * @param string $key     Field key.
	 * @param int    $id      Post ID.
	 * @param array  $context Context.
	 * @return mixed
	 */
	public function get_field( string $key, int $id, array $context ) {
		$post = get_post( $id );

		if ( ! $post ) {
			return '';
		}

		switch ( $key ) {
			case 'title':
			case 'post_title':
				return get_the_title( $post );
			
			case 'content':
			case 'post_content':
				return apply_filters( 'the_content', $post->post_content );
			
			case 'excerpt':
			case 'post_excerpt':
				return get_the_excerpt( $post ); // Using WP core function handles auto-excerpt.

			case 'permalink':
			case 'url':
				return get_permalink( $post );

			case 'date':
			case 'post_date':
				return get_the_date( '', $post );

			case 'thumbnail':
			case 'featured_image':
				return get_the_post_thumbnail_url( $post, 'full' );

			case 'thumbnail_id':
				return get_post_thumbnail_id( $post );
			
			case 'id':
			case 'ID':
				return $post->ID;

			default:
				// Fallback to post meta if not a core field.
				return get_post_meta( $id, $key, true );
		}
	}
}
