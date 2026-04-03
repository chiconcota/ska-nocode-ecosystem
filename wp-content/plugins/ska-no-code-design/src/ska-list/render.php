<?php
/**
 * Render: Ska List
 */
defined( 'ABSPATH' ) || exit;

$list_type = $attributes['listType'] ?? 'ul';
$content   = $content ?? '';

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'ska-list-block ' . esc_attr( $user_tailwindClasses ),
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
    '<%1$s %2$s>%3$s</%1$s>',
    esc_attr( $list_type ),
    $wrapper_attributes,
    $content
);
