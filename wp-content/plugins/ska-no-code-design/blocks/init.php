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
    register_block_type( SKA_DESIGN_PATH . 'build/ska-form-rich-text' );

    // Ska Symbols (Organism Reference)
    register_block_type( SKA_DESIGN_PATH . 'build/ska-organism-ref' );

    // Ska Query Loop
    register_block_type( SKA_DESIGN_PATH . 'build/ska-loop' );

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
	$ska_categories = array(
		array(
			'slug'  => 'ska-atomic',
			'title' => __( 'Ska Atomic', 'ska-no-code-design' ),
			'icon'  => 'admin-customizer',
		),
		array(
			'slug'  => 'ska-molecules',
			'title' => __( 'Ska Molecules', 'ska-no-code-design' ),
			'icon'  => 'networking',
		),
	);

    // Graceful Fallback: Only show Organisms category if Ska Data Pro is active
    if ( class_exists( '\Ska_System_Framework\Dependency_Manager' ) && \Ska_System_Framework\Dependency_Manager::is_data_pro_active() ) {
        $ska_categories[] = array(
            'slug'  => 'ska-organisms',
            'title' => __( 'Ska Organisms', 'ska-no-code-design' ),
            'icon'  => 'superhero',
        );
    }

	return array_merge( $ska_categories, $categories );
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

        // Preload organisms cache into JS window object (Conditionally via Graceful Fallback)
        $organisms_data = array();
        
        // Only load if Ska Data Pro is active
        if ( class_exists( '\Ska_System_Framework\Dependency_Manager' ) && \Ska_System_Framework\Dependency_Manager::is_data_pro_active() ) {
            $organisms_cache_file = WP_CONTENT_DIR . '/uploads/ska-data/organisms.json';
            if ( file_exists( $organisms_cache_file ) ) {
                $json = file_get_contents( $organisms_cache_file );
                $data = json_decode( $json, true );
                if ( is_array( $data ) ) {
                    $organisms_data = $data;
                }
            }
        }
        
        wp_localize_script( 'ska-builder-extensions', 'skaOrganismsCache', $organisms_data );

        $ska_data_dict = get_option( 'ska_data_dictionary', array() );
        wp_localize_script( 'ska-builder-extensions', 'skaDataDictionary', $ska_data_dict );

        // Preload Design Tokens Cache into JS window object
        $tokens_data = array();
        $tokens_cache_file = WP_CONTENT_DIR . '/uploads/ska-data/tokens.json';
        if ( file_exists( $tokens_cache_file ) ) {
            $json = file_get_contents( $tokens_cache_file );
            $data = json_decode( $json, true );
            if ( is_array( $data ) ) {
                $tokens_data = $data;
            }
        }
        wp_localize_script( 'ska-builder-extensions', 'skaDesignTokens', $tokens_data );
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
        array( 'ska-frontend' ),
        '3.13.3',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ska_builder_core_register_alpine' );
add_action( 'admin_enqueue_scripts', 'ska_builder_core_register_alpine' );

/**
 * Register Ska Frontend Engine (Alpine Controller cho Form).
 * QUAN TRỌNG: Script này load TRƯỚC Alpine.js (không có dependency).
 * Lý do: Alpine tự auto-start khi load. Nếu ska-frontend load SAU Alpine,
 * thì alpine:init event đã bắn xong → skaForm không được đăng ký.
 * Thứ tự: ska-frontend.js → alpine.min.js → Alpine phát alpine:init → skaForm đăng ký.
 */
function ska_builder_core_register_frontend_engine() {
    wp_register_script(
        'ska-frontend',
        SKA_DESIGN_URL . 'assets/js/ska-frontend.js',
        array(),
        '1.0.1',
        true
    );

    // Truyền biến môi trường cho JS (REST URL)
    wp_localize_script( 'ska-frontend', 'skaEnv', array(
        'restUrl' => esc_url_raw( rest_url() ),
    ) );

    // Đảm bảo Alpine load SAU ska-frontend
    // Bằng cách set ska-frontend là dependency của ska-alpine
    // → Khi enqueue ska-alpine, nó sẽ kéo ska-frontend vào trước
}
add_action( 'wp_enqueue_scripts', 'ska_builder_core_register_frontend_engine' );
add_action( 'admin_enqueue_scripts', 'ska_builder_core_register_frontend_engine' );

/**
 * Render HTML Attributes & Alpine.js logic dynamically via PHP filter.
 * The zero-overhead Engine: only loads Alpine when x- attributes are used.
 */
function ska_builder_render_html_attributes( $block_content, $block ) {
    if ( ! empty( $block['blockName'] ) && strpos( $block['blockName'], 'ska-builder/' ) === 0 ) {
        $html_attrs = '';
        $has_alpine = false;
        $has_x_data = false;
        $has_x_show = false;
        
        if ( ! empty( $block['attrs']['htmlAttributes'] ) && is_array( $block['attrs']['htmlAttributes'] ) ) {
            foreach ( $block['attrs']['htmlAttributes'] as $attr ) {
                if ( ! empty( $attr['key'] ) ) {
                    if ( str_starts_with( $attr['key'], 'x-' ) || str_starts_with( $attr['key'], '@' ) ) {
                        $has_alpine = true;
                    }
                    if ( $attr['key'] === 'x-data' ) {
                        $has_x_data = true;
                    }
                    if ( $attr['key'] === 'x-show' ) {
                        $has_x_show = true;
                    }
                    $html_attrs .= ' ' . esc_attr( $attr['key'] ) . '="' . esc_attr( $attr['value'] ?? '' ) . '"';
                }
            }
        }
        
        // Portal Visibility logic removed.

        
        // Zero-overhead enqueuing
        if ( $has_alpine ) {
            wp_enqueue_script( 'ska-alpine' );
            if ( ! $has_x_data ) {
                // Auto-inject x-data for Alpine v3 compliance if missing
                $html_attrs .= ' x-data=""';
            }
        }

        // Inject into the very first HTML opening tag
        if ( ! empty( $html_attrs ) ) {
            $block_content = preg_replace( '/^<([a-zA-Z0-9\-]+)([^>]*?)>/', '<$1$2' . $html_attrs . '>', $block_content, 1 );
        }
    }
    return $block_content;
}
add_filter( 'render_block', 'ska_builder_render_html_attributes', 10, 2 );
