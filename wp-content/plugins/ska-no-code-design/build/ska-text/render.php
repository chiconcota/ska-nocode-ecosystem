<?php
/**
 * Render: Ska Text
 */
defined( 'ABSPATH' ) || exit;

$content = $content ?? '';
$tag     = $attributes['tagName'] ?? 'p';

// Use attributes content if available (for imported blocks)
if ( ! empty( $attributes['content'] ) ) {
    $content = \Ska\Builder\Utils\Dynamic_Data::resolve_inline_links( $attributes['content'] );
} else {
    $content = \Ska\Builder\Utils\Dynamic_Data::resolve_inline_links( $content );
}

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$link_url = '';
$link_target = '';
if ( ! empty( $attributes['link'] ) ) {
    $link_url = \Ska\Builder\Utils\Dynamic_Data::resolve_dynamic_link( $attributes['link'] );
    $link_target = ! empty( $attributes['link']['target'] ) ? ' target="' . esc_attr( $attributes['link']['target'] ) . '"' : '';
}

if ( ! empty( $link_url ) ) {
    $tag = 'a';
    if ( strpos( $user_tailwindClasses, 'block' ) === false && strpos( $user_tailwindClasses, 'flex' ) === false && strpos( $user_tailwindClasses, 'grid' ) === false && strpos( $user_tailwindClasses, 'hidden' ) === false && strpos( $user_tailwindClasses, 'inline-block' ) === false ) {
        $user_tailwindClasses = trim($user_tailwindClasses . ' block');
    }
}

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

$link_attrs = '';
if ( ! empty( $link_url ) ) {
    $link_attrs = ' href="' . esc_url( $link_url ) . '"' . $link_target;
}

printf(
    '<%1$s %2$s%4$s>%3$s</%1$s>',
    esc_attr( $tag ),
    $wrapper_attributes,
    $content,
    $link_attrs
);
