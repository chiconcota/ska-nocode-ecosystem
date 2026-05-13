<?php
/**
 * Render: Ska Button
 */
defined( 'ABSPATH' ) || exit;

$text         = $attributes['text'] ?? '';
$url          = '#';
$target       = '_self';

if ( ! empty( $attributes['link'] ) && ( ! empty( $attributes['link']['url'] ) || ( ! empty( $attributes['link']['dynamic']['source'] ) && $attributes['link']['dynamic']['source'] !== 'static' ) ) ) {
    $url    = \Ska\Builder\Utils\Dynamic_Data::resolve_dynamic_link( $attributes['link'] ) ?: '#';
    $target = ! empty( $attributes['link']['target'] ) ? $attributes['link']['target'] : '_self';
} else {
    $url    = $attributes['url'] ?? '#';
    $target = $attributes['target'] ?? '_self';
    if ( ! empty( $attributes['dynamic']['url_source'] ) && $attributes['dynamic']['url_source'] !== 'static' ) {
        // Fallback dynamic resolve
        $url = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'url' );
    }
}
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

$alpine_attrs = '';
if ( $actionType === 'logic_api' ) {
    $workflow_id = $attributes['fieldName'] ?? '';
    if ( ! empty( $workflow_id ) ) {
        $user_tailwindClasses .= ' ska-action-' . sanitize_html_class( $workflow_id );
        // The event will be intercepted globally by ska-core.js (window.$ska.submitForm)
    }
} elseif ( $actionType === 'theme_toggle' ) {
    $alpine_attrs .= ' @click.prevent="$store.skaTheme.toggle()"';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'ska-button-block ' . esc_attr( trim( $user_tailwindClasses ) ),
) );

if ( ! empty( $attributes['dynamic']['text_source'] ) && $attributes['dynamic']['text_source'] !== 'static' ) {
    $text = \Ska\Builder\Utils\Assets::get_dynamic_content( $attributes['dynamic'], 'text' );
}
// URL resolving is now handled at the top of the file.

if ( ! empty( $attributes['logic']['enabled'] ) ) {
    $engine = \Ska\Builder\Logic\Core::instance();
    if ( ! $engine->should_render( $attributes['logic'] ) ) {
        return '';
    }
}

$icon_class_attr = trim( 'material-symbols-outlined ' . esc_attr( $iconClasses ) );

$inner_html = '';
if ( $hasIcon && $iconPosition === 'left' && ! empty( $iconName ) ) {
    $inner_html .= '<span class="' . $icon_class_attr . '">' . esc_html( $iconName ) . '</span>';
}
$inner_html .= ( $hasIcon && $iconPosition === 'left' && ! empty( $iconName ) && ! empty( $text ) ) ? ' ' : '';
$inner_html .= esc_html( $text );
$inner_html .= ( $hasIcon && $iconPosition === 'right' && ! empty( $iconName ) && ! empty( $text ) ) ? ' ' : '';
if ( $hasIcon && $iconPosition === 'right' && ! empty( $iconName ) ) {
    $inner_html .= '<span class="' . $icon_class_attr . '">' . esc_html( $iconName ) . '</span>';
}

if ( $tagName === 'button' ) {
    $type_attr = '';
    if ( $actionType === 'submit' ) {
        $type_attr = ' type="submit"';
        $fieldName = $attributes['fieldName'] ?? '';
        $fieldValue = $attributes['fieldValue'] ?? '';
        if ( ! empty( $fieldName ) ) {
            $type_attr .= ' name="' . esc_attr( $fieldName ) . '"';
        }
        if ( ! empty( $fieldValue ) ) {
            $type_attr .= ' value="' . esc_attr( $fieldValue ) . '"';
        }
    }
    printf(
        '<button%1$s %2$s%4$s>%3$s</button>',
        $type_attr,
        $wrapper_attributes,
        $inner_html,
        $alpine_attrs
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
