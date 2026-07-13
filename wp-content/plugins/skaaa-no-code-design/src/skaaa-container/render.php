<?php
/**
 * Render: Skaaa Container
 * Hỗ trợ:
 * - Auto-Inject Alpine.js Form Engine khi isSkaaaForm = true.
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
    $link_url = \Skaaa\Builder\Utils\Dynamic_Data::resolve_dynamic_link( $attributes['link'] );
    $link_target = ! empty( $attributes['link']['target'] ) ? ' target="' . esc_attr( $attributes['link']['target'] ) . '"' : '';
}

if ( ! empty( $link_url ) ) {
    $tag_name = 'a';
    if ( strpos( $user_tailwindClasses, 'block' ) === false && strpos( $user_tailwindClasses, 'flex' ) === false && strpos( $user_tailwindClasses, 'grid' ) === false && strpos( $user_tailwindClasses, 'hidden' ) === false ) {
        $user_tailwindClasses = trim($user_tailwindClasses . ' block');
    }
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'skaaa-container-block ' . esc_attr( $user_tailwindClasses ),
) );

if ( ! empty( $attributes['logic']['enabled'] ) ) {
	$engine = \Skaaa\Builder\Logic\Core::instance();
	if ( ! $engine->should_render( $attributes['logic'] ) ) {
		return '';
	}
}

// === SKAAA FORM ENGINE & LINKS: Auto-Inject Alpine.js / Href ===
$form_extra_attrs = '';
if ( ! empty( $link_url ) ) {
    $form_extra_attrs .= ' href="' . \Skaaa\Builder\Utils\Dynamic_Data::safe_esc_url( $link_url ) . '"' . $link_target;
}

$is_skaaa_form      = ! empty( $attributes['isSkaaaForm'] );

if ( 'form' === $tag_name && $is_skaaa_form ) {
	$action_id   = ! empty( $attributes['formActionId'] ) ? esc_attr( $attributes['formActionId'] ) : 'default';
	$use_persist = ! empty( $attributes['usePersist'] ) ? 'true' : 'false';

	// Inject x-data Controller + @submit.prevent để chặn reload trang
	$form_extra_attrs .= sprintf(
		' x-data="skaaaForm(\'%s\', {persist: %s})" @submit.prevent="submitForm()"',
		$action_id,
		$use_persist
	);

	// Zero-overhead: Nạp skaaa-frontend TRƯỚC Alpine (thứ tự quan trọng!)
	wp_enqueue_script( 'skaaa-frontend' );
	wp_enqueue_script( 'skaaa-alpine' );
}

printf(
	'<%1$s %2$s%3$s>%4$s</%1$s>',
	esc_attr( $tag_name ),
	$wrapper_attributes,
	$form_extra_attrs,
	$content
);
