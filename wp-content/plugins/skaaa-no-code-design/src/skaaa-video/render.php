<?php
/**
 * Render: Skaaa Video
 */
defined( 'ABSPATH' ) || exit;

$video_url = $attributes['url'] ?? '';
$video_type = $attributes['videoType'] ?? 'youtube';
$aspect_ratio = $attributes['aspectRatio'] ?? 'aspect-video';
$poster_url = $attributes['posterUrl'] ?? '';
$autoplay = ! empty( $attributes['autoplay'] ) ? ' autoplay muted loop playsinline' : '';
$controls = ! empty( $attributes['controls'] ) ? ' controls' : '';

// Convert embed URLs for YouTube/Vimeo
if ( $video_type === 'youtube' && ! empty( $video_url ) ) {
    if ( strpos( $video_url, 'youtu.be' ) !== false || strpos( $video_url, 'youtube.com' ) !== false ) {
        preg_match( '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/i', $video_url, $match );
        if ( isset( $match[2] ) && strlen( $match[2] ) === 11 ) {
            $video_url = 'https://www.youtube.com/embed/' . $match[2];
        }
    }
} elseif ( $video_type === 'vimeo' && ! empty( $video_url ) ) {
    if ( strpos( $video_url, 'vimeo.com' ) !== false ) {
        preg_match( '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i', $video_url, $match );
        if ( isset( $match[3] ) ) {
            $video_url = 'https://player.vimeo.com/video/' . $match[3];
        }
    }
}

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'skaaa-video-block overflow-hidden relative ' . esc_attr( $aspect_ratio ) . ' ' . esc_attr( $user_tailwindClasses ),
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    $engine = \Skaaa\Builder\Logic\Core::instance();
    if ( ! $engine->should_render( $attributes['logic'] ) ) {
        return '';
    }
}

$custom_style = ! empty( $attributes['customStyle'] ) ? ' style="' . esc_attr( $attributes['customStyle'] ) . '"' : '';

echo '<div ' . $wrapper_attributes . $custom_style . '>';
if ( ! empty( $video_url ) ) {
    if ( $video_type === 'youtube' || $video_type === 'vimeo' ) {
        // Embed iframe for YouTube/Vimeo
        printf(
            '<iframe class="w-full h-full absolute inset-0" src="%s" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>',
            esc_url( $video_url )
        );
    } else {
        // Local video file
        printf(
            '<video class="w-full h-full object-cover" %s %s %s><source src="%s" type="video/mp4"></video>',
            $autoplay,
            $controls,
            $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : '',
            esc_url( $video_url )
        );
    }
} else {
    echo '<div class="w-full h-full bg-gray-900 flex items-center justify-center text-white/50">Video Placeholder</div>';
}
echo '</div>';
