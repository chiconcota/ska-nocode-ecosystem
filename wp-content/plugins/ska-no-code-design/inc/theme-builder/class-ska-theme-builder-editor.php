<?php
/**
 * Ska Theme Builder Editor Wrapper
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined( 'ABSPATH' ) || exit;

class Ska_Theme_Builder_Editor {

	/**
	 * Instance
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
		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );
	}

	/**
	 * Register the hidden submenu page for the Editor Wrapper
	 */
	public function register_menu() {
		// Thêm một trang ẩn, không có parent slug thực sự để không hiện trên Menu
		add_submenu_page(
			'null', // Hidden
			'Theme Editor',
			'Theme Editor',
			'manage_options',
			'ska-theme-builder-editor',
			array( $this, 'render_editor_wrapper' )
		);
	}

	/**
	 * Render the full-screen Editor Wrapper
	 */
	public function render_editor_wrapper() {
		$template_id = isset( $_GET['template_id'] ) ? absint( $_GET['template_id'] ) : 0;

		if ( ! $template_id ) {
			echo '<div class="wrap"><h2>Lỗi: Không tìm thấy Template ID.</h2></div>';
			return;
		}

		// Ensure Organism dummy post exists
		$dummy_post_id = get_option( 'ska_organism_dummy_post_id' );
		if ( ! $dummy_post_id ) {
			echo '<div class="wrap"><h2>Lỗi: Chưa khởi tạo Dummy Post. Vui lòng quay lại Dashboard.</h2></div>';
			return;
		}

		// Fetch template info to show title
		global $wpdb;
		$table_name = $wpdb->prefix . 'ska_data_sys_organisms';
		$template = $wpdb->get_row( $wpdb->prepare( "SELECT name, type FROM {$table_name} WHERE id = %d", $template_id ) );

		if ( ! $template ) {
			echo '<div class="wrap"><h2>Lỗi: Template không tồn tại.</h2></div>';
			return;
		}

		// URL for iframe
		$iframe_url = admin_url( sprintf( 'post.php?post=%d&action=edit&ska_iframe=1&edit_organism=%d', $dummy_post_id, $template_id ) );
		$back_url   = admin_url( 'admin.php?page=ska-theme-builder' );
		?>
		<style>
			#wpadminbar { display: none !important; }
			#adminmenumain { display: none !important; }
			#wpcontent { margin-left: 0 !important; padding-left: 0 !important; }
			html.wp-toolbar { padding-top: 0 !important; }
			#wpfooter { display: none !important; }
		</style>
		
		<div class="ska-theme-editor-wrapper bg-slate-100 h-screen w-full flex flex-col -ml-5 -mt-2 overflow-hidden" x-data="themeEditorData()">
			<!-- Topbar -->
			<div class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 shadow-sm z-10 shrink-0">
				<div class="flex items-center gap-3">
					<a href="<?php echo esc_url( $back_url ); ?>" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors cursor-pointer text-decoration-none">
						<span class="material-symbols-outlined text-[20px]">arrow_back</span>
					</a>
					<div class="h-6 w-px bg-slate-200 mx-1"></div>
					<div>
						<h1 class="m-0 text-sm font-bold text-slate-800 flex items-center gap-2">
							<?php echo esc_html( $template->name ); ?>
							<span class="bg-indigo-100 text-indigo-700 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">
								<?php echo esc_html( str_replace('theme_', '', $template->type) ); ?>
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
					<!-- Save button is inside Gutenberg, we don't need a wrapper save button unless we want to trigger it via postMessage -->
				</div>
			</div>

			<!-- Editor Iframe -->
			<div class="flex-1 w-full relative bg-slate-50">
				<div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 z-20">
					<span class="material-symbols-outlined text-4xl text-indigo-500 animate-spin mb-3">data_usage</span>
					<span class="text-slate-500 font-medium text-sm">Đang tải Editor...</span>
				</div>
				<iframe 
					src="<?php echo esc_url( $iframe_url ); ?>" 
					class="w-full h-full border-0" 
					@load="loading = false"
					id="ska-editor-iframe">
				</iframe>
			</div>
		</div>

		<script>
		document.addEventListener('alpine:init', () => {
			Alpine.data('themeEditorData', () => ({
				loading: true,
				isSaving: false,
				saved: false,
				saveTimeout: null,

				init() {
					// Lắng nghe sự kiện lưu từ iframe
					window.addEventListener('message', (event) => {
						if (event.data && event.data.type === 'SKA_ORGANISM_SAVED') {
							this.isSaving = false;
							this.saved = true;
							
							if (this.saveTimeout) clearTimeout(this.saveTimeout);
							
							this.saveTimeout = setTimeout(() => {
								this.saved = false;
							}, 3000);
						} else if (event.data && event.data.type === 'SKA_ORGANISM_SAVING') {
							// Tuỳ chọn: nếu Gutenberg iframe bắn sự kiện "đang lưu"
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
}
