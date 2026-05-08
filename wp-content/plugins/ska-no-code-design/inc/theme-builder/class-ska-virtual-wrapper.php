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

		if ( is_404() ) {
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
			$ska_current_template_id = ! empty( $matched_template ) ? $matched_template['id'] : 0;

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

		// Simple Condition Matching Logic
		// TODO: (Milestone 4) Implement advanced condition parsing for custom & app_layout routing
		// For now, we just return the most recently created template that matches the location
		// Defaulting to "Toàn trang" behavior if conditions are empty.
		
		foreach ( $templates as $template ) {
			$conditions_json = $template['conditions'];
			
			// If no conditions are set, it's a global template for this location
			if ( empty( $conditions_json ) ) {
				return $template;
			}

			$conditions = json_decode( $conditions_json, true );
			if ( ! is_array( $conditions ) ) {
				return $template; // Fallback if invalid JSON
			}

			// Lógica kiểm tra conditions có thể mở rộng ở đây
			// Ví dụ: kiểm tra is_page( $conditions['page_id'] )
			// Tạm thời return luôn template đầu tiên (coi như match)
			return $template;
		}

		return null;
	}

	/**
	 * Get HTML content of an Organism
	 */
	private function get_organism_html( $organism_id ) {
		if ( ! class_exists( '\Ska\Design\Api\Organisms_API' ) ) {
			// Fallback direct query if API class is not loaded
			global $wpdb;
			$org_table = $wpdb->prefix . 'ska_data_sys_organisms';
			$html = $wpdb->get_var( $wpdb->prepare( "SELECT html_content FROM {$org_table} WHERE id = %d", $organism_id ) );
			return $html ? do_blocks( $html ) : '';
		}

		$html_array = \Ska\Design\Api\Organisms_API::get_bulk_html( array( $organism_id ) );
		return isset( $html_array[ $organism_id ] ) ? do_blocks( $html_array[ $organism_id ] ) : '';
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
<body <?php body_class( \'ska-builder ska-virtual-app\' ); ?>>

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
