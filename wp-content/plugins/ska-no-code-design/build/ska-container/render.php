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

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'ska-container-block ' . esc_attr( $user_tailwindClasses ),
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
	$engine = \Ska\Builder\Logic\Core::instance();
	if ( ! $engine->should_render( $attributes['logic'] ) ) {
		return '';
	}
}

// === SKA FORM ENGINE: Auto-Inject Alpine.js ===
$form_extra_attrs = '';
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
