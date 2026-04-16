<?php
/**
 * Ska Pattern Registry (Molecules)
 * 
 * Registers pre-configured block patterns (Ska Molecules) using Alpine.js and Tailwind CSS.
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

class Pattern_Registry {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_patterns' ) );
	}

	public static function register_patterns() {
		// Register the category
		register_block_pattern_category(
			'ska-molecules',
			array( 'label' => __( 'Ska Molecules', 'ska-no-code-design' ) )
		);

		// 1. Molecule: Tabs
		register_block_pattern(
			'ska-design/molecule-tabs',
			array(
				'title'       => __( 'Molecules: Tabs (Alpine.js)', 'ska-no-code-design' ),
				'categories'  => array( 'ska-molecules' ),
				'content'     => self::get_tabs_markup(),
			)
		);

		// 2. Molecule: Accordion
		register_block_pattern(
			'ska-design/molecule-accordion',
			array(
				'title'       => __( 'Molecules: Accordion (Alpine.js)', 'ska-no-code-design' ),
				'categories'  => array( 'ska-molecules' ),
				'content'     => self::get_accordion_markup(),
			)
		);
	}

	private static function get_tabs_markup() {
		return '<!-- wp:ska-builder/container {"tailwindClasses":"w-full max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden","htmlAttributes":[{"key":"x-data","value":"{ activeTab: \'tab1\' }"}]} -->
<!-- wp:ska-builder/container {"tailwindClasses":"flex border-b border-gray-200 bg-gray-50/50"} -->
<!-- wp:ska-builder/container {"tagName":"button","tailwindClasses":"flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100","htmlAttributes":[{"key":"@click","value":"activeTab = \'tab1\'"},{"key":":class","value":"activeTab === \'tab1\' ? \'active\' : \'\'"}]} -->
<!-- wp:ska-builder/text {"content":"Tab 1","tagName":"span"} /-->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tagName":"button","tailwindClasses":"flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100","htmlAttributes":[{"key":"@click","value":"activeTab = \'tab2\'"},{"key":":class","value":"activeTab === \'tab2\' ? \'active\' : \'\'"}]} -->
<!-- wp:ska-builder/text {"content":"Tab 2","tagName":"span"} /-->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tailwindClasses":"p-6"} -->
<!-- wp:ska-builder/container {"tailwindClasses":"prose text-gray-600","htmlAttributes":[{"key":"x-show","value":"activeTab === \'tab1\'"},{"key":"x-transition:enter","value":"transition ease-out duration-200"},{"key":"x-transition:enter-start","value":"opacity-0 translate-y-2"},{"key":"x-transition:enter-end","value":"opacity-100 translate-y-0"}]} -->
<!-- wp:ska-builder/text {"content":"Nội dung của Tab 1. Bạn có thể thay thế văn bản này bằng bất kỳ block nào khác.","tagName":"p"} /-->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tailwindClasses":"prose text-gray-600","htmlAttributes":[{"key":"x-show","value":"activeTab === \'tab2\'"},{"key":"x-transition:enter","value":"transition ease-out duration-200"},{"key":"x-transition:enter-start","value":"opacity-0 translate-y-2"},{"key":"x-transition:enter-end","value":"opacity-100 translate-y-0"},{"key":"style","value":"display: none;"}]} -->
<!-- wp:ska-builder/text {"content":"Nội dung của Tab 2. Bạn đã chuyển tab thành công bằng Alpine.js!","tagName":"p"} /-->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->';
	}

	private static function get_accordion_markup() {
		return '<!-- wp:ska-builder/container {"tailwindClasses":"w-full max-w-3xl mx-auto space-y-3","htmlAttributes":[{"key":"x-data","value":"{ activeAccordion: null }"}]} -->
<!-- wp:ska-builder/container {"tailwindClasses":"bg-white border text-gray-700 border-gray-200 rounded-lg overflow-hidden transition-all shadow-sm"} -->
<!-- wp:ska-builder/container {"tagName":"button","tailwindClasses":"w-full px-5 py-4 flex justify-between items-center text-left focus:outline-none hover:bg-gray-50","htmlAttributes":[{"key":"@click","value":"activeAccordion = activeAccordion === 1 ? null : 1"}]} -->
<!-- wp:ska-builder/text {"content":"Câu hỏi thường gặp 1?","tagName":"span","tailwindClasses":"font-semibold"} /-->
<!-- wp:ska-builder/icon {"iconName":"chevron-down","tailwindClasses":"w-5 h-5 transition-transform duration-200 text-gray-400","htmlAttributes":[{"key":":class","value":"activeAccordion === 1 ? \'rotate-180\' : \'\'"}]} /-->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tailwindClasses":"px-5 border-t border-gray-100 bg-gray-50/50","htmlAttributes":[{"key":"x-show","value":"activeAccordion === 1"},{"key":"x-collapse","value":""},{"key":"style","value":"display: none;"}]} -->
<!-- wp:ska-builder/text {"content":"Đúng vậy! Đây là nội dung bên trong Accordion 1. Bạn có thể chèn bất cứ block atomic nào.","tailwindClasses":"py-4 text-sm text-gray-600"} /-->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tailwindClasses":"bg-white border text-gray-700 border-gray-200 rounded-lg overflow-hidden transition-all shadow-sm"} -->
<!-- wp:ska-builder/container {"tagName":"button","tailwindClasses":"w-full px-5 py-4 flex justify-between items-center text-left focus:outline-none hover:bg-gray-50","htmlAttributes":[{"key":"@click","value":"activeAccordion = activeAccordion === 2 ? null : 2"}]} -->
<!-- wp:ska-builder/text {"content":"Câu hỏi thường gặp 2?","tagName":"span","tailwindClasses":"font-semibold"} /-->
<!-- wp:ska-builder/icon {"iconName":"chevron-down","tailwindClasses":"w-5 h-5 transition-transform duration-200 text-gray-400","htmlAttributes":[{"key":":class","value":"activeAccordion === 2 ? \'rotate-180\' : \'\'"}]} /-->
<!-- /wp:ska-builder/container -->

<!-- wp:ska-builder/container {"tailwindClasses":"px-5 border-t border-gray-100 bg-gray-50/50","htmlAttributes":[{"key":"x-show","value":"activeAccordion === 2"},{"key":"x-collapse","value":""},{"key":"style","value":"display: none;"}]} -->
<!-- wp:ska-builder/text {"content":"Đây là nội dung của Accordion 2. Được điều khiển hoàn toàn bằng Alpine.js.","tailwindClasses":"py-4 text-sm text-gray-600"} /-->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->';
	}
}
