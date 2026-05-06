<?php
/**
 * Render: Ska Icon
 */
defined( 'ABSPATH' ) || exit;

$icon_name = $attributes['iconName'] ?? 'star';

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class'       => 'ska-icon-block material-symbols-outlined ' . esc_attr( $user_tailwindClasses ),
    'style'       => $attributes['customStyle'] ?? '',
    'aria-hidden' => 'true',
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    if ( class_exists( '\Ska\Builder\Logic\Core' ) ) {
        $engine = \Ska\Builder\Logic\Core::instance();
        if ( ! $engine->should_render( $attributes['logic'] ) ) {
            return '';
        }
    }
}

printf(
    '<span %1$s>%2$s</span>',
    $wrapper_attributes,
    esc_html( $icon_name )
);
