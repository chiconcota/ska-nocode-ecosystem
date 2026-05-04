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
                            if ( preg_match( '/^[a-zA-Z0-9:\[\]\/\-\._\(\),#%\\\]+$/', $val ) ) {
                                $classes[] = $val;
                            }
                        }
                    }

                    // Auto-inject Alpine.js default transition classes if x-transition is found in content
                    if ( $key === 'content' && strpos( $value, 'x-transition' ) !== false ) {
                        $classes = array_merge( $classes, array( 'transition', 'ease-out', 'duration-100', 'opacity-0', 'scale-90', 'opacity-100', 'scale-100', 'ease-in', 'duration-75' ) );
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

			// Recursive for Organism References
			if ( $block['blockName'] === 'ska-builder/organism-ref' && ! empty( $block['attrs']['organismId'] ) ) {
				$organism_id = $block['attrs']['organismId'];
				
				$upload_dir = wp_upload_dir();
				$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/organisms.json';
				
				$organisms = array();
				if ( class_exists( '\Ska_System_Framework\System_Cache' ) ) {
					$organisms = \Ska_System_Framework\System_Cache::get_instance()->get_system_data( 'organisms' );
				} elseif ( file_exists( $cache_file ) ) {
					$file_contents = file_get_contents( $cache_file );
					if ( ! empty( $file_contents ) ) {
						$decoded = json_decode( $file_contents, true );
						if ( is_array( $decoded ) ) {
							$organisms = $decoded;
						}
					}
				}

				if ( is_array( $organisms ) ) {
					foreach ( $organisms as $org ) {
						if ( isset( $org['id'] ) && (string) $org['id'] === (string) $organism_id && ! empty( $org['html_content'] ) ) {
							$ref_blocks = parse_blocks( $org['html_content'] );
							$this->extract_block_classes( $ref_blocks, $classes );
							break;
						}
					}
				}
			}

			// Recursive for Ska Loop Slots
			if ( $block['blockName'] === 'ska-builder/loop' && ! empty( $block['attrs']['slots'] ) && is_array( $block['attrs']['slots'] ) ) {
				$upload_dir = wp_upload_dir();
				$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/organisms.json';
				
				$organisms = array();
				if ( class_exists( '\Ska_System_Framework\System_Cache' ) ) {
					$organisms = \Ska_System_Framework\System_Cache::get_instance()->get_system_data( 'organisms' );
				} elseif ( file_exists( $cache_file ) ) {
					$file_contents = file_get_contents( $cache_file );
					if ( ! empty( $file_contents ) ) {
						$decoded = json_decode( $file_contents, true );
						if ( is_array( $decoded ) ) {
							$organisms = $decoded;
						}
					}
				}

				if ( is_array( $organisms ) ) {
					foreach ( $block['attrs']['slots'] as $slot ) {
						if ( ! empty( $slot['organismId'] ) ) {
							foreach ( $organisms as $org ) {
								if ( isset( $org['id'] ) && (string) $org['id'] === (string) $slot['organismId'] && ! empty( $org['html_content'] ) ) {
									$ref_blocks = parse_blocks( $org['html_content'] );
									$this->extract_block_classes( $ref_blocks, $classes );
									break;
								}
							}
						}
					}
				}
			}

			// Extract from HTML Attributes (Support for Alpine.js x-transition classes)
			if ( ! empty( $block['attrs']['htmlAttributes'] ) && is_array( $block['attrs']['htmlAttributes'] ) ) {
				foreach ( $block['attrs']['htmlAttributes'] as $attr ) {
					if ( ! empty( $attr['value'] ) && is_string( $attr['value'] ) ) {
						// Normalize whitespace to spaces to correctly handle newlines or tabs from TextareaControl
						$clean_value = preg_replace( '/\s+/', ' ', $attr['value'] );
						$attr_values = explode( ' ', trim( $clean_value ) );
						foreach ( $attr_values as $val ) {
							if ( preg_match( '/^[a-zA-Z0-9:\[\]\/\-\._\(\),#%\\\]+$/', $val ) ) {
								$classes[] = $val;
							}
						}
					}
					
					// Auto-inject Alpine.js default transition classes if x-transition is used without specific tailwind classes
					if ( isset( $attr['key'] ) && strpos( $attr['key'], 'x-transition' ) !== false ) {
						$classes = array_merge( $classes, array( 'transition', 'ease-out', 'duration-100', 'opacity-0', 'scale-90', 'opacity-100', 'scale-100', 'ease-in', 'duration-75' ) );
					}
				}
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
