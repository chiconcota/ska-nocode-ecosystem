<?php
/**
 * Tailwind Color Registry
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tailwind_Color_Registry
 */
class Tailwind_Color_Registry {

	/**
	 * Hex to RGB helper.
	 */
	public static function hex_to_rgb( string $hex ): string {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return intval( substr( $hex, 0, 2 ), 16 ) . ', ' . intval( substr( $hex, 2, 2 ), 16 ) . ', ' . intval( substr( $hex, 4, 2 ), 16 );
	}

	/**
	 * Mock helper for Tailwind color palette.
	 */
	public static function get_color_hex( $color, $shade ) {
		$palette = array(
			'slate'   => ['50' => '#f8fafc', '100' => '#f1f5f9', '200' => '#e2e8f0', '300' => '#cbd5e1', '400' => '#94a3b8', '500' => '#64748b', '600' => '#475569', '700' => '#334155', '800' => '#1e293b', '900' => '#0f172a', '950' => '#020617'],
			'gray'    => ['50' => '#f9fafb', '100' => '#f3f4f6', '200' => '#e5e7eb', '300' => '#d1d5db', '400' => '#9ca3af', '500' => '#6b7280', '600' => '#4b5563', '700' => '#374151', '800' => '#1f2937', '900' => '#111827', '950' => '#030712'],
			'zinc'    => ['50' => '#fafafa', '100' => '#f4f4f5', '200' => '#e4e4e7', '300' => '#d4d4d8', '400' => '#a1a1aa', '500' => '#71717a', '600' => '#52525b', '700' => '#3f3f46', '800' => '#27272a', '900' => '#18181b', '950' => '#09090b'],
			'neutral' => ['50' => '#fafafa', '100' => '#f5f5f5', '200' => '#e5e5e5', '300' => '#d4d4d4', '400' => '#a3a3a3', '500' => '#737373', '600' => '#525252', '700' => '#404040', '800' => '#262626', '900' => '#171717', '950' => '#0a0a0a'],
			'stone'   => ['50' => '#fafaf9', '100' => '#f5f5f4', '200' => '#e7e5e4', '300' => '#d6d3d1', '400' => '#a8a29e', '500' => '#78716c', '600' => '#57534e', '700' => '#44403c', '800' => '#292524', '900' => '#1c1917', '950' => '#0c0a09'],
			'red'     => ['50' => '#fef2f2', '100' => '#fee2e2', '200' => '#fecaca', '300' => '#fca5a5', '400' => '#f87171', '500' => '#ef4444', '600' => '#dc2626', '700' => '#b91c1c', '800' => '#991b1b', '900' => '#7f1d1d', '950' => '#450a0a'],
			'orange'  => ['50' => '#fff7ed', '100' => '#ffedd5', '200' => '#fed7aa', '300' => '#fdba74', '400' => '#fb923c', '500' => '#f97316', '600' => '#ea580c', '700' => '#c2410c', '800' => '#9a3412', '900' => '#7c2d12', '950' => '#431407'],
			'amber'   => ['50' => '#fffbeb', '100' => '#fef3c7', '200' => '#fde68a', '300' => '#fcd34d', '400' => '#fbbf24', '500' => '#f59e0b', '600' => '#d97706', '700' => '#b45309', '800' => '#92400e', '900' => '#78350f', '950' => '#451a03'],
			'yellow'  => ['50' => '#fefce8', '100' => '#fef9c3', '200' => '#fef08a', '300' => '#fde047', '400' => '#facc15', '500' => '#eab308', '600' => '#ca8a04', '700' => '#a16207', '800' => '#854d0e', '900' => '#713f12', '950' => '#422006'],
			'lime'    => ['50' => '#f7fee7', '100' => '#ecfccb', '200' => '#d9f99d', '300' => '#bef264', '400' => '#a3e635', '500' => '#84cc16', '600' => '#65a30d', '700' => '#4d7c0f', '800' => '#3f6212', '900' => '#365314', '950' => '#1a2e05'],
			'green'   => ['50' => '#f0fdf4', '100' => '#dcfce7', '200' => '#bbf7d0', '300' => '#86efac', '400' => '#4ade80', '500' => '#22c55e', '600' => '#16a34a', '700' => '#15803d', '800' => '#166534', '900' => '#14532d', '950' => '#052e16'],
			'emerald' => ['50' => '#ecfdf5', '100' => '#d1fae5', '200' => '#a7f3d0', '300' => '#6ee7b7', '400' => '#34d399', '500' => '#10b981', '600' => '#059669', '700' => '#047857', '800' => '#065f46', '900' => '#064e3b', '950' => '#022c22'],
			'teal'    => ['50' => '#f0fdfa', '100' => '#ccfbf1', '200' => '#99f6e4', '300' => '#5eead4', '400' => '#2dd4bf', '500' => '#14b8a6', '600' => '#0d9488', '700' => '#0f766e', '800' => '#115e59', '900' => '#134e4a', '950' => '#042f2e'],
			'cyan'    => ['50' => '#ecfeff', '100' => '#cffafe', '200' => '#a5f3fc', '300' => '#67e8f9', '400' => '#22d3ee', '500' => '#06b6d4', '600' => '#0891b2', '700' => '#0e7490', '800' => '#155e75', '900' => '#164e63', '950' => '#083344'],
			'sky'     => ['50' => '#f0f9ff', '100' => '#e0f2fe', '200' => '#bae6fd', '300' => '#7dd3fc', '400' => '#38bdf8', '500' => '#0ea5e9', '600' => '#0284c7', '700' => '#0369a1', '800' => '#075985', '900' => '#0c4a6e', '950' => '#082f49'],
			'blue'    => ['50' => '#eff6ff', '100' => '#dbeafe', '200' => '#bfdbfe', '300' => '#93c5fd', '400' => '#60a5fa', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8', '800' => '#1e40af', '900' => '#1e3a8a', '950' => '#172554'],
			'indigo'  => ['50' => '#eef2ff', '100' => '#e0e7ff', '200' => '#c7d2fe', '300' => '#a5b4fc', '400' => '#818cf8', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca', '800' => '#3730a3', '900' => '#312e81', '950' => '#1e1b4b'],
			'violet'  => ['50' => '#f5f3ff', '100' => '#ede9fe', '200' => '#ddd6fe', '300' => '#c4b5fd', '400' => '#a78bfa', '500' => '#8b5cf6', '600' => '#7c3aed', '700' => '#6d28d9', '800' => '#5b21b6', '900' => '#4c1d95', '950' => '#2e1065'],
			'purple'  => ['50' => '#faf5ff', '100' => '#f3e8ff', '200' => '#e9d5ff', '300' => '#d8b4fe', '400' => '#c084fc', '500' => '#a855f7', '600' => '#9333ea', '700' => '#7e22ce', '800' => '#6b21a8', '900' => '#581c87', '950' => '#3b0764'],
			'fuchsia' => ['50' => '#fdf4ff', '100' => '#fae8ff', '200' => '#f5d0fe', '300' => '#f0abfc', '400' => '#e879f9', '500' => '#d946ef', '600' => '#c026d3', '700' => '#a21caf', '800' => '#86198f', '900' => '#701a75', '950' => '#4a044e'],
			'pink'    => ['50' => '#fdf2f8', '100' => '#fce7f3', '200' => '#fbcfe8', '300' => '#f9a8d4', '400' => '#f472b6', '500' => '#ec4899', '600' => '#db2777', '700' => '#be185d', '800' => '#9d174d', '900' => '#831843', '950' => '#500724'],
			'rose'    => ['50' => '#fff1f2', '100' => '#ffe4e6', '200' => '#fecdd3', '300' => '#fda4af', '400' => '#fb7185', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c', '800' => '#9f1239', '900' => '#881337', '950' => '#4c0519'],
			'black'   => ['default' => '#000000'],
			'white'   => ['default' => '#ffffff'],
		);

		return $palette[$color][$shade] ?? ($palette[$color]['default'] ?? '#000');
	}

	/**
	 * Lấy bảng màu tùy chỉnh từ Physical Cache (tokens.json) hoặc DB.
	 *
	 * @return array Associative array { 'primary' => '#0d46f2', ... }
	 */
	public static function get_custom_colors(): array {
		$upload_dir = wp_upload_dir();
		$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/tokens.json';
		$colors = array();

		// 1. Đọc từ Physical Cache
		if ( file_exists( $cache_file ) ) {
			$json_data = file_get_contents( $cache_file );
			$tokens = json_decode( $json_data, true );
			if ( isset( $tokens['colors'] ) && is_array( $tokens['colors'] ) ) {
				$colors = $tokens['colors'];
			}
		}

		// 2. Fallback: Nếu không có file cache, đọc từ Database
		if ( empty( $colors ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'ska_data_sys_presets';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
				$rows = $wpdb->get_results( "SELECT name, value FROM {$table_name} WHERE type = 'token_color'", ARRAY_A );
				if ( $rows ) {
					foreach ( $rows as $row ) {
						$key = sanitize_key( str_replace( '-', '_', sanitize_title( $row['name'] ) ) );
						$colors[ $key ] = $row['value'];
					}
				}
			}
		}

		// 3. Fallback: Default colors
		if ( empty( $colors ) ) {
			$colors = array(
				'primary'          => '#0d46f2',
				'background-light' => '#f5f6f8',
				'background-dark'  => '#101422',
				'secondary'        => '#1e293b',
			);
		}
		return $colors;
	}

	/**
	 * Lấy bảng màu Dark Mode từ Physical Cache (tokens.json).
	 *
	 * @return array Associative array { 'primary' => '#60a5fa', ... }
	 */
	public static function get_custom_dark_colors(): array {
		$upload_dir = wp_upload_dir();
		$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/tokens.json';
		$colors = array();

		if ( file_exists( $cache_file ) ) {
			$json_data = file_get_contents( $cache_file );
			$tokens = json_decode( $json_data, true );
			if ( isset( $tokens['darkColors'] ) && is_array( $tokens['darkColors'] ) ) {
				$colors = $tokens['darkColors'];
			}
		}

		if ( empty( $colors ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'ska_data_sys_presets';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
				$rows = $wpdb->get_results( "SELECT name, value FROM {$table_name} WHERE type = 'token_dark_color'", ARRAY_A );
				if ( $rows ) {
					foreach ( $rows as $row ) {
						$key = sanitize_key( str_replace( '-', '_', sanitize_title( $row['name'] ) ) );
						$colors[ $key ] = $row['value'];
					}
				}
			}
		}

		if ( empty( $colors ) ) {
			$colors = array(
				'primary'    => '#60a5fa',
				'background' => '#111827',
			);
		}
		return $colors;
	}

	/**
	 * Lấy thông tin Typography từ tokens.json
	 */
	public static function get_typography_config(): array {
		$upload_dir = wp_upload_dir();
		$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/tokens.json';
		$typography = array(
			'primary' => 'Inter, sans-serif',
			'secondary' => 'Outfit, sans-serif',
			'customFontUrl' => ''
		);

		if ( file_exists( $cache_file ) ) {
			$json_data = file_get_contents( $cache_file );
			$tokens = json_decode( $json_data, true );
			if ( isset( $tokens['typography'] ) && is_array( $tokens['typography'] ) ) {
				$typography = wp_parse_args( $tokens['typography'], $typography );
			}
		}

		return $typography;
	}

	/**
	 * Lấy thông tin Tokens cơ bản từ tokens.json (container width, border radius, v.v)
	 */
	public static function get_tokens_config(): array {
		$upload_dir = wp_upload_dir();
		$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/tokens.json';
		$sys_tokens = array(
			'borderRadius' => '8px',
			'boxShadow' => 'none',
			'containerWidth' => '1280px',
			'transitionDuration' => '150ms',
			'blockGap' => '1.5rem',
			'contentPadding' => '1rem'
		);

		if ( file_exists( $cache_file ) ) {
			$json_data = file_get_contents( $cache_file );
			$parsed = json_decode( $json_data, true );
			if ( isset( $parsed['tokens'] ) && is_array( $parsed['tokens'] ) ) {
				// Convert snake_case keys from tokens.json to camelCase to match defaults
				$camel_tokens = array();
				foreach ( $parsed['tokens'] as $key => $val ) {
					$camel_key = lcfirst( str_replace( ' ', '', ucwords( str_replace( array( '_', '-' ), ' ', $key ) ) ) );
					$camel_tokens[ $camel_key ] = $val;
				}
				$sys_tokens = wp_parse_args( $camel_tokens, $sys_tokens );
			}
		}

		return $sys_tokens;
	}

	/**
	 * Resolve custom brand color classes.
	 *
	 * Hỗ trợ: bg-{name}, text-{name}, border-{name}, ring-{name}
	 * Hỗ trợ opacity modifier: bg-{name}/{opacity}
	 *
	 * @param string $class Tailwind class.
	 * @param array|null $colors Mảng màu tùy chỉnh (tùy chọn)
	 * @return string|null CSS rule hoặc null nếu không khớp.
	 */
	public static function resolve_custom_color( string $class, ?array $colors = null ): ?string {
		$custom_colors = $colors ?? self::get_custom_colors();
		if ( empty( $custom_colors ) ) {
			return null;
		}

		$css_prop_map = array(
			'bg'     => 'background-color',
			'text'   => 'color',
			'border' => 'border-color',
			'ring'   => '--tw-ring-color',
			'shadow' => '--tw-shadow-color',
			'from'   => '--tw-gradient-from',
			'to'     => '--tw-gradient-to',
		);

		// Pattern: {prefix}-{color_name} hoặc {prefix}-{color_name}/{opacity}
		if ( ! preg_match( '/^(bg|text|border|ring|shadow|from|to)-([a-z0-9-]+?)(?:\/([0-9]+))?$/', $class, $matches ) ) {
			return null;
		}

		$prefix     = $matches[1];
		$color_name = $matches[2];
		$opacity    = isset( $matches[3] ) ? intval( $matches[3] ) : null;

		if ( ! isset( $custom_colors[ $color_name ] ) || ! isset( $css_prop_map[ $prefix ] ) ) {
			return null;
		}

		$hex      = $custom_colors[ $color_name ];
		$css_prop = $css_prop_map[ $prefix ];

		if ( null !== $opacity ) {
			$rgb = self::hex_to_rgb( $hex );
			$alpha = round( $opacity / 100, 2 );
			return "{$css_prop}: rgba({$rgb}, {$alpha});";
		}

		return "{$css_prop}: {$hex};";
	}

	/**
	 * Generate a complete CSS block for all custom brand colors.
	 * This is useful for pre-generating "Source of Truth" styles for the editor.
	 *
	 * @return string CSS block.
	 */
	public static function get_brand_colors_css(): string {
		$custom_colors = self::get_custom_colors();
		if ( empty( $custom_colors ) ) {
			return '';
		}

		$css = "/* Ska Brand Colors - Plugin Source Truth */\n";
		foreach ( array_keys( $custom_colors ) as $name ) {
			$base_classes = array(
				"bg-{$name}",
				"text-{$name}",
				"border-{$name}",
				"shadow-{$name}",
			);

			foreach ( $base_classes as $base ) {
				$classes_to_compile = array( $base, "{$base}/20", "{$base}/50", "{$base}/80" );
				foreach ( $classes_to_compile as $class ) {
					$rule = self::resolve_custom_color( $class, $custom_colors );
					if ( $rule ) {
						// Add !important to ensure it overrides Tailwind CDN's !important rule
						$rule = str_replace( ';', ' !important;', $rule );
						$escaped = str_replace( array( '/', '.' ), array( '\/', '\.' ), $class );
						$css .= ".{$escaped} { {$rule} }\n";
						$css .= "html body.ska-builder .{$escaped}.{$escaped} { {$rule} }\n";
						$css .= ".editor-styles-wrapper .{$escaped}.{$escaped} { {$rule} }\n";
					}
				}
			}
		}

		// Thêm CSS dành riêng cho chế độ Dark Mode từ Dark Colors
		$custom_dark_colors = self::get_custom_dark_colors();
		if ( ! empty( $custom_dark_colors ) ) {
			foreach ( array_keys( $custom_dark_colors ) as $name ) {
				$base_classes = array(
					"bg-{$name}",
					"text-{$name}",
					"border-{$name}",
					"shadow-{$name}",
				);

				foreach ( $base_classes as $base ) {
					$classes_to_compile = array( $base, "{$base}/20", "{$base}/50", "{$base}/80" );
					foreach ( $classes_to_compile as $class ) {
						$rule = self::resolve_custom_color( $class, $custom_dark_colors );
						if ( $rule ) {
							// Add !important to ensure it overrides Tailwind CDN's !important rule
							$rule = str_replace( ';', ' !important;', $rule );
							$escaped = str_replace( array( '/', '.' ), array( '\/', '\.' ), $class );
							$dark_escaped = "dark\\:{$escaped}";
							$css .= ".dark .{$dark_escaped} { {$rule} }\n";
							$css .= "html.dark body.ska-builder .{$dark_escaped}.{$dark_escaped} { {$rule} }\n";
							$css .= "html.dark .editor-styles-wrapper .{$dark_escaped}.{$dark_escaped} { {$rule} }\n";
						}
					}
				}
			}
		}

		return $css;
	}
}
