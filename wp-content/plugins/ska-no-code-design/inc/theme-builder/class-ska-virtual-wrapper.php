<?php
/**
 * Smart Virtual Wrapper
 * Bypasses or integrates with the active theme to render Theme Templates.
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined( 'ABSPATH' ) || exit;

class Ska_Virtual_Wrapper {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Hook for Ska Canvas fallback template
		add_action( 'ska_theme_header', array( $this, 'render_header' ) );
		add_action( 'ska_theme_footer', array( $this, 'render_footer' ) );

		// Hook into standard WordPress template hierarchy
		add_filter( 'template_include', array( $this, 'override_template' ), 99 );
	}

	/**
	 * Render Header location
	 */
	public function render_header() {
		$this->render_location( 'header' );
	}

	/**
	 * Render Footer location
	 */
	public function render_footer() {
		$this->render_location( 'footer' );
	}

	/**
	 * Render a specific template by its ID
	 */
	public function render_template_by_id( $template_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ska_data_sys_theme_templates';

		$template = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $template_id ), ARRAY_A );

		if ( $template && ! empty( $template['organism_id'] ) ) {
			echo $this->get_organism_html( $template['organism_id'] );
		}
	}

	/**
	 * Override the main template if a matching Theme Template exists
	 */
	public function override_template( $template ) {
		$location = '';

		$portal_slug = get_query_var( 'ska_portal' );

		if ( ! empty( $portal_slug ) ) {
			$location = 'app_layout'; // Allow users to design portal using 'app_layout' templates
		} elseif ( is_404() ) {
			$location = '404';
		} elseif ( is_singular() ) {
			$location = 'single';
		} elseif ( is_archive() || is_home() || is_search() ) {
			$location = 'archive';
		}

		$matched_template = ! empty( $location ) ? $this->get_matched_template( $location ) : null;
		$matched_header   = $this->get_matched_template( 'header' );
		$matched_footer   = $this->get_matched_template( 'footer' );
		$matched_app      = $this->get_matched_template( 'app_layout' );

		// If ANY of these parts exist, we take over the rendering with our Virtual Wrapper
		if ( ! empty( $matched_template ) || ! empty( $matched_header ) || ! empty( $matched_footer ) || ! empty( $matched_app ) ) {
			// We have at least one matched template! Use our Virtual Template wrapper.
			global $ska_current_template_id;
			global $ska_active_theme_organisms;
			
			$ska_current_template_id = ! empty( $matched_template ) ? $matched_template['id'] : 0;

			// Register active organisms for JIT compiler scanning during wp_head
			$ska_active_theme_organisms = array();
			if ( ! empty( $matched_template ) && ! empty( $matched_template['organism_id'] ) ) {
				$ska_active_theme_organisms[] = $matched_template['organism_id'];
			}
			if ( ! empty( $matched_header ) && ! empty( $matched_header['organism_id'] ) ) {
				$ska_active_theme_organisms[] = $matched_header['organism_id'];
			}
			if ( ! empty( $matched_footer ) && ! empty( $matched_footer['organism_id'] ) ) {
				$ska_active_theme_organisms[] = $matched_footer['organism_id'];
			}
			if ( ! empty( $matched_app ) && ! empty( $matched_app['organism_id'] ) ) {
				$ska_active_theme_organisms[] = $matched_app['organism_id'];
			}

			$custom_template = plugin_dir_path( __FILE__ ) . 'templates/virtual-template.php';
			
			// Ensure the templates directory exists
			if ( ! file_exists( plugin_dir_path( __FILE__ ) . 'templates' ) ) {
				wp_mkdir_p( plugin_dir_path( __FILE__ ) . 'templates' );
			}

			// Create the virtual template file if it doesn't exist
			if ( ! file_exists( $custom_template ) ) {
				$this->create_virtual_template_file( $custom_template );
			}

			return $custom_template;
		}

		return $template;
	}

	/**
	 * Render a specific location (Header, Footer) based on conditions
	 */
	public function render_location( $location ) {
		$matched_template = $this->get_matched_template( $location );

		if ( ! empty( $matched_template ) && ! empty( $matched_template['organism_id'] ) ) {
			echo $this->get_organism_html( $matched_template['organism_id'] );
		}
	}

	/**
	 * Find the best matching template for a given location
	 */
	private function get_matched_template( $location ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ska_data_sys_theme_templates';

		// Chỉ lấy các template đang Active (is_active = 1)
		$templates = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE location = %s AND is_active = 1 ORDER BY id DESC", $location ), ARRAY_A );

		if ( empty( $templates ) ) {
			return null;
		}

		// Evaluate advanced condition parsing
		foreach ( $templates as $template ) {
			$conditions_json = $template['conditions'];
			
			// If no conditions are set, it's a global template for this location
			if ( empty( $conditions_json ) || trim( $conditions_json ) === '[]' ) {
				return $template;
			}

			$conditions = json_decode( $conditions_json, true );
			if ( ! is_array( $conditions ) ) {
				return $template; // Fallback if invalid JSON
			}

			// Hỗ trợ định dạng mới: {"rules": [...], "folder_id": "..."}
			$rules_list = isset( $conditions['rules'] ) && is_array( $conditions['rules'] ) ? $conditions['rules'] : $conditions;

			$is_included      = false;
			$is_excluded      = false;
			$has_include_rule = false;

			foreach ( $rules_list as $rule_data ) {
				$type = isset( $rule_data['type'] ) ? $rule_data['type'] : 'include';
				
				if ( 'include' === $type ) {
					$has_include_rule = true;
				}
				
				if ( $this->evaluate_rule( $rule_data ) ) {
					if ( 'exclude' === $type ) {
						$is_excluded = true;
						break; // Once excluded, the template is rejected
					} else {
						$is_included = true;
					}
				}
			}

			// If the rules list only contains 'exclude' rules, it's implicitly included globally unless excluded
			if ( ! $has_include_rule ) {
				$is_included = true;
			}

			if ( $is_included && ! $is_excluded ) {
				return $template;
			}
		}

		return null;
	}

	/**
	 * Evaluate a single display condition rule
	 */
	private function evaluate_rule( $rule_data ) {
		if ( ! is_array( $rule_data ) || empty( $rule_data['rule'] ) ) {
			return false;
		}

		$rule  = $rule_data['rule'];
		$value = isset( $rule_data['value'] ) ? trim( $rule_data['value'] ) : '';

		switch ( $rule ) {
			case 'all':
				return true;
			case 'is_front_page':
				return is_front_page() || is_home();
			case 'is_archive':
				return is_archive() || is_search();
			case 'is_single':
				return is_singular();
			case 'post_type':
				if ( empty( $value ) ) {
					return is_singular();
				}
				return is_singular( $value ) || is_post_type_archive( $value );
			case 'specific_post':
				if ( empty( $value ) ) {
					return false;
				}
				return is_singular() && ( (string) get_the_ID() === $value );
			case 'is_404':
				return is_404();
			case 'is_search':
				return is_search();
			case 'is_portal':
				return ! empty( get_query_var( 'ska_portal' ) );
			case 'specific_portal':
				if ( empty( $value ) ) {
					return ! empty( get_query_var( 'ska_portal' ) );
				}
				return get_query_var( 'ska_portal' ) === $value;
			case 'specific_portal_list':
				if ( empty( $value ) ) {
					return ! empty( get_query_var( 'ska_portal' ) ) && empty( get_query_var( 'ska_id' ) );
				}
				return get_query_var( 'ska_portal' ) === $value && empty( get_query_var( 'ska_id' ) );
			case 'specific_portal_detail':
				$ska_id = get_query_var( 'ska_id' );
				if ( empty( $value ) ) {
					return ! empty( get_query_var( 'ska_portal' ) ) && ! empty( $ska_id ) && $ska_id !== 'create';
				}
				return get_query_var( 'ska_portal' ) === $value && ! empty( $ska_id ) && $ska_id !== 'create';
			case 'specific_portal_create':
				$ska_id = get_query_var( 'ska_id' );
				if ( empty( $value ) ) {
					return ! empty( get_query_var( 'ska_portal' ) ) && $ska_id === 'create';
				}
				return get_query_var( 'ska_portal' ) === $value && $ska_id === 'create';
		}

		return false;
	}

	private function get_organism_html( $organism_id ) {
		if ( ! class_exists( '\Ska\Design\Api\Organisms_API' ) ) {
			// Fallback direct query if API class is not loaded
			global $wpdb;
			$org_table = $wpdb->prefix . 'ska_data_sys_organisms';
			$html = $wpdb->get_var( $wpdb->prepare( "SELECT html_content FROM {$org_table} WHERE id = %d", $organism_id ) );
			$html = $html ? do_blocks( $html ) : '';
		} else {
			$html_array = \Ska\Design\Api\Organisms_API::get_bulk_html( array( $organism_id ) );
			$html = isset( $html_array[ $organism_id ] ) ? do_blocks( $html_array[ $organism_id ] ) : '';
		}

		// --------------------------------------------------------------------------
		// THEME TEMPLATE HYDRATION (Global Context for Dedicated Portal Pages)
		// --------------------------------------------------------------------------
		global $ska_current_portal;
		$ska_id = get_query_var( 'ska_id' );

		if ( ! empty( $ska_id ) && ! empty( $ska_current_portal ) && ! empty( $ska_current_portal['table_name'] ) && class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
			$table_name = $ska_current_portal['table_name'];
			$args = array(
				'filter_field' => 'id',
				'filter_op'    => 'eq',
				'filter_val'   => $ska_id,
			);
			
			// Lấy Record hiện tại của Portal Page
			$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, $args, 1 );
			if ( ! empty( $rows ) ) {
				$context = $rows[0];

				// Regex Hydration y hệt như Ska Loop Render (hỗ trợ cả URL encoded)
				$html = preg_replace_callback( '/\{\{\s*([a-zA-Z0-9_\.\$\-]+)\s*\}\}|\[\s*([a-zA-Z0-9_\.\$\-]+)\s*\]|%7B%7B\s*([a-zA-Z0-9_\.\$\-]+)\s*%7D%7D|%5B\s*([a-zA-Z0-9_\.\$\-]+)\s*%5D/', function( $matches ) use ( $context, $table_name ) {
					$raw_key = '';
					for ($i = 1; $i <= 4; $i++) {
						if (!empty($matches[$i])) {
							$raw_key = trim($matches[$i]);
							break;
						}
					}
					
					// Dọn dẹp prefix (vd: 'app_courses.courses.teacher_id.url' -> 'teacher_id.url')
					$key = str_replace( $table_name . '.', '', $raw_key );
					
					$parts = explode( '.', $raw_key );
					
					// Xử lý Dynamic Link cho cột Relation (vd: teacher_id.url)
					if ( count( $parts ) > 1 && end( $parts ) === 'url' ) {
						$field_name = $parts[ count($parts) - 2 ];
						if ( isset( $context[ $field_name ] ) && is_string( $context[ $field_name ] ) ) {
							$rel_val = $context[ $field_name ];
							$decoded = json_decode($rel_val, true);
							if ( is_array($decoded) && !empty($decoded[0]['url']) ) {
								return esc_url( $decoded[0]['url'] );
							}
						}
					}
					
					// Xử lý Value thông thường
					if ( isset( $context[ $key ] ) ) {
						$val = $context[ $key ];
						// Nếu là array (vd: Relation chưa parse url), xuất dạng chuỗi để tránh lỗi Array to string conversion
						if ( is_array( $val ) ) {
							return esc_html( wp_json_encode( $val ) );
						}
						// Nếu là chuỗi JSON từ relation (ví dụ chỉ gọi [teacher_id])
						if ( is_string( $val ) && strpos( $val, '[{"' ) === 0 ) {
							$decoded = json_decode( $val, true );
							if ( is_array( $decoded ) && ! empty( $decoded[0]['label'] ) ) {
								// Mặc định xuất label nếu không chỉ định .url hay .id
								$labels = array_column( $decoded, 'label' );
								return esc_html( implode( ', ', $labels ) );
							}
						}
						return esc_html( $val );
					}
					
					// Giữ nguyên nếu không khớp
					return $matches[0];
				}, $html );
			}
		}

		return $html;
	}

	/**
	 * Create the Virtual Template file dynamically if it doesn't exist
	 */
	private function create_virtual_template_file( $filepath ) {
		$content = '<?php
/**
 * Ska Smart Virtual Template
 * This file is automatically generated. Do not edit directly.
 */

if ( ! defined( \'ABSPATH\' ) ) {
	exit;
}

global $ska_current_template_id;
$wrapper = \Ska_No_Code_Design\Theme_Builder\Ska_Virtual_Wrapper::get_instance();

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( \'charset\' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( \'ska-builder ska-virtual-app\' ); ?> x-data>

<?php wp_body_open(); ?>

	<?php
	// 1. Render Global Header
	$wrapper->render_location( \'header\' );
	?>

	<main id="primary" class="ska-main-content">
		<?php 
		// 2. Render Main Route Content (Single, Archive, 404)
		if ( ! empty( $ska_current_template_id ) ) {
			$wrapper->render_template_by_id( $ska_current_template_id );
		} else {
			// Fallback: Render default content if no main template is defined
			echo \'<div class="ska-container mx-auto p-4">\';
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					the_content();
				}
			}
			echo \'</div>\';
		}
		?>
	</main>

	<?php
	// 3. Render Global Footer
	$wrapper->render_location( \'footer\' );
	?>

<?php wp_footer(); ?>

</body>
</html>';

		file_put_contents( $filepath, $content );
	}
}
