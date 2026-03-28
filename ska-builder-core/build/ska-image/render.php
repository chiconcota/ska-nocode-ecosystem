<?php
/**
 * Render: Ska Image
 */
defined( 'ABSPATH' ) || exit;

$image_id = $attributes['imageId'] ?? $attributes['id'] ?? 0;
$image_url = $attributes['imageUrl'] ?? $attributes['url'] ?? '';
$aspect_ratio = $attributes['aspectRatio'] ?? 'aspect-square';
$object_fit = $attributes['objectFit'] ?? 'object-cover';

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

// Determine final classes for both the wrapper and the image
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'ska-image-block overflow-hidden relative ' . esc_attr( $aspect_ratio ) . ' ' . esc_attr( $user_tailwindClasses ),
) );

$img_attributes = sprintf(
    'class="w-full h-full %s"',
    esc_attr( $object_fit )
);

$src = $image_url ? ' src="' . esc_url( $image_url ) . '"' : '';
$image_alt = $attributes['alt'] ?? '';
if ( ! $image_alt && $image_id ) {
    $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
}
$alt = ' alt="' . esc_attr( $image_alt ) . '"';

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    $engine = \Ska\Builder\Logic\Core::instance();
    if ( ! $engine->should_render( $attributes['logic'] ) ) {
        return '';
    }
}

// Render dynamic content if needed
if ( ! empty( $attributes['dynamic']['source'] ) && $attributes['dynamic']['source'] !== 'static' ) {
    $image_url = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'url' );
    $src = ' src="' . esc_url( $image_url ) . '"';
}

$custom_style = ! empty( $attributes['customStyle'] ) ? ' style="' . esc_attr( $attributes['customStyle'] ) . '"' : '';

// 5. Render HTML
echo '<div ' . $wrapper_attributes . $custom_style . '>';
if ( ! empty( $image_url ) ) {
    echo     '<img ' . $img_attributes . $src . $alt . ' />';
} else {
    echo     '<div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">Icon Image</div>';
}
echo '</div>';
