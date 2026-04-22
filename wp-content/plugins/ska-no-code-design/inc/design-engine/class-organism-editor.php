<?php
/**
 * Shadow CPT Editor for Ska Organisms
 *
 * @package Ska_Builder_Design
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

class Organism_Editor {

	/**
	 * Instance of this class.
	 *
	 * @var Organism_Editor
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return Organism_Editor A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_shadow_cpt' ) );
		add_action( 'admin_init', array( $this, 'ensure_dummy_post_exists' ) );

		// Inject data into the REST response when Editor loads
		add_filter( 'rest_prepare_ska_organism_draft', array( $this, 'inject_organism_data' ), 10, 3 );
		
		// Intercept save after it updates the dummy post
		add_action( 'rest_after_insert_ska_organism_draft', array( $this, 'intercept_organism_save' ), 10, 3 );

		// Clean up the UI inside iframe
		add_action( 'admin_head', array( $this, 'inject_iframe_styles' ), 999 );
        add_action( 'admin_footer', array( $this, 'inject_iframe_js' ), 999 );
	}

	/**
	 * Register the Shadow Custom Post Type.
	 */
	public function register_shadow_cpt() {
		register_post_type( 'ska_organism_draft', array(
			'labels'              => array(
				'name'          => 'Ska Organism Draft',
				'singular_name' => 'Ska Organism Draft',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'supports'            => array( 'editor', 'custom-fields' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
		) );
	}

	/**
	 * Ensure exactly ONE dummy post exists to be used as the Editor shell.
	 */
	public function ensure_dummy_post_exists() {
		$dummy_post_id = get_option( 'ska_organism_dummy_post_id' );
		if ( ! $dummy_post_id || ! get_post( $dummy_post_id ) ) {
			$new_post_id = wp_insert_post( array(
				'post_title'  => 'Ska Dummy Editor',
				'post_type'   => 'ska_organism_draft',
				'post_status' => 'publish', // Publish to avoid auto-draft weirdness
			) );
			if ( ! is_wp_error( $new_post_id ) ) {
				update_option( 'ska_organism_dummy_post_id', $new_post_id );
			}
		}
	}

	/**
	 * Bơm dữ liệu thật từ Bảng Phẳng vào REST API để Gutenberg hiển thị.
	 */
	public function inject_organism_data( $response, $post, $request ) {
		// Editor load the data either via preloaded REST request (on page load) or AJAX.
        // During preload, $_GET['edit_organism'] is available. 
        // During AJAX, it might be in the referer.
		$organism_id = 0;
        
        if ( isset( $_GET['edit_organism'] ) ) {
            $organism_id = intval( $_GET['edit_organism'] );
        } else {
            $referer = wp_get_referer();
            if ( $referer && strpos( $referer, 'edit_organism=' ) !== false ) {
                $url_parts = wp_parse_url( $referer );
                if ( isset( $url_parts['query'] ) ) {
                    parse_str( $url_parts['query'], $query_params );
                    if ( isset( $query_params['edit_organism'] ) ) {
                        $organism_id = intval( $query_params['edit_organism'] );
                    }
                }
            }
        }

		if ( ! $organism_id ) {
			return $response;
		}

		if ( ! class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
			return $response;
		}

		// Fetch from flat table
        global $wpdb;
		$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $wpdb->prefix . 'ska_data_sys_organisms', array( 'filter_field' => 'id', 'filter_op' => 'eq', 'filter_val' => $organism_id ), 1 );
		if ( empty( $rows ) ) {
			return $response;
		}

		$organism = $rows[0];
		$html_content = isset( $organism['html_content'] ) ? $organism['html_content'] : '';

		// Inject into response
		$data = $response->get_data();
		$data['content']['raw'] = $html_content;
		$data['title']['raw']   = isset( $organism['name'] ) ? $organism['name'] : 'Organism';
		$response->set_data( $data );

		return $response;
	}

	/**
	 * Intercept the Save action and push data to Flat Table.
	 */
	public function intercept_organism_save( $post, $request, $creating ) {
		// Dữ liệu edit_organism được truyền qua apiFetch middleware từ Iframe
		$organism_id = $request->get_param( 'edit_organism' );

		if ( ! $organism_id ) {
			return;
		}

		$organism_id = intval( $organism_id );

		$content = $post->post_content;
		$title   = $post->post_title;

		// Parse HTML back to blocks JSON
		$blocks = parse_blocks( $content );
		$blocks = array_filter( $blocks, function( $block ) {
			return ! empty( $block['blockName'] ) || ! empty( trim( $block['innerHTML'] ) );
		});

		$record_data = array(
			'json_content' => wp_json_encode( array_values( $blocks ) ),
			'html_content' => $content
		);

		// Update Flat Table
		apply_filters( 'ska_data_update_record', false, $record_data, 'ska_data_sys_organisms', array( 'id' => $organism_id ) );

		// Trigger Physical Cache Export
		if ( class_exists( '\Ska\Design\Api\Organisms_API' ) ) {
			$api = \Ska\Design\Api\Organisms_API::get_instance();
			if ( method_exists( $api, 'export_physical_cache' ) ) {
				$api->export_physical_cache();
			}
		}
	}

	/**
	 * Cắt gọt giao diện WP Admin khi mở Iframe.
	 */
	public function inject_iframe_styles() {
		if ( ! isset( $_GET['ska_iframe'] ) ) {
			return;
		}

		echo '<style>
			#adminmenumain, #wpadminbar { display: none !important; }
			#wpcontent, #wpfooter { margin-left: 0 !important; }
			html.wp-toolbar { padding-top: 0 !important; }
		</style>';
	}

    /**
	 * Thêm JS vào Iframe để bắn tín hiệu postMessage khi lưu thành công.
	 */
    public function inject_iframe_js() {
        if ( ! isset( $_GET['ska_iframe'] ) ) {
			return;
		}

        $organism_id = isset( $_GET['edit_organism'] ) ? sanitize_text_field( wp_unslash( $_GET['edit_organism'] ) ) : '';

        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if ( typeof wp === 'undefined' || typeof wp.data === 'undefined' ) return;

				// Chèn Middleware vào apiFetch để tự động gán tham số edit_organism vào các request lưu nháp
				if ( typeof wp.apiFetch !== 'undefined' ) {
					wp.apiFetch.use(function(options, next) {
						if ( options.path && options.path.indexOf('/wp/v2/ska_organism_draft/') !== -1 && (options.method === 'POST' || options.method === 'PUT') ) {
							var separator = options.path.indexOf('?') !== -1 ? '&' : '?';
							options.path += separator + 'edit_organism=<?php echo esc_js( $organism_id ); ?>';
						}
						return next(options);
					});
				}

                let wasSaving = false;

                wp.data.subscribe(() => {
                    // Trì hoãn việc đọc store bằng setTimeout để tránh lỗi "__unstableMarkListeningStores"
                    // do gọi wp.data.select() ngay giữa luồng render của React (Gutenberg Crash Boundary)
                    setTimeout(() => {
                        const editor = wp.data.select('core/editor');
                        if ( ! editor || typeof editor.isSavingPost !== 'function' ) return;

                        const isSaving = editor.isSavingPost();
                        const isAutosaving = editor.isAutosavingPost();

                        if ( isSaving && ! isAutosaving ) {
                            wasSaving = true;
                        }

                        if ( wasSaving && ! isSaving && ! isAutosaving && ! editor.isEditedPostDirty() ) {
                            wasSaving = false;
                            
                            const newHtml = editor.getEditedPostContent();
                            const newTitle = editor.getEditedPostAttribute('title');

                            window.parent.postMessage({
                                type: 'SKA_ORGANISM_SAVED',
                                data: {
                                    id: '<?php echo esc_js( $organism_id ); ?>',
                                    name: newTitle,
                                    html: newHtml
                                }
                            }, '*');
                        }
                    }, 0);
                });
            });
        </script>
        <?php
    }
}
