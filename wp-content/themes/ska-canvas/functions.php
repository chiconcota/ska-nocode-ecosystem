<?php
/**
 * Ska Canvas Theme Functions
 * 
 * @package SkaCanvas
 * @version 1.0.0
 * 
 * ⚠️ RULES OF SKA CANVAS:
 * This theme must remain absolutely clean. 
 * Its sole purpose is to remove WordPress bloat and provide a blank slate.
 * ALL visual features must come from Ska Plugins.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * 1. THEME SUPPORT SETUP
 * Turn on what we need (editor styles), turn off everything else that causes clutter.
 */
function ska_canvas_setup() {
	// Enable support for Editor Styles
	add_theme_support( 'editor-styles' );
	
	// Add Editor-only CSS to fix "hugging the edges" UI
	add_editor_style( 'editor-style.css' );
	// Disable Core Block Patterns (We don't want WP suggesting default layouts)
	remove_theme_support( 'core-block-patterns' );
	
	// Add support for full and wide align images/blocks
	add_theme_support( 'align-wide' );

	// Let WP handle Title Tags
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'ska_canvas_setup' );

/**
 * 2. THE PURGE (FRONTEND ASSETS CLEANUP)
 * Remove all default WordPress CSS to ensure 100% Tailwind control.
 */
function ska_canvas_dequeue_styles() {
	// Remove Gutenberg Default CSS
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-blocks-style' ); // WooCommerce Gutenberg blocks

	// Remove Global Styles (theme.json inline CSS)
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'wp-global-styles' ); // WP 6.2+ fallback
	wp_dequeue_style( 'core-block-supports' ); // WP Block supports like align/margin

	// Remove Classic Theme Styles
	wp_dequeue_style( 'classic-theme-styles' );
}
// We use priority 100 to make sure we dequeue AFTER they are enqueued by WP Core.
add_action( 'wp_enqueue_scripts', 'ska_canvas_dequeue_styles', 100 );

/**
 * 3. DISABLE EMOJIS (Performance Optimization)
 */
function ska_canvas_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	
	// Remove from TinyMCE
	add_filter( 'tiny_mce_plugins', 'ska_canvas_disable_emojis_tinymce' );
}
add_action( 'init', 'ska_canvas_disable_emojis' );

function ska_canvas_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/**
 * 4. DISABLE O-EMBED SCRIPTS
 */
function ska_canvas_deregister_scripts() {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'ska_canvas_deregister_scripts' );

/**
 * 5. REMOVE SVG FILTERS & GLOBAL STYLES INLINE CSS
 */
function ska_canvas_remove_global_styles_and_svg() {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
}
add_action( 'after_setup_theme', 'ska_canvas_remove_global_styles_and_svg', 10 );
add_action( 'init', 'ska_canvas_remove_global_styles_and_svg', 10 ); // Double tap just in case

/**
 * 6. HIDE SITE EDITOR (FSE) MENU
 * Prevent users from accessing the empty Site Editor screen.
 */
function ska_canvas_hide_site_editor() {
	remove_submenu_page( 'themes.php', 'site-editor.php' ); // Removes "Editor" under Appearance
}
add_action( 'admin_menu', 'ska_canvas_hide_site_editor', 999 );
