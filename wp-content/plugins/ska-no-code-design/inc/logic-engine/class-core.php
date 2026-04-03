<?php
/**
 * Logic Engine Core Class
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Logic;

use Ska\Builder\Data\Core as Data_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Core
 */
class Core {

	/**
	 * Instance.
	 *
	 * @var Core
	 */
	protected static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Core
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Kích hoạt Cỗ máy biên dịch nội dung tự động trên Frontend (Độ ưu tiên cực thấp 90 để chạy sau Gutenberg blocks)
		if ( ! is_admin() ) {
			add_filter( 'the_content', array( $this, 'compile' ), 90 );
		}
	}

	/**
	 * Compile content with logic and data.
	 *
	 * @param string $content Input string with logic tags.
	 * @param array  $context Optional data context.
	 * @return string Compiled string.
	 */
	public function compile( $content, $context = array() ) {
		if ( empty( $content ) ) {
			return '';
		}

		// Pattern: {{#tag args}}...{{/tag}}
		// Note: This regex does NOT handle nested tags of the SAME type correctly (e.g. if inside if).
		// For nested structures, we'd need a parser. For MVP/Prompt constraints, proceed with simple regex.
		
		$pattern = '/\{\{#(if|foreach)\s+(.*?)\}\}([\s\S]*?)\{\{\/\1\}\}/';
		
		// Use callback for processing
		$processed = preg_replace_callback( $pattern, function( $matches ) use ( $context ) {
			$tag       = $matches[1];
			$args      = trim( $matches[2] );
			$inner     = $matches[3];

			if ( 'if' === $tag ) {
				return $this->handle_if( $args, $inner, $context );
			} elseif ( 'foreach' === $tag ) {
				return $this->handle_foreach( $args, $inner, $context );
			}
			return '';
		}, $content );
		
		// If regex replaced nothing, just bind data.
		if ( null === $processed ) {
			$processed = $content; 
		}

		// Bind Data Variables (Leaf nodes)
		if ( class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
			return Data_Core::instance()->bind_data( $processed, $context );
		}

		return $processed;
	}

	/**
	 * Handle IF block.
	 */
	private function handle_if( $condition, $inner, $context ) {
		$value = false;
		if ( class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
			// Extract key from condition "provider:key" or "key"
			$parts = explode( ':', $condition, 2 );
			$provider = ( count( $parts ) > 1 ) ? $parts[0] : 'post';
			$key      = ( count( $parts ) > 1 ) ? $parts[1] : $parts[0];

			// Fetch value
			$value = Data_Core::instance()->get_field_value( null, $key, $provider );
		}

		if ( ! empty( $value ) ) {
			// Recurse
			return $this->compile( $inner, $context );
		}
		return '';
	}

	/**
	 * Handle FOREACH block.
	 */
	private function handle_foreach( $expression, $inner, $context ) {
		// "item in items" -> "items"
		$parts = explode( ' in ', $expression );
		$source_key = end( $parts );
		
		$items = array();
		if ( class_exists( 'Ska\\Builder\\Data\\Core' ) ) {
			// Resolve items
			$key_parts = explode( ':', trim( $source_key ), 2 );
			$provider  = ( count( $key_parts ) > 1 ) ? $key_parts[0] : 'post';
			$key       = ( count( $key_parts ) > 1 ) ? $key_parts[1] : $key_parts[0];
			
			$items = Data_Core::instance()->get_field_value( array(), $key, $provider );
		}

		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}

		$output = '';
		$data_manager = Data_Core::instance()->context_manager;

		foreach ( $items as $item ) {
			$item_id   = 0;
			$item_type = 'post';

			if ( is_numeric( $item ) ) {
				$item_id = $item;
			} elseif ( is_a( $item, 'WP_Post' ) ) {
				$item_id = $item->ID;
			} elseif ( is_a( $item, 'WP_User' ) ) {
				$item_id   = $item->ID;
				$item_type = 'user';
			} elseif ( is_a( $item, 'WP_Term' ) ) {
				$item_id   = $item->term_id;
				$item_type = 'term';
			}

			if ( $item_id ) {
				$data_manager->push_context( $item_id, $item_type );
				// Recurse compile inner content
				$output .= $this->compile( $inner, $context ); 
				$data_manager->pop_context();
			}
		}

		return $output;
	}
}
