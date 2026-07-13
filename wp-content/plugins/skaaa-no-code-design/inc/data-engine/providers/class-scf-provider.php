<?php
/**
 * SCF (Secure Custom Fields) Provider
 *
 * Handles data retrieval for SCF/ACF fields.
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Data\Providers;

use Skaaa\Builder\Data\Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Class SCF_Provider
 */
class SCF_Provider implements Provider {

	/**
	 * Get provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'scf';
	}

	/**
	 * Get field value.
	 *
	 * @param string $key     Field key.
	 * @param int    $id      Object ID.
	 * @param array  $context Context.
	 * @return mixed
	 */
	public function get_field( string $key, int $id, array $context ) {
		// Ensure SCF/ACF function exists.
		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}

		// Determine the Object ID format for SCF.
		// SCF uses specific ID formats for non-post objects:
		// - Term: "term_{term_id}" or "category_{term_id}"
		// - User: "user_{user_id}"
		// - Option: "option"
		// - Comment: "comment_{comment_id}"
		
		$scf_id = $id;

		if ( isset( $context['type'] ) ) {
			switch ( $context['type'] ) {
				case 'term':
				case 'taxonomy':
					$scf_id = 'term_' . $id;
					break;
				case 'user':
					$scf_id = 'user_' . $id;
					break;
				case 'option':
				case 'options':
					$scf_id = 'option';
					break;
				case 'comment':
					$scf_id = 'comment_' . $id;
					break;
				case 'post':
				default:
					$scf_id = $id;
					break;
			}
		}

		// Fetch field value via SCF/ACF.
		// Note: get_field handles formatting by default.
		// We might want to use get_field_object for more details later, 
		// but for simple binding, get_field is sufficient.
		$value = get_field( $key, $scf_id );

		if ( null === $value ) {
			return '';
		}

		// Handle array/object returns (e.g., Image object, Relationship).
		// For simple text binding, we need a string.
		if ( is_array( $value ) ) {
			// If it's an image object, return URL if possible, or ID.
			if ( isset( $value['url'] ) ) {
				return $value['url'];
			}
			// If it's simple array of values, join them.
			return implode( ', ', $value );
		}

		if ( is_object( $value ) ) {
			// Try string conversion if available
			if ( method_exists( $value, '__toString' ) ) {
				return (string) $value;
			}
			return ''; // Cannot render object directly.
		}

		return $value;
	}
}
