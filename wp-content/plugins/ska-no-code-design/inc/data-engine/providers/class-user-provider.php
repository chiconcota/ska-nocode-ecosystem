<?php
/**
 * User Provider
 *
 * Handles data retrieval for Users.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data\Providers;

use Ska\Builder\Data\Provider;
use WP_User;

defined( 'ABSPATH' ) || exit;

/**
 * Class User_Provider
 */
class User_Provider implements Provider {

	/**
	 * Get provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'user';
	}

	/**
	 * Get field value.
	 *
	 * @param string $key     Field key.
	 * @param int    $id      User ID.
	 * @param array  $context Context.
	 * @return mixed
	 */
	public function get_field( string $key, int $id, array $context ) {
		$user = get_userdata( $id );

		if ( ! $user ) {
			return '';
		}

		switch ( $key ) {
			case 'display_name':
			case 'name':
				return $user->display_name;

			case 'email':
			case 'user_email':
				return $user->user_email;

			case 'login':
			case 'user_login':
				return $user->user_login;

			case 'id':
			case 'ID':
				return $user->ID;

			case 'url':
			case 'user_url':
			case 'website':
				return $user->user_url;

			case 'avatar':
				return get_avatar_url( $id );

			case 'description':
			case 'bio':
				return get_user_meta( $id, 'description', true );

			case 'first_name':
				return $user->first_name;

			case 'last_name':
				return $user->last_name;

			case 'nicename':
				return $user->user_nicename; // Slug-like name

			case 'posts_url':
				return get_author_posts_url( $id );
			
			default:
				// Fallback to user meta.
				return get_user_meta( $id, $key, true );
		}
	}
}
