<?php
/**
 * Skaaa Data Pro REST API
 */

namespace Skaaa\Data\Api;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Skaaa\Data\Core\Data_Fetcher;

defined('ABSPATH') || exit;

class Rest_Api {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'skaaa-data/v1', '/portal/(?P<table>[a-zA-Z0-9_-]+)/rows', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_portal_rows' ),
			'permission_callback' => array( $this, 'check_portal_permissions' ),
			'args'                => array(
				'page' => array(
					'type'    => 'integer',
					'default' => 1,
					'minimum' => 1,
					'sanitize_callback' => 'absint',
				),
				'per_page' => array(
					'type'    => 'integer',
					'default' => 100, // Theo yêu cầu, mặc định 100
					'minimum' => 1,
					'maximum' => 500,
					'sanitize_callback' => 'absint',
				),
				'filter_field' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
				'filter_val' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'filter_op' => array(
					'type'              => 'string',
					'default'           => 'like',
					'sanitize_callback' => 'sanitize_key',
				),
				'orderby' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
				'order' => array(
					'type'              => 'string',
					'default'           => 'DESC',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		register_rest_route( 'skaaa-data/v1', '/portal/(?P<table>[a-zA-Z0-9_-]+)/rows/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_portal_row' ),
			'permission_callback' => array( $this, 'check_portal_permissions' ),
		) );

		register_rest_route( 'skaaa-data/v1', '/scripts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_scripts_list' ),
				'permission_callback' => array( $this, 'check_scripts_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_script' ),
				'permission_callback' => array( $this, 'check_scripts_permissions' ),
			)
		) );
	}

	/**
	 * Check permission to fetch scripts list (allow editors and admins).
	 */
	public function check_scripts_permissions( WP_REST_Request $request ) {
		return current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Retrieve active scripts from Flat Table database.
	 */
	public function get_scripts_list( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_scripts';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
			return new WP_REST_Response( array( 'success' => true, 'data' => array() ), 200 );
		}

		$scripts = $wpdb->get_results(
			"SELECT `id`, `script_id`, `name`, `type`, `location` FROM `{$table_name}` WHERE `status` = 1 ORDER BY `name` ASC",
			ARRAY_A
		);

		return new WP_REST_Response( array(
			'success' => true,
			'data'    => $scripts ? $scripts : array()
		), 200 );
	}

	/**
	 * Create a new script in Scripts Library from Gutenberg editor.
	 */
	public function create_script( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_scripts';

		$script_id = sanitize_key( $request->get_param( 'script_id' ) );
		$name      = sanitize_text_field( $request->get_param( 'name' ) );
		$type      = sanitize_text_field( $request->get_param( 'type' ) );
		$content   = $request->get_param( 'content' ); // Keep raw JS/CSS/HTML code
		$location  = sanitize_text_field( $request->get_param( 'location' ) );

		if ( empty( $script_id ) || empty( $name ) || empty( $type ) ) {
			return new WP_Error( 'skaaa_data_missing_fields', 'Missing required configuration fields.', array( 'status' => 400 ) );
		}

		// Verify unique ID
		$duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table_name}` WHERE `script_id` = %s", $script_id ) );
		if ( $duplicate > 0 ) {
			return new WP_Error( 'skaaa_data_duplicate_id', 'Script ID already exists.', array( 'status' => 400 ) );
		}

		$data = array(
			'script_id'      => $script_id,
			'name'           => $name,
			'type'           => $type,
			'content'        => $content,
			'location'       => $location,
			'load_condition' => 'block_only', // default to block_only
			'conditions'     => null,
			'status'         => 1,
		);

		$result = $wpdb->insert( $table_name, $data );

		if ( ! $result ) {
			return new WP_Error( 'skaaa_data_insert_failed', 'Failed to save script to database.', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( array(
			'success' => true,
			'message' => 'Script created successfully.',
			'id'      => $wpdb->insert_id,
			'script'  => $data
		), 200 );
	}

	/**
	 * Kiểm tra quyền truy cập dựa trên cấu hình Smart Object
	 */
	public function check_portal_permissions( WP_REST_Request $request ) {
		$table_name = $request['table'];
		
		// Đọc Dictionary
		$all_dict = get_option( 'skaaa_data_dictionary', array() );
		global $wpdb;
		
		// Tìm table_dict (hỗ trợ cả có và không có prefix, tên bảng thô, và portal slug)
		$table_dict = null;
		$matched_table = null;

		// 1. Thử tìm trực tiếp hoặc theo prefix mặc định
		if ( isset( $all_dict[ $table_name ] ) ) {
			$table_dict = $all_dict[ $table_name ];
			$matched_table = $table_name;
		} else {
			$with_prefix    = strpos( $table_name, $wpdb->prefix ) === 0 ? $table_name : $wpdb->prefix . $table_name;
			$without_prefix = str_replace( $wpdb->prefix, '', $table_name );
			
			if ( isset( $all_dict[ $with_prefix ] ) ) {
				$table_dict = $all_dict[ $with_prefix ];
				$matched_table = $with_prefix;
			} elseif ( isset( $all_dict[ $without_prefix ] ) ) {
				$table_dict = $all_dict[ $without_prefix ];
				$matched_table = $without_prefix;
			}
		}

		// 2. Thử tìm bằng cách thêm prefix 'skaaa_data_' cho tên bảng thô (ví dụ: 'revenue' -> 'skaaa_data_revenue')
		if ( ! $table_dict ) {
			$skaaa_table_raw = 'skaaa_data_' . $table_name;
			$skaaa_table_prefix = $wpdb->prefix . $skaaa_table_raw;
			if ( isset( $all_dict[ $skaaa_table_prefix ] ) ) {
				$table_dict = $all_dict[ $skaaa_table_prefix ];
				$matched_table = $skaaa_table_prefix;
			} elseif ( isset( $all_dict[ $skaaa_table_raw ] ) ) {
				$table_dict = $all_dict[ $skaaa_table_raw ];
				$matched_table = $skaaa_table_raw;
			}
		}

		// 3. Thử tìm theo Portal Slug (ví dụ: 'revenue-api')
		if ( ! $table_dict ) {
			foreach ( $all_dict as $real_table => $dict ) {
				if ( isset( $dict['__table_info']['portal_settings']['slug'] ) && $dict['__table_info']['portal_settings']['slug'] === $table_name ) {
					$table_dict = $dict;
					$matched_table = $real_table;
					break;
				}
			}
		}

		if ( ! $table_dict ) {
			return new WP_Error( 'skaaa_data_not_found', 'Table not found in dictionary.', array( 'status' => 404 ) );
		}

		// Gán lại tên bảng thật để các xử lý sau (như query dữ liệu) dùng đúng bảng phẳng MySQL
		$request->set_param( 'table', $matched_table );


		
		// Không có thông tin portal -> Mặc định là không cho phép gọi qua API Portal
		if ( ! empty( $table_dict['__table_info']['is_portal'] ) ) {
            // Support older config style: is_portal = true
        } else if ( ! isset( $table_dict['__table_info']['portal_settings'] ) ) {
			return new WP_Error( 'skaaa_data_forbidden', 'This table is not configured as an App Portal.', array( 'status' => 403 ) );
		}

		$portal_settings = isset($table_dict['__table_info']['portal_settings']) ? $table_dict['__table_info']['portal_settings'] : array();
        
        $is_active = isset($portal_settings['active']) ? $portal_settings['active'] : false;
        
        if ( isset($table_dict['__table_info']['is_portal']) && $table_dict['__table_info']['is_portal'] == '1' ) {
             $is_active = true;
        }

		// Portal đang bị vô hiệu hóa
		if ( empty( $is_active ) || $is_active === 'false' ) {
			return new WP_Error( 'skaaa_data_inactive', 'This portal is currently inactive.', array( 'status' => 403 ) );
		}

		// Nếu Roles rỗng => Public
		if ( empty( $portal_settings['roles'] ) || ! is_array( $portal_settings['roles'] ) ) {
			return true;
		}

		// Public Frontend Page Check (Case-insensitive)
		$allowed_roles_lower = array_map( 'strtolower', (array) $portal_settings['roles'] );
		if ( count( array_intersect( array( 'public', 'guest', 'all', '' ), $allowed_roles_lower ) ) > 0 ) {
			return true; // Anyone can access!
		}

		// Nếu có Roles => Phải đăng nhập
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'skaaa_data_unauthorized', 'You must be logged in to access this portal data.', array( 'status' => 401 ) );
		}

		$user = wp_get_current_user();
		
		// Admin luôn có quyền
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return true;
		}

		// Kiểm tra User Role có nằm trong danh sách Allowed Roles không
		$allowed_roles = array_map( 'sanitize_key', $portal_settings['roles'] );
		$has_role = false;
		foreach ( $user->roles as $role ) {
			if ( in_array( $role, $allowed_roles, true ) ) {
				$has_role = true;
				break;
			}
		}

		if ( ! $has_role ) {
			return new WP_Error( 'skaaa_data_forbidden_role', 'You do not have the required role to access this portal.', array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Xử lý trả về Data & Meta
	 */
	public function get_portal_rows( WP_REST_Request $request ) {
		global $wpdb;
		
		$table_name = $request['table'];
		
		// Đảm bảo có prefix
		if ( strpos( $table_name, $wpdb->prefix ) !== 0 ) {
			$table_name = $wpdb->prefix . $table_name;
		}

		// Tham số truy vấn
		$args = array(
			'page'         => $request->get_param( 'page' ),
			'per_page'     => $request->get_param( 'per_page' ),
			'filter_field' => $request->get_param( 'filter_field' ),
			'filter_val'   => $request->get_param( 'filter_val' ),
			'filter_op'    => $request->get_param( 'filter_op' ),
			'orderby'      => $request->get_param( 'orderby' ),
			'order'        => $request->get_param( 'order' ),
		);

		// Xóa các tham số trống để tránh lỗi query
		$args = array_filter( $args, function( $val ) {
			return $val !== null && $val !== '';
		});

		$per_page = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 100;

		$total_items = Data_Fetcher::count_table_rows( $table_name, $args );
		$total_pages = ceil( $total_items / $per_page );
		
		$rows = Data_Fetcher::get_table_rows( $table_name, $args, $per_page );

		$response_data = array(
			'success' => true,
			'data'    => $rows,
			'meta'    => array(
				'total_items'  => $total_items,
				'total_pages'  => $total_pages,
				'current_page' => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
				'per_page'     => $per_page,
			)
		);

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Xử lý xóa dòng từ Portal Frontend REST API
	 */
	public function delete_portal_row( WP_REST_Request $request ) {
		global $wpdb;
		
		$table_name = $request['table'];
		$id = absint( $request['id'] );
		
		// Đảm bảo có prefix
		if ( strpos( $table_name, $wpdb->prefix ) !== 0 ) {
			$table_name = $wpdb->prefix . $table_name;
		}

		if ( ! class_exists( '\Skaaa\Data\Core\Database_Engine' ) ) {
			return new WP_REST_Response( array(
				'success' => false,
				'message' => __( 'System error: Data engine is not ready.', 'skaaa-data-pro' )
			), 500 );
		}

		$engine = \Skaaa\Data\Core\Database_Engine::get_instance();
		$result = $engine->delete_row( $table_name, $id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array(
				'success' => false,
				'message' => $result->get_error_message()
			), 400 );
		}

		if ( ! $result ) {
			return new WP_REST_Response( array(
				'success' => false,
				'message' => __( 'The line cannot be deleted or the line does not exist.', 'skaaa-data-pro' )
			), 400 );
		}

		return new WP_REST_Response( array(
			'success' => true,
			'message' => __( 'Successfully deleted line.', 'skaaa-data-pro' )
		), 200 );
	}
}
