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
    register_block_type( SKA_CORE_PATH . 'build/ska-container' );
    register_block_type( SKA_CORE_PATH . 'build/ska-text' );
    register_block_type( SKA_CORE_PATH . 'build/ska-image' );
    register_block_type( SKA_CORE_PATH . 'build/ska-icon' );
    register_block_type( SKA_CORE_PATH . 'build/ska-button' );
    register_block_type( SKA_CORE_PATH . 'build/ska-video' );
    register_block_type( SKA_CORE_PATH . 'build/ska-list' );
    register_block_type( SKA_CORE_PATH . 'build/ska-list-item' );

    // Register Bridge Import if enabled
    if ( get_option( 'ska_bridge_enabled', 'yes' ) === 'yes' ) {
        register_block_type( SKA_CORE_PATH . 'build/ska-bridge-import' );
    }
}

add_action( 'init', 'ska_builder_core_register_blocks' );
