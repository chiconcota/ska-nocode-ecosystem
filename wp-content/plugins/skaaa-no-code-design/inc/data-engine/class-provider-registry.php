<?php
/**
 * Provider Registry
 *
 * Manages registration and retrieval of data providers.
 *
 * @package Skaaa_Builder_Core
 */

namespace Skaaa\Builder\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Class Provider_Registry
 */
class Provider_Registry {

	/**
	 * Registered providers.
	 *
	 * @var array
	 */
	private $providers = array();

	/**
	 * Register a provider.
	 *
	 * @param Provider $provider Provider instance.
	 */
	public function register( Provider $provider ) {
		$this->providers[ $provider->get_slug() ] = $provider;
	}

	/**
	 * Get a provider by slug.
	 *
	 * @param string $slug Provider slug.
	 * @return Provider|null
	 */
	public function get( string $slug ) {
		return isset( $this->providers[ $slug ] ) ? $this->providers[ $slug ] : null;
	}

	/**
	 * Get all providers.
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->providers;
	}
}
