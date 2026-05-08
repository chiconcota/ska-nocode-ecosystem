<?php
/**
 * Render: Ska Container
 * Hỗ trợ:
 * - Auto-Inject Alpine.js Form Engine khi isSkaForm = true.
 * - formRole: Tự inject x-show để ẩn/hiện Container theo trạng thái form.
 */
defined( 'ABSPATH' ) || exit;

$tag_name = $attributes['tagName'] ?? 'div';
$content  = $content ?? '';

// Robust Fallback: Check for non-empty tailwindClasses first, then fallback to className (legacy).
$user_tailwindClasses = ! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' );

$link_url = '';
$link_target = '';
if ( ! empty( $attributes['link'] ) ) {
    $link_url = \Ska\Builder\Utils\Dynamic_Data::resolve_dynamic_link( $attributes['link'] );
    $link_target = ! empty( $attributes['link']['target'] ) ? ' target="' . esc_attr( $attributes['link']['target'] ) . '"' : '';
}

if ( ! empty( $link_url ) ) {
    $tag_name = 'a';
    if ( strpos( $user_tailwindClasses, 'block' ) === false && strpos( $user_tailwindClasses, 'flex' ) === false && strpos( $user_tailwindClasses, 'grid' ) === false && strpos( $user_tailwindClasses, 'hidden' ) === false ) {
        $user_tailwindClasses = trim($user_tailwindClasses . ' block');
    }
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'ska-container-block ' . esc_attr( $user_tailwindClasses ),
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
	$engine = \Ska\Builder\Logic\Core::instance();
	if ( ! $engine->should_render( $attributes['logic'] ) ) {
		return '';
	}
}

// === SKA FORM ENGINE & LINKS: Auto-Inject Alpine.js / Href ===
$form_extra_attrs = '';
if ( ! empty( $link_url ) ) {
    $form_extra_attrs .= ' href="' . esc_url( $link_url ) . '"' . $link_target;
}

$is_ska_form      = ! empty( $attributes['isSkaForm'] );

if ( 'form' === $tag_name && $is_ska_form ) {
	$action_id   = ! empty( $attributes['formActionId'] ) ? esc_attr( $attributes['formActionId'] ) : 'default';
	$use_persist = ! empty( $attributes['usePersist'] ) ? 'true' : 'false';

	// Inject x-data Controller + @submit.prevent để chặn reload trang
	$form_extra_attrs .= sprintf(
		' x-data="skaForm(\'%s\', {persist: %s})" @submit.prevent="submitForm()"',
		$action_id,
		$use_persist
	);

	// Zero-overhead: Nạp ska-frontend TRƯỚC Alpine (thứ tự quan trọng!)
	wp_enqueue_script( 'ska-frontend' );
	wp_enqueue_script( 'ska-alpine' );
}

printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	esc_attr( $tag_name ),
	$wrapper_attributes,
	$form_extra_attrs,
	$content
);
