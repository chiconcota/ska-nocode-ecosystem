<?php
/**
 * Ska App Router (Virtual Router & Security Layer)
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined( 'ABSPATH' ) || exit;

class Ska_App_Router {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Register rewrite rules and tags
		add_action( 'init', array( $this, 'register_rewrites' ) );
		
		// Lắng nghe sự kiện lưu cấu hình Portal để flush rewrite rules
		add_action( 'ska_data_portal_settings_updated', array( $this, 'flush_rewrites' ) );

		// Đánh chặn request siêu sớm (Parasite Architecture)
		add_action( 'parse_request', array( $this, 'parasite_dispatcher' ), 1 );

		// Prevent WP from forcing 404 when no posts are found for our custom routes
		add_filter( 'pre_handle_404', array( $this, 'prevent_404' ), 10, 2 );

		// Ép chết WP_Query nếu đang ở App Portal
		add_filter( 'posts_pre_query', array( $this, 'short_circuit_wp_query' ), 10, 2 );

		// Setup frontend context (Alpine variables)
		add_action( 'wp_head', array( $this, 'inject_frontend_context' ), 5 );

		// Inject portal footer scripts
		add_action( 'wp_footer', array( $this, 'inject_portal_footer_scripts' ), 100 );
	}

	/**
	 * Prevent WordPress from forcing 404 on portal routes
	 */
	public function prevent_404( $preempt, $wp_query ) {
		if ( ! empty( get_query_var( 'ska_portal' ) ) ) {
			global $wp;
			$wp_query->is_404 = false;
			status_header( 200 );
			return true; // Short-circuit handle_404
		}
		return $preempt;
	}

	/**
	 * Register custom rewrite rules for the App Portal.
	 */
	public function register_rewrites() {
		add_rewrite_tag( '%ska_portal%', '([^&]+)' );
		add_rewrite_tag( '%ska_id%', '([a-zA-Z0-9-]+)' );
		
		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( is_array( $dictionary ) ) {
			foreach ( $dictionary as $table_name => $schema ) {
				if ( ! empty( $schema['__table_info']['portal_settings'] ) ) {
					$settings = $schema['__table_info']['portal_settings'];
					if ( ! empty( $settings['active'] ) && ! empty( $settings['slug'] ) ) {
						$slug = sanitize_title( $settings['slug'] );
						// Detail view or Create view: /bai-hoc/123 or /bai-hoc/create
						add_rewrite_rule( '^' . $slug . '/([a-zA-Z0-9-]+)/?$', 'index.php?ska_portal=' . $slug . '&ska_id=$matches[1]', 'top' );
						// List view: /bai-hoc
						add_rewrite_rule( '^' . $slug . '/?$', 'index.php?ska_portal=' . $slug, 'top' );
					}
				}
			}
		}

		// Backward compatibility fallback
		add_rewrite_rule( '^portal/([a-z0-9-]+)/?$', 'index.php?ska_portal=$matches[1]', 'top' );

		// Auto-flush rewrite rules once
		if ( ! get_option( 'ska_portal_rewrites_flushed_v2' ) ) {
			flush_rewrite_rules();
			update_option( 'ska_portal_rewrites_flushed_v2', true );
		}
	}

	/**
	 * Safe Flush Rewrite Rules triggered by Data Pro
	 */
	public function flush_rewrites() {
		$this->register_rewrites();
		flush_rewrite_rules();
	}

	/**
	 * Auth Middleware: Intercept portal requests, load config, and enforce role checks.
	 */
	public function parasite_dispatcher( $wp ) {
		if ( ! isset( $wp->query_vars['ska_portal'] ) ) {
			return; // Not a portal request
		}

		$portal_slug = $wp->query_vars['ska_portal'];

		// Look up the portal config from Data Pro
		$portal_config = $this->get_portal_config( $portal_slug );

		if ( ! $portal_config || empty( $portal_config['active'] ) ) {
			// Portal not found or inactive, let WP handle it normally (probably 404)
			return;
		}

		// Security Check: Roles & Custom Redirect
		$this->enforce_security( $portal_config );

		// If passed, store config in a global context for Virtual Wrapper and other components
		global $ska_current_portal;
		$ska_current_portal = $portal_config;

		// Tránh lỗi 404 từ hàm handle_404 của WP
		$wp->query_vars['error'] = '';
		status_header( 200 );
	}

	/**
	 * Short-circuit WP_Query for Portal Requests to save CPU
	 */
	public function short_circuit_wp_query( $posts, $query ) {
		// Chỉ chặn Main Query nếu nó là yêu cầu Portal hợp lệ
		if ( $query->is_main_query() && get_query_var( 'ska_portal' ) ) {
			global $ska_current_portal;
			if ( ! empty( $ska_current_portal ) ) {
				// Ép WP_Query trả về mảng rỗng để không tốn chi phí query DB
				$query->is_404 = false; // Ngăn WP set is_404 = true khi mảng posts rỗng
				$query->is_home = false;
				$query->is_archive = false;
				$query->is_singular = false;
				return array(); 
			}
		}
		return $posts;
	}

	/**
	 * Retrieve portal configuration from Ska Data Pro dictionary.
	 * 
	 * @param string $slug
	 * @return array|false
	 */
	private function get_portal_config( $slug ) {
		// Fetch dictionary from options
		$dictionary = get_option( 'ska_data_dictionary', array() );
		
		if ( empty( $dictionary ) || ! is_array( $dictionary ) ) {
			return false;
		}
		
		foreach ( $dictionary as $table_name => $schema ) {
			if ( ! empty( $schema['__table_info']['portal_settings'] ) ) {
				$settings = $schema['__table_info']['portal_settings'];
				if ( ! empty( $settings['active'] ) && $settings['slug'] === $slug ) {
					// Found the matching table portal
					$settings['table_name'] = $table_name; // Attach table name for context
					return $settings;
				}
			}
		}

		return false;
	}

	/**
	 * Enforce security based on allowed roles.
	 * 
	 * @param array $portal_config
	 */
	private function enforce_security( $portal_config ) {
		$allowed_roles = isset( $portal_config['roles'] ) ? $portal_config['roles'] : array();

		// Normalize roles to array
		if ( is_string( $allowed_roles ) ) {
			$allowed_roles = array_map( 'trim', explode( ',', $allowed_roles ) );
		}

		if ( empty( $allowed_roles ) || ! is_array( $allowed_roles ) ) {
			wp_die( 'App Portal configuration error: No roles assigned.', 'Configuration Error', array( 'response' => 403 ) );
		}

		// Normalize roles to lowercase for case-insensitive check
		$allowed_roles_lower = array_map( 'strtolower', $allowed_roles );

		// Public Frontend Page Check
		if ( count( array_intersect( array( 'public', 'guest', 'all', '' ), $allowed_roles_lower ) ) > 0 ) {
			return; // Anyone can access! No need to check login.
		}

		if ( ! is_user_logged_in() ) {
			// Not logged in -> Redirect to login page and return back to portal
			global $wp;
			$current_url = home_url( add_query_arg( array(), $wp->request ) );

			if ( ! empty( $portal_config['unauthorized_redirect_url'] ) ) {
				$redirect_base = esc_url_raw( $portal_config['unauthorized_redirect_url'] );
				$login_url = add_query_arg( 'redirect_to', urlencode( $current_url ), $redirect_base );
			} else {
				$login_url = wp_login_url( $current_url );
			}
			
			$login_url = apply_filters( 'ska_auth_redirect_url', $login_url );
			
			wp_redirect( $login_url );
			exit;
		}

		$user = wp_get_current_user();
		$user_roles = (array) $user->roles;
		
		$has_access = false;
		
		// Check for intersection using normalized lowercase roles
		if ( count( array_intersect( $allowed_roles_lower, $user_roles ) ) > 0 ) {
			$has_access = true;
		}
		
		// Administrator override
		if ( in_array( 'administrator', $user_roles, true ) ) {
			$has_access = true;
		}

		if ( ! $has_access ) {
			$access_denied_url = apply_filters( 'ska_access_denied_redirect_url', '' );
			if ( ! empty( $access_denied_url ) ) {
				wp_redirect( $access_denied_url );
				exit;
			}

			wp_die( 
				'Bạn không có đủ quyền (Role) truy cập vào App Portal này. Vui lòng liên hệ quản trị viên.', 
				'403 Forbidden', 
				array( 'response' => 403 ) 
			);
		}
	}

	/**
	 * Inject frontend context (Data, Config, Nonce) into wp_head for Alpine.js
	 */
	public function inject_frontend_context() {
		global $ska_current_portal;
		
		// Only output if this is a valid portal request
		if ( empty( $ska_current_portal ) ) {
			return;
		}

		$table_name = $ska_current_portal['table_name'];
		
		// Fetch schema columns
		$dictionary = get_option( 'ska_data_dictionary', array() );
		$schema = isset( $dictionary[ $table_name ] ) ? $dictionary[ $table_name ] : array();
		
		$columns = array();
		foreach ( $schema as $key => $val ) {
			if ( $key === '__table_info' ) continue;
			$columns[ $key ] = $val;
		}

		$current_id = (get_query_var( 'ska_id' ) && get_query_var( 'ska_id' ) !== 'create') ? absint( get_query_var( 'ska_id' ) ) : null;
		$current_data = null;
		
		if ( $current_id && class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
			$args = array(
				'filter_field' => 'id',
				'filter_op'    => 'eq',
				'filter_val'   => $current_id,
			);
			$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $table_name, $args, 1 );
			if ( ! empty( $rows ) ) {
				$current_data = $rows[0];
			}
		}

		$context = array(
			'portal'     => $ska_current_portal,
			'columns'    => $columns,
			'currentId'  => $current_id,
			'currentData'=> $current_data,
			'restUrl'    => esc_url_raw( rest_url( 'ska-data/v1/portal/' . $table_name . '/rows' ) ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
		);

		echo "<script id=\"ska-portal-context\">\n";
		echo "window.SkaPortalContext = " . wp_json_encode( $context ) . ";\n";
		echo "</script>\n";
	}

	/**
	 * Inject portal helper scripts (e.g. deleteRow) into footer
	 */
	public function inject_portal_footer_scripts() {
		global $ska_current_portal;
		
		// Only output if this is a valid portal request
		if ( empty( $ska_current_portal ) ) {
			return;
		}
		?>
		<script id="ska-portal-footer-scripts">
		function deleteRow(id, btnEl) {
			if (!id || !btnEl) return;
			
			const rowEl = btnEl.closest('.ska-organism-row');
			if (!rowEl) return;

			const context = window.SkaPortalContext;
			if (!context || !context.restUrl || !context.nonce) {
				alert('Lỗi: Hệ thống chưa được cấu hình đầy đủ để thực hiện hành động này.');
				return;
			}

			const deleteUrl = context.restUrl + '/' + id;

			rowEl.style.transition = 'all 0.5s ease';
			rowEl.style.opacity = '0.5';
			rowEl.style.pointerEvents = 'none';

			fetch(deleteUrl, {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': context.nonce,
					'Content-Type': 'application/json'
				}
			})
			.then(response => {
				if (!response.ok) {
					throw new Error('Yêu cầu xóa thất bại.');
				}
				return response.json();
			})
			.then(data => {
				if (data.success) {
					rowEl.style.opacity = '0';
					rowEl.style.height = '0';
					rowEl.style.paddingTop = '0';
					rowEl.style.paddingBottom = '0';
					rowEl.style.marginTop = '0';
					rowEl.style.marginBottom = '0';
					rowEl.style.borderWidth = '0';
					setTimeout(() => {
						rowEl.remove();
					}, 500);
				} else {
					rowEl.style.opacity = '1';
					rowEl.style.pointerEvents = 'auto';
					alert(data.message || 'Lỗi: Không thể xóa dòng.');
				}
			})
			.catch(error => {
				rowEl.style.opacity = '1';
				rowEl.style.pointerEvents = 'auto';
				alert(error.message || 'Lỗi kết nối hoặc hệ thống.');
			});
		}
		</script>
		<?php
	}
}

