<?php
/**
 * Shadow Scratchpad API
 * Quản lý các ID bài viết ảo (Shadow CPT) để Gutenberg có thể khởi chạy bên trong Iframe.
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined('ABSPATH') || exit;

class API_Shadow_Scratchpad
{
	private static $instance = null;

	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		add_action('init', array($this, 'register_scratchpad_cpt'));
		add_action('rest_api_init', array($this, 'register_routes'));
		add_action('admin_head', array($this, 'disable_beforeunload_for_scratchpad'));
	}

	/**
	 * Vô hiệu hóa event beforeunload cho Scratchpad để tránh hiện popup "Rời khỏi trang web"
	 */
	public function disable_beforeunload_for_scratchpad()
	{
		$is_scratchpad = false;

		// Method 1: Check using get_current_screen
		if (function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && $screen->post_type === 'ska_scratchpad') {
				$is_scratchpad = true;
			}
		}

		// Method 2: Check using global $post
		if (!$is_scratchpad) {
			global $post;
			if ($post && $post->post_type === 'ska_scratchpad') {
				$is_scratchpad = true;
			}
		}

		// Method 3: Check using request parameters
		if (!$is_scratchpad) {
			$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
			if ($post_id > 0) {
				$post_type = get_post_type($post_id);
				if ($post_type === 'ska_scratchpad') {
					$is_scratchpad = true;
				}
			} elseif (isset($_GET['post_type']) && $_GET['post_type'] === 'ska_scratchpad') {
				$is_scratchpad = true;
			}
		}

		if ($is_scratchpad) {
			?>
			<script id="ska-disable-beforeunload">
			(function() {
				// Intercept addEventListener
				const originalAddEventListener = window.addEventListener;
				window.addEventListener = function(type, listener, options) {
					if (type === 'beforeunload') {
						return;
					}
					return originalAddEventListener.apply(this, arguments);
				};

				// Intercept window.onbeforeunload
				Object.defineProperty(window, 'onbeforeunload', {
					get: function() { return null; },
					set: function(value) {
						// Silently ignore
					},
					configurable: true
				});
			})();
			</script>
			<?php
		}
	}

	/**
	 * Đăng ký Custom Post Type ẩn dành cho Scratchpad
	 */
	public function register_scratchpad_cpt()
	{
		register_post_type('ska_scratchpad', array(
			'label'               => 'Scratchpad',
			'public'              => false,
			'show_ui'             => true, // Phải true để Gutenberg có thể hoạt động
			'show_in_menu'        => false,
			'show_in_rest'        => true, // Bắt buộc để load Gutenberg
			'supports'            => array('title', 'editor', 'custom-fields'),
			'capabilities' => array(
				'create_posts' => 'do_not_allow', // Không cho tạo tay qua wp-admin
			),
			'map_meta_cap' => true
		));
	}

	public function register_routes()
	{
		register_rest_route('ska-builder/v1', '/scratchpad/create', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array($this, 'create_scratchpad'),
			'permission_callback' => array($this, 'check_permission'),
		));

		register_rest_route('ska-builder/v1', '/scratchpad/destroy', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array($this, 'destroy_scratchpad'),
			'permission_callback' => array($this, 'check_permission'),
		));
	}

	public function check_permission()
	{
		return current_user_can('edit_posts');
	}

	/**
	 * Tạo ID phái sinh (Shadow Post) và nạp sẵn nội dung HTML
	 */
	public function create_scratchpad(\WP_REST_Request $request)
	{
		$html_content = $request->get_param('content');

		$post_id = wp_insert_post(array(
			'post_type'    => 'ska_scratchpad',
			'post_title'   => 'Temp Scratchpad ' . time(),
			'post_content' => $html_content ? wp_kses_post($html_content) : '',
			'post_status'  => 'draft',
		));

		if (is_wp_error($post_id)) {
			return new \WP_Error('create_failed', __( 'Unable to create Scratchpad.', 'ska-no-code-design' ), array('status' => 500));
		}

		// Tạo URL Iframe để Frontend nhúng vào
		$iframe_url = get_edit_post_link($post_id, 'raw');

		return rest_ensure_response(array(
			'success'    => true,
			'post_id'    => $post_id,
			'iframe_url' => $iframe_url
		));
	}

	/**
	 * Hủy ID ảo sau khi Frontend đóng Iframe
	 */
	public function destroy_scratchpad(\WP_REST_Request $request)
	{
		$post_id = $request->get_param('post_id');

		if (!$post_id) {
			return new \WP_Error('missing_id', __( 'Missing ID.', 'ska-no-code-design' ), array('status' => 400));
		}

		$post = get_post($post_id);
		if (!$post || $post->post_type !== 'ska_scratchpad') {
			return new \WP_Error('invalid_id', __( 'Invalid ID.', 'ska-no-code-design' ), array('status' => 400));
		}

		// Xóa vĩnh viễn không qua thùng rác
		$deleted = wp_delete_post($post_id, true);

		if (!$deleted) {
			return new \WP_Error('delete_failed', __( 'Scratchpad cannot be removed.', 'ska-no-code-design' ), array('status' => 500));
		}

		return rest_ensure_response(array(
			'success' => true,
			'message' => __( 'Scratchpad canceled successfully.', 'ska-no-code-design' )
		));
	}
}
