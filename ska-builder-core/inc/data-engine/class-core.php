<?php
/**
 * Data Engine Core Class
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Data;

use Ska\Builder\Data\Providers\WP_Post_Provider;
use Ska\Builder\Data\Providers\SCF_Provider;
use Ska\Builder\Data\Providers\Term_Provider;
use Ska\Builder\Data\Providers\User_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Class Core
 */
class Core {

	/**
	 * Instance of this class.
	 *
	 * @var Core
	 */
	protected static $instance = null;

	/**
	 * Context Manager instance.
	 *
	 * @var Context_Manager
	 */
	public $context_manager;

	/**
	 * Provider Registry instance.
	 *
	 * @var Provider_Registry
	 */
	public $registry;

	/**
	 * Return an instance of this class.
	 *
	 * @return Core A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Core constructor.
	 */
	private function __construct() {
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Initialize components.
	 */
	private function init_components() {
		$this->context_manager = new Context_Manager();
		$this->registry        = new Provider_Registry();

		// Register default providers.
		$this->registry->register( new WP_Post_Provider() );
		$this->registry->register( new SCF_Provider() );
		$this->registry->register( new Term_Provider() );
		$this->registry->register( new User_Provider() );
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_filter( 'ska_bind_data', array( $this, 'bind_data' ), 10, 2 );
		add_filter( 'ska_get_field', array( $this, 'get_field_value' ), 10, 3 );
	}

	/**
	 * Bind data to a string with placeholders.
	 * Format: {{provider:key}} or {{key}} (defaults to post).
	 *
	 * @param string $content String containing placeholders.
	 * @param array  $context Optional context override.
	 * @return string Processed string.
	 */
	public function bind_data( $content, $context = array() ) {
		if ( empty( $content ) || strpos( $content, '{{' ) === false ) {
			return $content;
		}

		// Use current context if not provided.
		if ( empty( $context ) ) {
			$context = $this->context_manager->get_current();
		}

		// Regex to find {{...}}.
		return preg_replace_callback( '/\{\{(.*?)\}\}/', function ( $matches ) use ( $context ) {
			$tag = trim( $matches[1] );
			
			// Parse tag: provider:key or just key.
			if ( strpos( $tag, ':' ) !== false ) {
				list( $provider_slug, $key ) = explode( ':', $tag, 2 );
			} else {
				$provider_slug = 'post'; // Default provider.
				$key           = $tag;
			}

			// Handle SCF short syntax if needed, e.g., {{scf:field_name}}.
			if ( 'scf' === $provider_slug || 'acf' === $provider_slug ) {
				// We haven't implemented SCF provider yet, but logic would be similar.
				// For now fallback to post meta.
				$provider_slug = 'post';
			}

			return $this->get_field_value( null, $key, $provider_slug );

		}, $content );
	}

	/**
	 * Get field value from a specific provider.
	 *
	 * @param mixed  $default       Default value (filter standard).
	 * @param string $key           Field key.
	 * @param string $provider_slug Provider slug (default 'post').
	 * @return mixed
	 */
	public function get_field_value( $default, $key, $provider_slug = 'post' ) {
		$provider = $this->registry->get( $provider_slug );
		$context  = $this->context_manager->get_current();

		if ( $provider && $context ) {
			// Check if context type matches provider (e.g., trying to get user data from post context).
			// For simplicity, we assume the ID in context is valid for the provider unless specified otherwise.
			// Complex logic: If provider is 'user' but context is 'post', we might look for 'post_author'.
			// For now, simple direct fetch.
			return $provider->get_field( $key, $context['id'], $context );
		}

		return $default;
	}
}
