<?php
/**
 * Shadow CPT Editor for Skaaa Organisms
 *
 * @package Skaaa_Builder_Design
 */

namespace Skaaa\Builder\Design;

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
		add_filter( 'rest_prepare_skaaa_organism_draft', array( $this, 'inject_organism_data' ), 10, 3 );
		
		// Intercept save after it updates the dummy post
		add_action( 'rest_after_insert_skaaa_organism_draft', array( $this, 'intercept_organism_save' ), 10, 3 );

		// Clean up the UI inside iframe
		add_action( 'admin_head', array( $this, 'inject_iframe_styles' ), 999 );
        add_action( 'admin_footer', array( $this, 'inject_iframe_js' ), 999 );
		
		// Register Editor Wrapper Menu
		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );
	}

	/**
	 * Register the Shadow Custom Post Type.
	 */
	public function register_shadow_cpt() {
		register_post_type( 'skaaa_organism_draft', array(
			'labels'              => array(
				'name'          => 'Skaaa Organism Draft',
				'singular_name' => 'Skaaa Organism Draft',
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
	 * Register the hidden submenu page for the Editor Wrapper
	 */
	public function register_menu() {
		add_submenu_page(
			'null', // Hidden
			'Organism Editor',
			'Organism Editor',
			'manage_options',
			'skaaa-organism-editor',
			array( $this, 'render_editor_wrapper' )
		);
	}

	/**
	 * Render the full-screen Editor Wrapper
	 */
	public function render_editor_wrapper() {
		$organism_id = isset( $_GET['organism_id'] ) ? absint( $_GET['organism_id'] ) : 0;

		if ( ! $organism_id ) {
			echo __( '<div class=\"wrap\"><h2>Error: Organism ID not found.</h2></div>', 'skaaa-no-code-design' );
			return;
		}

		// Ensure Organism dummy post exists
		$dummy_post_id = get_option( 'skaaa_organism_dummy_post_id' );
		if ( ! $dummy_post_id ) {
			echo __( '<div class=\"wrap\"><h2>Error: Dummy Post has not been initialized. ', 'skaaa-no-code-design' );
			return;
		}

		// Fetch organism info
		global $wpdb;
		$table_name = $wpdb->prefix . 'skaaa_data_sys_organisms';
		$organism = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$table_name} WHERE id = %d", $organism_id ) );

		if ( ! $organism ) {
			echo __( '<div class=\"wrap\"><h2>Error: Organism does not exist.</h2></div>', 'skaaa-no-code-design' );
			return;
		}

		// URL for iframe
		$iframe_url = admin_url( sprintf( 'post.php?post=%d&action=edit&skaaa_iframe=1&edit_organism=%d', $dummy_post_id, $organism_id ) );
		$back_url   = admin_url( 'admin.php?page=skaaa-design-workspace' );
		?>
		<style>
			#wpadminbar { display: none !important; }
			#adminmenumain { display: none !important; }
			#wpcontent { margin-left: 0 !important; padding-left: 0 !important; }
			html.wp-toolbar { padding-top: 0 !important; }
			#wpfooter { display: none !important; }
		</style>
		
		<div class="skaaa-organism-editor-wrapper bg-slate-100 h-screen w-full flex flex-col overflow-hidden" x-data="organismEditorData()">
			<!-- Topbar -->
			<div class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 shadow-sm z-10 shrink-0">
				<div class="flex items-center gap-3">
					<a href="<?php echo esc_url( $back_url ); ?>" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors cursor-pointer text-decoration-none">
						<span class="material-symbols-outlined text-[20px]">arrow_back</span>
					</a>
					<div class="h-6 w-px bg-slate-200 mx-1"></div>
					<div>
						<h1 class="m-0 text-sm font-bold text-slate-800 flex items-center gap-2">
							<?php echo esc_html( $organism->name ); ?>
							<span class="bg-pink-100 text-pink-700 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">
								SYMBOL
							</span>
						</h1>
					</div>
				</div>

				<div class="flex items-center gap-3">
					<div x-show="isSaving" x-transition class="flex items-center gap-2 text-sm text-slate-500 font-medium" style="display: none;">
						<span class="material-symbols-outlined text-[16px] animate-spin">sync</span>
						Đang lưu...
					</div>
					<div x-show="saved" x-transition class="flex items-center gap-2 text-sm text-emerald-600 font-medium bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100" style="display: none;">
						<span class="material-symbols-outlined text-[16px]">check_circle</span>
						Đã lưu
					</div>
				</div>
			</div>

			<!-- Editor Iframe -->
			<div class="flex-1 w-full relative bg-slate-50">
				<div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 z-20">
					<span class="material-symbols-outlined text-4xl text-pink-500 animate-spin mb-3">data_usage</span>
					<span class="text-slate-500 font-medium text-sm"><?php esc_html_e( 'Loading Editor...', 'skaaa-no-code-design' ); ?></span>
				</div>
				<iframe 
					src="<?php echo esc_url( $iframe_url ); ?>" 
					class="w-full h-full border-0" 
					@load="loading = false"
					id="skaaa-editor-iframe">
				</iframe>
			</div>
		</div>

		<script>
		document.addEventListener('alpine:init', () => {
			Alpine.data('organismEditorData', () => ({
				loading: true,
				isSaving: false,
				saved: false,
				saveTimeout: null,

				init() {
					window.addEventListener('message', (event) => {
						if (event.data && event.data.type === 'SKAAA_ORGANISM_SAVED') {
							this.isSaving = false;
							this.saved = true;
							
							if (this.saveTimeout) clearTimeout(this.saveTimeout);
							
							this.saveTimeout = setTimeout(() => {
								this.saved = false;
							}, 3000);
						} else if (event.data && event.data.type === 'SKAAA_ORGANISM_SAVING') {
							this.isSaving = true;
							this.saved = false;
						}
					});
				}
			}));
		});
		</script>
		<?php
	}

	/**
	 * Ensure exactly ONE dummy post exists to be used as the Editor shell.
	 */
	public function ensure_dummy_post_exists() {
		$dummy_post_id = get_option( 'skaaa_organism_dummy_post_id' );
		if ( ! $dummy_post_id || ! get_post( $dummy_post_id ) ) {
			$new_post_id = wp_insert_post( array(
				'post_title'  => 'Skaaa Dummy Editor',
				'post_type'   => 'skaaa_organism_draft',
				'post_status' => 'publish', // Publish to avoid auto-draft weirdness
			) );
			if ( ! is_wp_error( $new_post_id ) ) {
				update_option( 'skaaa_organism_dummy_post_id', $new_post_id );
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

		if ( ! class_exists( '\Skaaa\Data\Core\Data_Fetcher' ) ) {
			return $response;
		}

		// Fetch from flat table
        global $wpdb;
		$rows = \Skaaa\Data\Core\Data_Fetcher::get_table_rows( $wpdb->prefix . 'skaaa_data_sys_organisms', array( 'filter_field' => 'id', 'filter_op' => 'eq', 'filter_val' => $organism_id ), 1 );
		if ( empty( $rows ) ) {
			return $response;
		}

		$organism = $rows[0];
		$html_content = isset( $organism['html_content'] ) ? $organism['html_content'] : '';

        // Tương thích ngược: Nếu organism cũ chỉ có json_content nhưng không có html_content
        if ( empty( $html_content ) && ! empty( $organism['json_content'] ) ) {
            $blocks_array = json_decode( $organism['json_content'], true );
            
            if ( is_array( $blocks_array ) ) {
                // Client-side block json structure might need to be converted to server-side array
                // before serialize_blocks can process it.
                $client_blocks = isset( $blocks_array['clientId'] ) ? [ $blocks_array ] : $blocks_array;
                $server_blocks = [];
                
                // Helper closure to convert block format
                $convert_block = function( $cb ) use ( &$convert_block ) {
                    if ( ! is_array( $cb ) ) return null;
                    $sb = [
                        'blockName'    => $cb['name'] ?? null,
                        'attrs'        => $cb['attributes'] ?? [],
                        'innerBlocks'  => [],
                        'innerHTML'    => '',
                        'innerContent' => []
                    ];
                    if ( isset( $cb['innerBlocks'] ) && is_array( $cb['innerBlocks'] ) ) {
                        foreach ( $cb['innerBlocks'] as $child ) {
                            $conv = $convert_block( $child );
                            if ( $conv ) {
                                $sb['innerBlocks'][] = $conv;
                                $sb['innerContent'][] = null;
                            }
                        }
                    }
                    return $sb;
                };

                foreach ( $client_blocks as $cb ) {
                    $conv = $convert_block( $cb );
                    if ( $conv ) {
                        $server_blocks[] = $conv;
                    }
                }

                $html_content = serialize_blocks( $server_blocks );
            }
        }

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
		apply_filters( 'skaaa_data_update_record', false, $record_data, 'skaaa_data_sys_organisms', array( 'id' => $organism_id ) );

		// Trigger Physical Cache Export
		if ( class_exists( '\Skaaa\Design\Api\Organisms_API' ) ) {
			$api = \Skaaa\Design\Api\Organisms_API::get_instance();
			if ( method_exists( $api, 'export_physical_cache' ) ) {
				$api->export_physical_cache();
			}
		}
	}

	/**
	 * Cắt gọt giao diện WP Admin khi mở Iframe.
	 */
	public function inject_iframe_styles() {
		if ( ! isset( $_GET['skaaa_iframe'] ) ) {
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
        if ( ! isset( $_GET['skaaa_iframe'] ) ) {
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
						if ( options.path && options.path.indexOf('/wp/v2/skaaa_organism_draft/') !== -1 && (options.method === 'POST' || options.method === 'PUT') ) {
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
                                type: 'SKAAA_ORGANISM_SAVED',
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
