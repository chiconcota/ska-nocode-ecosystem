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

		global $ska_access_denied;
		if ( ! empty( $ska_access_denied ) ) {
			$wp->query_vars['error'] = '';
			status_header( 403 );
			return;
		}

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
			global $ska_current_portal, $ska_access_denied;
			if ( ! empty( $ska_current_portal ) || ! empty( $ska_access_denied ) ) {
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
					$settings['app_id']     = isset( $schema['__table_info']['app_id'] ) ? $schema['__table_info']['app_id'] : ''; // Attach app_id
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

		$has_access = false;
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_roles = (array) $user->roles;
			
			// Check for intersection using normalized lowercase roles
			if ( count( array_intersect( $allowed_roles_lower, $user_roles ) ) > 0 ) {
				$has_access = true;
			}
			
			// Administrator override
			if ( in_array( 'administrator', $user_roles, true ) ) {
				$has_access = true;
			}
		}

		if ( ! $has_access ) {
			// Phân giải URL chuyển hướng (Cấp Table hoặc cấp Workspace)
			$redirect_url = '';
			if ( ! empty( $portal_config['unauthorized_redirect_url'] ) ) {
				$redirect_url = $portal_config['unauthorized_redirect_url'];
			} else {
				$app_id = isset( $portal_config['app_id'] ) ? $portal_config['app_id'] : '';
				if ( ! empty( $app_id ) ) {
					global $wpdb;
					$table_apps = $wpdb->prefix . 'ska_data_sys_apps';
					// Đảm bảo bảng apps tồn tại trước khi select
					$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_apps ) );
					if ( $table_exists === $table_apps ) {
						$redirect_url = $wpdb->get_var( $wpdb->prepare( "SELECT unauthorized_redirect_url FROM {$table_apps} WHERE app_id = %s", $app_id ) );
					}
				}
			}

			// Lớp filter mở rộng của bên thứ 3
			$redirect_url = apply_filters( 'ska_access_denied_redirect_url', $redirect_url );

			if ( ! empty( $redirect_url ) ) {
				global $wp;
				$current_url = home_url( add_query_arg( array(), $wp->request ) );
				$redirect_target = add_query_arg( 'redirect_to', urlencode( $current_url ), $redirect_url );
				$redirect_target = apply_filters( 'ska_auth_redirect_url', $redirect_target );
				wp_redirect( $redirect_target );
				exit;
			}

			// Không có URL chuyển hướng -> Bật cờ lỗi phân quyền để Virtual Wrapper xử lý render template 403
			global $ska_access_denied;
			$ska_access_denied = true;
		}
	}

	/**
	 * Render the default beautiful 403 Forbidden / Access Denied page
	 */
	public static function render_default_403_page() {
		status_header( 403 );
		nocache_headers();
		
		$login_url = wp_login_url( home_url( $_SERVER['REQUEST_URI'] ) );
		$home_url  = home_url();
		$site_name = get_bloginfo( 'name' );
		
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php esc_html_e( 'Access Denied - 403 Forbidden', 'ska-no-code-design' ); ?></title>
			<script src="https://cdn.tailwindcss.com"></script>
			<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
			<style>
				body {
					font-family: 'Outfit', sans-serif;
				}
				.dark-glass {
					background: rgba(15, 23, 42, 0.6);
					backdrop-filter: blur(16px);
					-webkit-backdrop-filter: blur(16px);
					border: 1px solid rgba(255, 255, 255, 0.08);
				}
			</style>
		</head>
		<body class="bg-gradient-to-tr from-slate-900 via-slate-800 to-indigo-950 min-h-screen flex items-center justify-center p-4 overflow-hidden relative">
			<!-- Decorative glowing circles -->
			<div class="absolute w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl -top-20 -left-20 animate-pulse"></div>
			<div class="absolute w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl -bottom-20 -right-20 animate-pulse" style="animation-duration: 8s;"></div>

			<div class="dark-glass max-w-md w-full rounded-2xl shadow-2xl p-8 text-center relative z-10 text-white animate-[fadeIn_0.5s_ease-out]">
				<!-- Shield Icon with Animation -->
				<div class="mx-auto w-24 h-24 mb-6 rounded-full bg-red-500/10 flex items-center justify-center border border-red-500/30 shadow-[0_0_50px_rgba(239,68,68,0.15)] relative">
					<div class="absolute inset-0 rounded-full border border-red-500/50 animate-ping opacity-25"></div>
					<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
					</svg>
				</div>

				<h1 class="text-4xl font-extrabold tracking-tight mb-2 bg-gradient-to-r from-red-400 to-amber-300 bg-clip-text text-transparent">
					403
				</h1>
				<h2 class="text-xl font-bold mb-4 text-slate-100">
					<?php esc_html_e( 'Access Denied', 'ska-no-code-design' ); ?>
				</h2>
				
				<p class="text-sm text-slate-400 mb-8 leading-relaxed">
					<?php 
					if ( is_user_logged_in() ) {
						esc_html_e( 'Your account does not have sufficient role permissions to access this Portal.', 'ska-no-code-design' );
					} else {
						esc_html_e( 'This page is protected. You must sign in with an authorized account to view it.', 'ska-no-code-design' );
					}
					?>
				</p>

				<div class="flex flex-col gap-3">
					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( $login_url ); ?>" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-indigo-500/20 transition duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
								<path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
							</svg>
							<?php esc_html_e( 'Login to Access', 'ska-no-code-design' ); ?>
						</a>
					<?php endif; ?>

					<a href="<?php echo esc_url( $home_url ); ?>" class="w-full bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white font-semibold py-3 px-4 rounded-xl border border-slate-700/50 transition duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
							<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
						</svg>
						<?php printf( esc_html__( 'Back to %s', 'ska-no-code-design' ), esc_html( $site_name ) ); ?>
					</a>
				</div>

				<div class="mt-8 pt-6 border-t border-slate-800/60 text-slate-500 text-xs flex justify-between items-center">
					<span><?php echo esc_html( $site_name ); ?></span>
					<span>•</span>
					<span><?php esc_html_e( 'Forbidden Resource', 'ska-no-code-design' ); ?></span>
				</div>
			</div>
		</body>
		</html>
		<?php
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


}

