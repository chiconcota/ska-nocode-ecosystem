<?php
/**
 * Render: Ska Button
 */
defined( 'ABSPATH' ) || exit;

$text         = $attributes['text'] ?? '';
$url          = $attributes['url'] ?? '#';
$target       = $attributes['target'] ?? '_self';
$tagName      = $attributes['tagName'] ?? 'a';
$hasIcon      = ! empty( $attributes['hasIcon'] ) ? $attributes['hasIcon'] : false;
$iconName     = $attributes['iconName'] ?? '';
$iconPosition = $attributes['iconPosition'] ?? 'left';
$iconClasses  = $attributes['iconClasses'] ?? '';
$actionType   = $attributes['actionType'] ?? 'link';

// Force tagName based on actionType to guarantee semantic HTML (prevent <button href="..."> bugs)
if ( $actionType === 'link' ) {
    $tagName = 'a';
} else {
    $tagName = 'button';
}

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$wrapper_attrs_array = array(
    'class' => 'ska-button-block ' . esc_attr( $user_tailwindClasses ),
    'style' => $attributes['customStyle'] ?? '',
);

if ( ! empty( $attributes['ariaLabel'] ) ) {
    $wrapper_attrs_array['aria-label'] = esc_attr( $attributes['ariaLabel'] );
}

$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attrs_array );

if ( ! empty( $attributes['dynamic']['text_source'] ) && $attributes['dynamic']['text_source'] !== 'static' ) {
    $text = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'text' );
}
if ( ! empty( $attributes['dynamic']['url_source'] ) && $attributes['dynamic']['url_source'] !== 'static' ) {
    $url = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'url' );
}

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    $engine = \Ska\Builder\Logic\Core::instance();
    if ( ! $engine->should_render( $attributes['logic'] ) ) {
        return '';
    }
}

$icon_class_attr = trim( 'material-symbols-outlined ' . esc_attr( $iconClasses ) );

$inner_html = '';
if ( $hasIcon && $iconPosition === 'left' && ! empty( $iconName ) ) {
    $inner_html .= '<span class="' . $icon_class_attr . '" aria-hidden="true">' . esc_html( $iconName ) . '</span>';
}
$inner_html .= ( $hasIcon && $iconPosition === 'left' && ! empty( $iconName ) && ! empty( $text ) ) ? ' ' : '';
$inner_html .= esc_html( $text );
$inner_html .= ( $hasIcon && $iconPosition === 'right' && ! empty( $iconName ) && ! empty( $text ) ) ? ' ' : '';
if ( $hasIcon && $iconPosition === 'right' && ! empty( $iconName ) ) {
    $inner_html .= '<span class="' . $icon_class_attr . '" aria-hidden="true">' . esc_html( $iconName ) . '</span>';
}

if ( $tagName === 'button' ) {
    $type_attr = '';
    if ( $actionType === 'submit' ) {
        $type_attr = ' type="submit"';
    }
    printf(
        '<button%1$s %2$s>%3$s</button>',
        $type_attr,
        $wrapper_attributes,
        $inner_html
    );
} else {
    printf(
        '<a href="%1$s" target="%2$s" %3$s>%4$s</a>',
        esc_url( $url ),
        esc_attr( $target ),
        $wrapper_attributes,
        $inner_html
    );
}
