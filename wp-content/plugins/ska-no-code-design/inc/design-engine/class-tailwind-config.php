<?php
/**
 * Tailwind Configuration Maps
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tailwind_Config
 */
class Tailwind_Config {
	public static $media_queries = array(
		'sm'  => '@media (min-width: 640px) { ',
		'md'  => '@media (min-width: 768px) { ',
		'lg'  => '@media (min-width: 1024px) { ',
		'xl'  => '@media (min-width: 1280px) { ',
		'2xl' => '@media (min-width: 1536px) { ',
		'max-sm'  => '@media (max-width: 639px) { ',
		'max-md'  => '@media (max-width: 767px) { ',
		'max-lg'  => '@media (max-width: 1023px) { ',
		'max-xl'  => '@media (max-width: 1279px) { ',
		'max-2xl' => '@media (max-width: 1535px) { ',
	);

	public static $basic_colors = array(
		'text-white' => 'color: #ffffff;',
		'text-black' => 'color: #000000;',
		'bg-white'   => 'background-color: #ffffff;',
		'bg-black'   => 'background-color: #000000;',
		'bg-transparent' => 'background-color: transparent;',
	);

	public static $weights = array(
		'font-thin'      => 'font-weight: 100;',
		'font-light'     => 'font-weight: 300;',
		'font-normal'    => 'font-weight: 400;',
		'font-medium'    => 'font-weight: 500;',
		'font-semibold'  => 'font-weight: 600;',
		'font-bold'      => 'font-weight: 700;',
		'font-extrabold' => 'font-weight: 800;',
		'font-black'     => 'font-weight: 900;',
	);

	public static $shadow_map = array(
		'shadow-sm' => 'box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);',
		'shadow'    => 'box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);',
		'shadow-md' => 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);',
		'shadow-lg' => 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);',
		'shadow-xl' => 'box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);',
		'shadow-2xl' => 'box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);',
		'shadow-none' => 'box-shadow: none;',
	);

	public static $max_w_map = array(
		'xs' => '20rem', 'sm' => '24rem', 'md' => '28rem', 'lg' => '32rem', 'xl' => '36rem',
		'2xl' => '42rem', '3xl' => '48rem', '4xl' => '56rem', '5xl' => '64rem', '6xl' => '72rem',
		'7xl' => '80rem', 'full' => '100%', 'none' => 'none',
	);

	public static $margin_auto_map = array(
		'mx-auto' => 'margin-left: auto; margin-right: auto;',
		'my-auto' => 'margin-top: auto; margin-bottom: auto;',
		'ml-auto' => 'margin-left: auto;',
		'mr-auto' => 'margin-right: auto;',
		'mt-auto' => 'margin-top: auto;',
		'mb-auto' => 'margin-bottom: auto;',
	);

	public static $layout_map = array(
		'flex'           => 'display: flex;',
		'inline-flex'    => 'display: inline-flex;',
		'grid'           => 'display: grid;',
		'inline-grid'    => 'display: inline-grid;',
		'flex-col'       => 'flex-direction: column;',
		'flex-row'       => 'flex-direction: row;',
		'flex-wrap'      => 'flex-wrap: wrap;',
		'items-start'    => 'align-items: flex-start;',
		'items-center'   => 'align-items: center;',
		'items-end'      => 'align-items: flex-end;',
		'items-stretch'  => 'align-items: stretch;',
		'justify-start'  => 'justify-content: flex-start;',
		'justify-center' => 'justify-content: center;',
		'justify-end'    => 'justify-content: flex-end;',
		'justify-between'=> 'justify-content: space-between;',
		'justify-around' => 'justify-content: space-around;',
	);

	public static $size_map = array(
		'xs'   => 'font-size: 0.75rem; line-height: 1rem;',
		'sm'   => 'font-size: 0.875rem; line-height: 1.25rem;',
		'base' => 'font-size: 1rem; line-height: 1.5rem;',
		'lg'   => 'font-size: 1.125rem; line-height: 1.75rem;',
		'xl'   => 'font-size: 1.25rem; line-height: 1.75rem;',
		'2xl'  => 'font-size: 1.5rem; line-height: 2rem;',
		'3xl'  => 'font-size: 1.875rem; line-height: 2.25rem;',
		'4xl'  => 'font-size: 2.25rem; line-height: 2.5rem;',
		'5xl'  => 'font-size: 3rem; line-height: 1;',
		'6xl'  => 'font-size: 3.75rem; line-height: 1;',
		'7xl'  => 'font-size: 4.5rem; line-height: 1;',
		'8xl'  => 'font-size: 6rem; line-height: 1;',
		'9xl'  => 'font-size: 8rem; line-height: 1;',
	);

	public static $text_align_map = array(
		'text-left'    => 'text-align: left;',
		'text-center'  => 'text-align: center;',
		'text-right'   => 'text-align: right;',
		'text-justify' => 'text-align: justify;',
	);

	public static $text_deco_map = array(
		'underline'    => 'text-decoration-line: underline;',
		'overline'     => 'text-decoration-line: overline;',
		'line-through' => 'text-decoration-line: line-through;',
		'no-underline' => 'text-decoration-line: none;',
	);

	public static $text_misc_map = array(
		'uppercase'    => 'text-transform: uppercase;',
		'lowercase'    => 'text-transform: lowercase;',
		'capitalize'   => 'text-transform: capitalize;',
		'normal-case'  => 'text-transform: none;',
		'italic'       => 'font-style: italic;',
		'not-italic'   => 'font-style: normal;',
		'truncate'     => 'overflow: hidden; text-overflow: ellipsis; white-space: nowrap;',
	);

	public static $whitespace_map = array(
		'whitespace-normal'       => 'white-space: normal;',
		'whitespace-nowrap'       => 'white-space: nowrap;',
		'whitespace-pre'          => 'white-space: pre;',
		'whitespace-pre-line'     => 'white-space: pre-line;',
		'whitespace-pre-wrap'     => 'white-space: pre-wrap;',
		'whitespace-break-spaces' => 'white-space: break-spaces;',
	);

	public static $leading_map = array(
		'none'    => '1',
		'tight'   => '1.25',
		'snug'    => '1.375',
		'normal'  => '1.5',
		'relaxed' => '1.625',
		'loose'   => '2',
	);

	public static $tracking_map = array(
		'tighter' => '-0.05em',
		'tight'   => '-0.025em',
		'normal'  => '0em',
		'wide'    => '0.025em',
		'wider'   => '0.05em',
		'widest'  => '0.1em',
	);

	public static $border_style_map = array(
		'border-solid'  => 'border-style: solid;',
		'border-dashed' => 'border-style: dashed;',
		'border-dotted' => 'border-style: dotted;',
		'border-double' => 'border-style: double;',
		'border-hidden' => 'border-style: hidden;',
		'border-none'   => 'border-style: none;',
	);

	public static $border_basic_map = array(
		'border-white'       => 'border-color: #ffffff;',
		'border-black'       => 'border-color: #000000;',
		'border-transparent' => 'border-color: transparent;',
	);

	public static $ring_basic = array(
		'ring-white'       => '--tw-ring-color: #ffffff;',
		'ring-black'       => '--tw-ring-color: #000000;',
		'ring-transparent' => '--tw-ring-color: transparent;',
	);

	public static $ring_offset_basic = array(
		'ring-offset-white' => '--tw-ring-offset-color: #ffffff;',
		'ring-offset-black' => '--tw-ring-offset-color: #000000;',
	);

	public static $bg_util_map = array(
		'bg-cover'      => 'background-size: cover;',
		'bg-contain'    => 'background-size: contain;',
		'bg-auto'       => 'background-size: auto;',
		'bg-center'     => 'background-position: center;',
		'bg-top'        => 'background-position: top;',
		'bg-bottom'     => 'background-position: bottom;',
		'bg-left'       => 'background-position: left;',
		'bg-right'      => 'background-position: right;',
		'bg-left-top'   => 'background-position: left top;',
		'bg-right-top'  => 'background-position: right top;',
		'bg-left-bottom'  => 'background-position: left bottom;',
		'bg-right-bottom' => 'background-position: right bottom;',
		'bg-repeat'     => 'background-repeat: repeat;',
		'bg-no-repeat'  => 'background-repeat: no-repeat;',
		'bg-repeat-x'   => 'background-repeat: repeat-x;',
		'bg-repeat-y'   => 'background-repeat: repeat-y;',
		'bg-repeat-round' => 'background-repeat: round;',
		'bg-repeat-space' => 'background-repeat: space;',
		'bg-fixed'      => 'background-attachment: fixed;',
		'bg-local'      => 'background-attachment: local;',
		'bg-scroll'     => 'background-attachment: scroll;',
		'bg-clip-border'  => 'background-clip: border-box;',
		'bg-clip-padding' => 'background-clip: padding-box;',
		'bg-clip-content' => 'background-clip: content-box;',
		'bg-clip-text'    => '-webkit-background-clip: text; background-clip: text;',
		'bg-origin-border'  => 'background-origin: border-box;',
		'bg-origin-padding' => 'background-origin: padding-box;',
		'bg-origin-content' => 'background-origin: content-box;',
	);

	public static $grad_basic = array(
		'from-white'       => '--tw-gradient-from: #ffffff; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, transparent);',
		'from-black'       => '--tw-gradient-from: #000000; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, transparent);',
		'from-transparent'  => '--tw-gradient-from: transparent; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, transparent);',
		'to-white'         => '--tw-gradient-to: #ffffff;',
		'to-black'         => '--tw-gradient-to: #000000;',
		'to-transparent'   => '--tw-gradient-to: transparent;',
		'via-white'        => '--tw-gradient-stops: var(--tw-gradient-from), #ffffff, var(--tw-gradient-to, transparent);',
		'via-black'        => '--tw-gradient-stops: var(--tw-gradient-from), #000000, var(--tw-gradient-to, transparent);',
		'via-transparent'  => '--tw-gradient-stops: var(--tw-gradient-from), transparent, var(--tw-gradient-to, transparent);',
	);

	public static $radius_map = array(
		''     => '0.25rem',
		'-sm'  => '0.125rem',
		'-md'  => '0.375rem',
		'-lg'  => '0.5rem',
		'-xl'  => '0.75rem',
		'-2xl' => '1rem',
		'-3xl' => '1.5rem',
		'-full'=> '9999px',
		'-none'=> '0px',
	);

	public static $flex_extra = array(
		'flex-1' => 'flex: 1 1 0%;', 'flex-auto' => 'flex: 1 1 auto;',
		'flex-initial' => 'flex: 0 1 auto;', 'flex-none' => 'flex: none;',
		'flex-shrink' => 'flex-shrink: 1;', 'flex-shrink-0' => 'flex-shrink: 0;',
		'flex-grow' => 'flex-grow: 1;', 'flex-grow-0' => 'flex-grow: 0;',
		'self-auto' => 'align-self: auto;', 'self-start' => 'align-self: flex-start;',
		'self-end' => 'align-self: flex-end;', 'self-center' => 'align-self: center;',
		'self-stretch' => 'align-self: stretch;',
		'order-first' => 'order: -9999;', 'order-last' => 'order: 9999;', 'order-none' => 'order: 0;',
	);

	public static $transition_map = array(
		'-all' => 'transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms;',
		'-colors' => 'transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms;',
		'-opacity' => 'transition-property: opacity; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms;',
		'-shadow' => 'transition-property: box-shadow; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms;',
		'-transform' => 'transition-property: transform; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms;',
	);

	public static $ease_map = array(
		'linear' => 'linear',
		'in'     => 'cubic-bezier(0.4, 0, 1, 1)',
		'out'    => 'cubic-bezier(0, 0, 0.2, 1)',
		'in-out' => 'cubic-bezier(0.4, 0, 0.2, 1)',
	);

	public static $blur_map = array(
		'' => '8px', '-none' => '0', '-sm' => '4px', '-md' => '12px',
		'-lg' => '16px', '-xl' => '24px', '-2xl' => '40px', '-3xl' => '64px',
	);

	/**
	 * Returns the base global CSS resets for Ska blocks.
	 * This is critical for 100% layout fidelity between the editor and frontend.
	 *
	 * @return string CSS resets
	 */
	public static function get_core_reset_css(): string {
		$css = "/* Ska Core Block Resets (Atomic Specificity) */\n";
		
		$typography = Tailwind_Color_Registry::get_typography_config();
		$primary_font = wp_strip_all_tags( $typography['primary'] );
		$secondary_font = wp_strip_all_tags( $typography['secondary'] );
		$custom_font_url = esc_url( $typography['customFontUrl'] );
		
		if ( ! empty( $custom_font_url ) ) {
			$css .= "@font-face {\n";
			$css .= "  font-family: 'SkaCustomFont';\n";
			$css .= "  src: url('{$custom_font_url}') format('woff2');\n";
			$css .= "  font-weight: normal;\n";
			$css .= "  font-style: normal;\n";
			$css .= "  font-display: swap;\n";
			$css .= "}\n";
		}
		
		// Container width setup
		$tokens = Tailwind_Color_Registry::get_tokens_config();
		$container_width = isset( $tokens['containerWidth'] ) ? wp_strip_all_tags( $tokens['containerWidth'] ) : '1280px';

		// 1. Global Typography Reset (Tailwind Standard) for Barebone Themes
		$css .= ":root {\n";
		$css .= "  --font-primary: {$primary_font};\n";
		$css .= "  --font-secondary: {$secondary_font};\n";
		$css .= "  --ska-container-width: {$container_width};\n";
		$css .= "}\n";
		$css .= "html body.ska-builder .ska-container, .editor-styles-wrapper .ska-container { width: 100%; max-width: var(--ska-container-width); margin-left: auto; margin-right: auto; }\n";
		$css .= "html body.ska-builder, .editor-styles-wrapper { font-family: var(--font-primary), ui-sans-serif, system-ui, sans-serif, \"Apple Color Emoji\", \"Segoe UI Emoji\", \"Segoe UI Symbol\", \"Noto Color Emoji\"; }\n";
		$css .= "html body.ska-builder h1, html body.ska-builder h2, html body.ska-builder h3, html body.ska-builder h4, html body.ska-builder h5, html body.ska-builder h6, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6 { font-family: var(--font-secondary), ui-sans-serif, system-ui, sans-serif; }\n";

		$prefixes = array(
			"html body.ska-builder",
			".editor-styles-wrapper"
		);

		$build_rule = function( $target ) use ( $prefixes ) {
			$parts = array();
			foreach ( $prefixes as $prefix ) {
				if ( $target === '' ) {
					$parts[] = "$prefix :where([class*='wp-block-ska-builder'])";
				} else {
					$parts[] = "$prefix :where({$target}[class*='wp-block-ska-builder'])";
					$parts[] = "$prefix :where([class*='wp-block-ska-builder'] {$target})";
				}
			}
			return implode( ', ', $parts );
		};

		$css .= "html body.ska-builder *, html body.ska-builder ::before, html body.ska-builder ::after, .editor-styles-wrapper *, .editor-styles-wrapper ::before, .editor-styles-wrapper ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e5e7eb; }\n";
		$css .= $build_rule( '' ) . " { --wp--style--block-gap: 0px; margin: 0; }\n";
		$css .= $build_rule( 'button:not(:where(.components-button))' ) . " { border: 0; background: none; padding: 0; margin: 0; cursor: pointer; font-family: inherit; }\n";
		$css .= $build_rule( 'a' ) . " { text-decoration: none; color: inherit; }\n";
		$css .= $build_rule( 'a.underline' ) . " { text-decoration: underline; }\n";

		// Typography Margin Resets (Tailwind Preflight Parity)
		$typography_elements = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'figure', 'hr' );
		foreach ( $typography_elements as $el ) {
			$css .= $build_rule( $el ) . " { margin: 0; }\n";
		}

		// List Resets (Tailwind Preflight Parity)
		$list_elements = array( 'ul', 'ol' );
		foreach ( $list_elements as $el ) {
			$css .= $build_rule( $el ) . " { list-style: none; margin: 0; padding: 0; }\n";
		}

		// Form Resets (Tailwind Preflight Parity)
		$form_elements = array( 'input:not([type="checkbox"]):not([type="radio"])', 'select', 'textarea', 'option', 'optgroup' );
		foreach ( $form_elements as $el ) {
			$css .= $build_rule( $el ) . " { appearance: none; background-color: transparent; border-width: 0; border-radius: 0; padding: 0; border-color: #e5e7eb; outline: none; font-family: inherit; font-size: 100%; font-weight: inherit; line-height: inherit; color: inherit; margin: 0; }\n";
		}

		return $css;
	}
}
