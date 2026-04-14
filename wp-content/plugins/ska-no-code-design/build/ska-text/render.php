<?php
/**
 * Render: Ska Text
 */
defined( 'ABSPATH' ) || exit;

$content = $content ?? '';
$tag     = $attributes['tagName'] ?? 'p';

// Use attributes content if available (for imported blocks)
if ( ! empty( $attributes['content'] ) ) {
    $content = $attributes['content'];
}

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'ska-text-block ' . esc_attr( $user_tailwindClasses ),
) );

// Handle Dynamic Content
if ( ! empty( $attributes['dynamic']['source'] ) && $attributes['dynamic']['source'] !== 'text' ) {
    $content = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'text' );
}

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    $engine = \Ska\Builder\Logic\Core::instance();
    if ( ! $engine->should_render( $attributes['logic'] ) ) {
        return '';
    }
}

printf(
    '<%1$s %2$s>%3$s</%1$s>',
    esc_attr( $tag ),
    $wrapper_attributes,
    $content
);
