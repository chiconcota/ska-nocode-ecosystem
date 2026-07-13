<?php
/**
 * Skaaa Theme Builder REST API Controller
 *
 * @package Skaaa_No_Code_Design\Theme_Builder
 */

namespace Skaaa_No_Code_Design\Theme_Builder;

defined( 'ABSPATH' ) || exit;

class Skaaa_Theme_Builder_API {

	/**
	 * Instance of the class
	 */
	private static $instance = null;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes
	 */
	public function register_routes() {
		$namespace = 'skaaaaa-builder/v1';
		$base      = 'theme-templates';

		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_template' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Check permission
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get all theme templates
	 */
	public function get_templates() {
		if ( ! class_exists( '\Skaaa\Data\Core\Data_Fetcher' ) ) {
			return new \WP_Error( 'missing_dependency', 'Skaaa Data Pro is not active', array( 'status' => 500 ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_theme_templates';

		// Query templates
		$results = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY id DESC", ARRAY_A );

		$templates = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$templates[] = array(
					'id'          => (int) $row['id'],
					'title'       => $row['name'],
					'location'    => $row['location'],
					'organism_id' => (int) $row['organism_id'],
					'conditions'  => $row['conditions'], // Chuỗi JSON chứa điều kiện hiển thị
					'is_active'   => (int) $row['is_active'],
				);
			}
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $templates,
		) );
	}

	/**
	 * Save a theme template
	 */
	public function save_template( \WP_REST_Request $request ) {
		if ( ! class_exists( '\Skaaa\Data\Core\Data_Fetcher' ) ) {
			return new \WP_Error( 'missing_dependency', 'Skaaa Data Pro is not active', array( 'status' => 500 ) );
		}

		$body = $request->get_json_params();

		if ( empty( $body ) || empty( $body['title'] ) || empty( $body['location'] ) ) {
			return new \WP_Error( 'invalid_data', __( 'Missing required data (title or location)', 'skaaa-no-code-design' ), array( 'status' => 400 ) );
		}

		$table_name = 'skaaa_data_sys_theme_templates';
		$id         = isset( $body['id'] ) ? absint( $body['id'] ) : 0;

		$record_data = array(
			'name'        => sanitize_text_field( $body['title'] ),
			'location'    => sanitize_text_field( $body['location'] ),
			'organism_id' => isset( $body['organism_id'] ) ? absint( $body['organism_id'] ) : 0,
			'conditions'  => isset( $body['conditions'] ) ? wp_unslash( $body['conditions'] ) : '{}',
			'is_active'   => isset( $body['is_active'] ) ? (int) $body['is_active'] : 1,
		);

		if ( $id > 0 ) {
			// Update using Skaaa Data Pro Filter Pipeline
			$update_result = apply_filters( 'skaaa_data_update_record', false, $record_data, $table_name, array( 'id' => $id ) );
			if ( $update_result !== false ) {
				$result = $id; // Giữ lại ID để trả về Client
			} else {
				$result = false;
			}
		} else {
			// Insert using Skaaa Data Pro Filter Pipeline
			$result = apply_filters( 'skaaa_data_insert_record', false, $record_data, $table_name );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $id && $result ) {
			$id = $result; // new ID
		} elseif ( ! $result ) {
			return new \WP_Error( 'db_error', __( 'Unable to save record to database.', 'skaaa-no-code-design' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Saved Theme Template successfully.', 'skaaa-no-code-design' ),
			'id'      => $id,
		) );
	}

	/**
	 * Delete a theme template
	 */
	public function delete_template( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid ID.', 'skaaa-no-code-design' ), array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_theme_templates';
		$table_organisms = $wpdb->prefix . 'skaaa_data_sys_organisms';

		// 1. Lấy organism_id liên kết từ template trước khi thực hiện xóa
		$template = $wpdb->get_row( $wpdb->prepare( "SELECT organism_id FROM {$table_name} WHERE id = %d", $id ), ARRAY_A );

		// 2. Tiến hành xóa template
		$deleted = $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

		if ( false === $deleted ) {
			return new \WP_Error( 'db_error', __( 'Record cannot be deleted.', 'skaaa-no-code-design' ), array( 'status' => 500 ) );
		}

		// 3. Tự động xóa Organism liên kết để tránh tích lũy dữ liệu rác trong database
		if ( ! empty( $template ) && ! empty( $template['organism_id'] ) ) {
			$wpdb->delete( $table_organisms, array( 'id' => $template['organism_id'] ), array( '%d' ) );
			
			// Làm mới (Flush) JSON Cache cho Editor sau khi xóa organism để dropdown tải đúng
			if ( class_exists( '\Skaaa\Design\Api\Organisms_API' ) ) {
				\Skaaa\Design\Api\Organisms_API::get_instance()->export_physical_cache();
			}
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Deleted Theme Template and Organism link successfully.', 'skaaa-no-code-design' ),
		) );
	}
}
