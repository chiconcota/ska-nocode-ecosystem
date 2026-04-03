<?php
/**
 * Style Manager Class
 *
 * Handles global style settings and Tailwind configuration.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Class Style_Manager
 */
class Style_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_filter( 'ska_get_tailwind_config', array( $this, 'get_config' ) );
		add_action( 'ska_register_theme_config', array( $this, 'register_config' ) );
	}

	/**
	 * Scan post content for Tailwind classes used in Ska Blocks.
	 *
	 * @param int $post_id Post ID to scan.
	 * @return string Space-separated classes.
	 */
	public function scan_post_classes( $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		$blocks  = parse_blocks( $post->post_content );
		$classes = array();

		$this->extract_block_classes( $blocks, $classes );

		return implode( ' ', array_unique( array_filter( $classes ) ) );
	}

	/**
	 * Recursively extract classes from blocks.
	 */
	private function extract_block_classes( $blocks, &$classes ) {
		foreach ( $blocks as $block ) {
			// 1. Check for className or tailwindClasses attribute
			$block_tailwind_classes = $block['attrs']['tailwindClasses'] ?? $block['attrs']['className'] ?? '';
			if ( ! empty( $block_tailwind_classes ) ) {
				$block_classes = explode( ' ', $block_tailwind_classes );
				$classes       = array_merge( $classes, $block_classes );
			}

            // 2. Scan SPECIFIC attributes for Tailwind-like strings
            // This captures dynamic classes or custom responsive classes
            foreach ( $block['attrs'] as $key => $value ) {
                if ( is_string( $value ) && ! empty( $value ) ) {
                    // ONLY scan attributes that are known to contain Tailwind classes
                    // We skip 'content', 'url', etc. to avoid scanning full post text
                    if ( ! in_array( $key, array( 'className', 'tailwindClasses', 'content', 'logic', 'iconName', 'tagName' ) ) ) {
                        continue;
                    }

                    if ( $key === 'content' ) {
                        preg_match_all( '/class=["\']([^"\']+)["\']/', $value, $attr_matches );
                        if ( ! empty( $attr_matches[1] ) ) {
                            foreach ( $attr_matches[1] as $class_string ) {
                                $classes = array_merge( $classes, explode( ' ', $class_string ) );
                            }
                        }
                    } else {
                        $attr_values = explode( ' ', $value );
                        foreach ( $attr_values as $val ) {
                            if ( preg_match( '/^[a-z0-9:\[\]\/\-\.]+$/', $val ) ) {
                                $classes[] = $val;
                            }
                        }
                    }
                }
            }

            // 3. Block-specific whitelists (Ensure core layout classes are ALWAYS compiled)
            if ( strpos( $block['blockName'], 'ska-builder/' ) !== false ) {
                $classes = array_merge( $classes, array( 'relative', 'grid', 'flex', 'flex-col', 'gap-8' ) );
            }

            if ( strpos( $block['blockName'], 'ska-builder/video' ) !== false ) {
                $classes = array_merge( $classes, array( 'overflow-hidden', 'isolate', 'aspect-video', 'object-cover' ) );
            }

			// Recursive for inner blocks
			if ( ! empty( $block['innerBlocks'] ) ) {
				$this->extract_block_classes( $block['innerBlocks'], $classes );
			}
		}
	}

	/**
	 * Get Tailwind Config.
	 *
	 * @return array
	 */
	public function get_config(): array {
		return array(
			'theme' => array(
				'extend' => array(
					'colors' => self::get_theme_colors(),
				),
			),
		);
	}

	/**
	 * Register Theme Config.
	 *
	 * @param array $config Config array to merge.
	 */
	public function register_config( $config ) {
		// Placeholder logic to merge config.
	}

	/**
	 * Get global theme colors.
	 *
	 * @return array
	 */
	public static function get_theme_colors(): array {
		return array(
			'primary'   => '#3b82f6',
			'secondary' => '#ef4444',
		);
	}
}
