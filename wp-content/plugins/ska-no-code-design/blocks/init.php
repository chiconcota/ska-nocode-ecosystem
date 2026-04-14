<?php
/**
 * Blocks initialization.
 * 
 * @package Ska_Builder_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register blocks from the build directory.
 */
function ska_builder_core_register_blocks() {
    // Register from build directory (where metadata is copied/built)
    register_block_type( SKA_DESIGN_PATH . 'build/ska-container' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-text' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-image' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-icon' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-button' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-video' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-list' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-list-item' );
    
    // Form Interface Blocks
    register_block_type( SKA_DESIGN_PATH . 'build/ska-input' );
    register_block_type( SKA_DESIGN_PATH . 'build/ska-select' );

    // Register Bridge Import if enabled
    if ( get_option( 'ska_bridge_enabled', 'yes' ) === 'yes' ) {
        register_block_type( SKA_DESIGN_PATH . 'build/ska-bridge-import' );
    }
}

add_action( 'init', 'ska_builder_core_register_blocks' );

/**
 * Register custom block category for Ska Atomic blocks.
 */
function ska_no_code_design_block_categories( $categories, $post ) {
	return array_merge(
		array(
			array(
				'slug'  => 'ska-atomic',
				'title' => __( 'Ska Atomic', 'ska-no-code-design' ),
				'icon'  => 'admin-customizer',
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'ska_no_code_design_block_categories', 10, 2 );

/**
 * Enqueue editor extensions (HTML Attributes Panel)
 */
function ska_builder_core_enqueue_extensions() {
    $asset_path = SKA_DESIGN_PATH . 'build/extensions.asset.php';
    if ( file_exists( $asset_path ) ) {
        $assets = require $asset_path;
        wp_enqueue_script(
            'ska-builder-extensions',
            SKA_DESIGN_URL . 'build/extensions.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );
    }
}
add_action( 'enqueue_block_editor_assets', 'ska_builder_core_enqueue_extensions' );

/**
 * Register Alpine.js script (Local file)
 */
function ska_builder_core_register_alpine() {
    wp_register_script(
        'ska-alpine',
        SKA_DESIGN_URL . 'assets/js/alpine.min.js',
        array(),
        '3.13.3',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ska_builder_core_register_alpine' );
add_action( 'admin_enqueue_scripts', 'ska_builder_core_register_alpine' );

/**
 * Render HTML Attributes & Alpine.js logic dynamically via PHP filter.
 * The zero-overhead Engine: only loads Alpine when x- attributes are used.
 */
function ska_builder_render_html_attributes( $block_content, $block ) {
    if ( strpos( $block['blockName'], 'ska-builder/' ) === 0 && ! empty( $block['attrs']['htmlAttributes'] ) && is_array( $block['attrs']['htmlAttributes'] ) ) {
        $html_attrs = '';
        $has_alpine = false;
        
        foreach ( $block['attrs']['htmlAttributes'] as $attr ) {
            if ( ! empty( $attr['key'] ) ) {
                if ( str_starts_with( $attr['key'], 'x-' ) || str_starts_with( $attr['key'], '@' ) ) {
                    $has_alpine = true;
                }
                $html_attrs .= ' ' . esc_attr( $attr['key'] ) . '="' . esc_attr( $attr['value'] ?? '' ) . '"';
            }
        }
        
        // Zero-overhead enqueuing
        if ( $has_alpine ) {
            wp_enqueue_script( 'ska-alpine' );
        }

        // Inject into the very first HTML opening tag
        if ( ! empty( $html_attrs ) ) {
            $block_content = preg_replace( '/^<([a-zA-Z0-9\-]+)([^>]*?)>/', '<$1$2' . $html_attrs . '>', $block_content, 1 );
        }
    }
    return $block_content;
}
add_filter( 'render_block', 'ska_builder_render_html_attributes', 10, 2 );
