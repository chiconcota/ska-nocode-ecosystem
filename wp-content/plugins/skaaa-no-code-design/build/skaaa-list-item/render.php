<?php
/**
 * Render: Skaaa List Item
 */
defined( 'ABSPATH' ) || exit;

$content = $content ?? '';
$content = \Skaaa\Builder\Utils\Dynamic_Data::resolve_inline_links( $content );

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'skaaa-list-item-block ' . esc_attr( $user_tailwindClasses ),
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    if ( class_exists( '\Skaaa\Builder\Logic\Core' ) ) {
        $engine = \Skaaa\Builder\Logic\Core::instance();
        if ( ! $engine->should_render( $attributes['logic'] ) ) {
            return '';
        }
    }
}

printf(
    '<li %1$s>%2$s</li>',
    $wrapper_attributes,
    $content
);
