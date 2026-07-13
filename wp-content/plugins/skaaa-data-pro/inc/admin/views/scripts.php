<?php
namespace Skaaa\Data\Admin;

defined( 'ABSPATH' ) || exit;

global $wpdb;
$table_name = $wpdb->prefix . 'skaaa_data_sys_scripts';

// Phân giải hành động trên URL
$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

$script_data = null;
if ( 'edit' === $action && $id > 0 ) {
	$script_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table_name}` WHERE `id` = %d", $id ) );
	if ( ! $script_data ) {
		$action = ''; // Fallback về trang danh sách nếu ID không tồn tại
	}
}

// Lấy danh sách scripts phục vụ trang Index
$scripts = $wpdb->get_results( "SELECT * FROM `{$table_name}` ORDER BY `id` DESC" );

// Lấy danh sách Pages & Apps phục vụ cấu hình điều kiện
$pages = get_pages( [ 'post_status' => 'publish' ] );
$apps = \Skaaa\Data\Core\App_Manager::get_apps();

// Tạo nonce bảo mật
$nonce = wp_create_nonce( 'skaaa_data_nonce' );

// Gom tất cả các class Tailwind được dùng trong file để compile JIT qua engine của Skaaa
$tailwind_classes = 'wrap mt-6 pr-4 flex justify-between items-center bg-white/80 backdrop-blur border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6 flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider hover:text-indigo-600 no-underline transition-colors text-slate-600 font-bold text-slate-900 text-2xl m-0 mt-1 flex items-center gap-2 text-indigo-600 text-[28px] flex gap-3 inline-flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-800 font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all no-underline cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md cursor-pointer border-0 flex flex-col lg:flex-row gap-6 w-full lg:w-2/3 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[500px] border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex justify-between items-center font-bold text-slate-800 text-xs uppercase tracking-wider m-0 flex items-center gap-2 text-xs text-slate-400 flex-1 p-0 relative w-full h-full min-h-[480px] p-6 border-0 focus:ring-0 focus:outline-none text-sm font-mono bg-slate-950 text-slate-100 placeholder-slate-600 leading-relaxed block resize-y w-full lg:w-1/3 flex flex-col gap-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4 font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 m-0 flex items-center gap-1.5 block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5 w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white disabled:bg-slate-50 disabled:text-slate-400 flex items-center gap-2 pt-2 w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 text-slate-700 select-none cursor-pointer hidden bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4 block text-xs font-medium text-slate-500 mb-1.5 w-full h-32 p-2 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none text-[10px] text-slate-400 leading-relaxed block mt-1 overflow-x-auto w-full border-collapse text-left text-sm bg-slate-50/75 border-b border-slate-200 text-slate-600 font-semibold py-4 px-6 text-center text-right divide-y divide-slate-100 text-slate-700 py-12 text-center text-slate-400 text-[48px] block mb-2 opacity-50 hover:bg-slate-50/50 transition-colors font-semibold text-slate-900 text-xs text-slate-400 mt-0.5 font-mono px-2 py-1 rounded-md text-xs font-semibold border bg-blue-50 text-blue-700 border-blue-100 bg-teal-50 text-teal-700 border-teal-100 bg-amber-50 text-amber-700 border-amber-100 bg-purple-50 text-purple-700 border-purple-100 capitalize text-slate-500 text-xs flex justify-end gap-2 p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg border-0 bg-transparent cursor-pointer transition-colors no-underline inline-flex items-center justify-center hover:text-red-600 hover:bg-red-50 animate-spin';

$tailwind_css = '';
if ( has_filter( 'skaaa_compile_tailwind' ) ) {
	$compiled = apply_filters( 'skaaa_compile_tailwind', $tailwind_classes );
	$tailwind_css = is_array( $compiled ) ? ( $compiled['css'] ?? '' ) : (string) $compiled;
}
?>

<!-- JIT CSS compiled server-side -->
<?php if ( ! empty( $tailwind_css ) ) : ?>
<style id="skaaa-scripts-tailwind-jit">
<?php echo $tailwind_css; ?>
</style>
<?php else : ?>
<!-- Fallback CDN if Skaaa No-Code Design plugin is deactivated -->
<script src="https://cdn.tailwindcss.com"></script>
<?php endif; ?>

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="wrap mt-6 pr-4 sk-scripts-wrapper">
	<!-- TIÊU CHÍ 1: TRANG SOẠN THẢO RIÊNG BIỆT (2 CỘT) -->
	<?php if ( 'new' === $action || 'edit' === $action ) : 
		$is_edit = ( 'edit' === $action && $script_data );
		$title = $is_edit ? __( 'Edit Script', 'skaaa-data-pro' ) : __( 'Add New Script', 'skaaa-data-pro' );
		
		// Parse các conditions đã chọn
		$selected_pages = [];
		$selected_apps = [];
		if ( $is_edit && ! empty( $script_data->conditions ) ) {
			$conds = json_decode( $script_data->conditions, true );
			if ( is_array( $conds ) ) {
				$selected_pages = isset( $conds['pages'] ) ? array_map( 'intval', $conds['pages'] ) : [];
				$selected_apps = isset( $conds['apps'] ) ? $conds['apps'] : [];
			}
		}
	?>
		<!-- Form Soạn Thảo 2 Cột -->
		<form id="skaaa-script-form" class="space-y-6">
			<input type="hidden" name="id" id="script-db-id" value="<?php echo esc_attr( $id ); ?>">
			
			<!-- Header Action Bar -->
			<div class="flex justify-between items-center bg-white/80 backdrop-blur border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
				<div>
					<div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
						<a href="?page=skaaa-data-pro-scripts" class="hover:text-indigo-600 no-underline transition-colors"><?php echo esc_html__( 'Scripts Library', 'skaaa-data-pro' ); ?></a>
						<span>/</span>
						<span class="text-slate-600"><?php echo esc_html( $title ); ?></span>
					</div>
					<h1 class="font-bold text-slate-900 text-2xl m-0 mt-1 flex items-center gap-2">
						<span class="material-symbols-outlined text-indigo-600 text-[28px]"><?php echo $is_edit ? 'edit_square' : 'add_box'; ?></span>
						<?php echo esc_html( $title ); ?>
					</h1>
				</div>
				<div class="flex gap-3">
					<a href="?page=skaaa-data-pro-scripts" class="inline-flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-800 font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all no-underline cursor-pointer">
						<span class="material-symbols-outlined text-[18px]">arrow_back</span>
						<?php echo esc_html__( 'Back to Library', 'skaaa-data-pro' ); ?>
					</a>
					<button type="submit" id="skaaa-save-script-btn" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md cursor-pointer border-0">
						<span class="material-symbols-outlined text-[18px]">save</span>
						<?php echo esc_html__( 'Save Script', 'skaaa-data-pro' ); ?>
					</button>
				</div>
			</div>

			<!-- Main Layout: 2 Cột -->
			<div class="flex flex-col lg:flex-row gap-6">
				<!-- Cột Trái: Trình soạn thảo mã (70%) -->
				<div class="w-full lg:w-2/3 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[500px]">
					<div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex justify-between items-center">
						<h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider m-0 flex items-center gap-2">
							<span class="material-symbols-outlined text-slate-500 text-[18px]">code</span>
							<span id="script-content-label"><?php echo esc_html__( 'Code Content (Raw)', 'skaaa-data-pro' ); ?></span>
						</h3>
						<span class="text-xs text-slate-400"><?php echo esc_html__( 'Monospace Font Editor', 'skaaa-data-pro' ); ?></span>
					</div>
					<div class="flex-1 p-0 relative">
						<textarea name="content" id="script-content" required placeholder="console.log('Hello from Skaaa No-code!');"
							class="w-full h-full min-h-[480px] p-6 border-0 focus:ring-0 focus:outline-none text-sm font-mono bg-slate-950 text-slate-100 placeholder-slate-600 leading-relaxed block resize-y"><?php echo $is_edit ? esc_textarea( $script_data->content ) : ''; ?></textarea>
					</div>
				</div>

				<!-- Cột Phải: Sidebar Cấu Hình (30%) -->
				<div class="w-full lg:w-1/3 flex flex-col gap-6">
					<!-- General Box -->
					<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
						<h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 m-0 flex items-center gap-1.5">
							<span class="material-symbols-outlined text-slate-500 text-[20px]">info</span>
							<?php echo esc_html__( 'Identity', 'skaaa-data-pro' ); ?>
						</h3>
						
						<!-- Name -->
						<div>
							<label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"><?php echo esc_html__( 'Script Name', 'skaaa-data-pro' ); ?></label>
							<input type="text" name="name" id="script-name" required placeholder="e.g. Google Analytics" value="<?php echo $is_edit ? esc_attr( $script_data->name ) : ''; ?>"
								class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white">
						</div>
						
						<!-- Script ID -->
						<div>
							<label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"><?php echo esc_html__( 'Script ID (Unique Slug)', 'skaaa-data-pro' ); ?></label>
							<input type="text" name="script_id" id="script-key" required placeholder="e.g. google-analytics" value="<?php echo $is_edit ? esc_attr( $script_data->script_id ) : ''; ?>" <?php disabled( $is_edit ); ?>
								class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white disabled:bg-slate-50 disabled:text-slate-400">
						</div>
					</div>

					<!-- Settings Box -->
					<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
						<h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 m-0 flex items-center gap-1.5">
							<span class="material-symbols-outlined text-slate-500 text-[20px]">settings</span>
							<?php echo esc_html__( 'Configuration', 'skaaa-data-pro' ); ?>
						</h3>
						
						<!-- Type -->
						<div>
							<label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"><?php echo esc_html__( 'Script Type', 'skaaa-data-pro' ); ?></label>
							<select name="type" id="script-type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 bg-white">
								<option value="js_inline" <?php selected( $is_edit ? $script_data->type : '', 'js_inline' ); ?>><?php echo esc_html__( 'JS Inline', 'skaaa-data-pro' ); ?></option>
								<option value="css_inline" <?php selected( $is_edit ? $script_data->type : '', 'css_inline' ); ?>><?php echo esc_html__( 'CSS Inline', 'skaaa-data-pro' ); ?></option>
								<option value="js_file" <?php selected( $is_edit ? $script_data->type : '', 'js_file' ); ?>><?php echo esc_html__( 'JS External File', 'skaaa-data-pro' ); ?></option>
								<option value="css_file" <?php selected( $is_edit ? $script_data->type : '', 'css_file' ); ?>><?php echo esc_html__( 'CSS External File', 'skaaa-data-pro' ); ?></option>
							</select>
						</div>

						<!-- Location -->
						<div>
							<label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"><?php echo esc_html__( 'Inject Location', 'skaaa-data-pro' ); ?></label>
							<select name="location" id="script-location" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 bg-white">
								<option value="footer" <?php selected( $is_edit ? $script_data->location : '', 'footer' ); ?>><?php echo esc_html__( 'Footer (wp_footer)', 'skaaa-data-pro' ); ?></option>
								<option value="header" <?php selected( $is_edit ? $script_data->location : '', 'header' ); ?>><?php echo esc_html__( 'Header (wp_head)', 'skaaa-data-pro' ); ?></option>
							</select>
						</div>

						<!-- Load Condition -->
						<div>
							<label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5"><?php echo esc_html__( 'Load Condition', 'skaaa-data-pro' ); ?></label>
							<select name="load_condition" id="script-load-condition" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 bg-white">
								<option value="global" <?php selected( $is_edit ? $script_data->load_condition : '', 'global' ); ?>><?php echo esc_html__( 'Global (All pages)', 'skaaa-data-pro' ); ?></option>
								<option value="conditional" <?php selected( $is_edit ? $script_data->load_condition : '', 'conditional' ); ?>><?php echo esc_html__( 'Conditional pages/apps', 'skaaa-data-pro' ); ?></option>
							</select>
						</div>

						<!-- Active Status -->
						<div class="flex items-center gap-2 pt-2">
							<input type="checkbox" name="status" id="script-status" value="1" <?php checked( $is_edit ? $script_data->status : 1, 1 ); ?> class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer">
							<label for="script-status" class="text-sm font-semibold text-slate-700 select-none cursor-pointer"><?php echo esc_html__( 'Enable immediately', 'skaaa-data-pro' ); ?></label>
						</div>
					</div>

					<!-- Conditional Config Box -->
					<div id="skaaa-condition-wrapper" class="hidden bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
						<h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 m-0 flex items-center gap-1.5">
							<span class="material-symbols-outlined text-slate-500 text-[20px]">filter_alt</span>
							<?php echo esc_html__( 'Conditional Rules', 'skaaa-data-pro' ); ?>
						</h3>
						
						<!-- Target Pages -->
						<div>
							<label class="block text-xs font-medium text-slate-500 mb-1.5"><?php echo esc_html__( 'Load on specific Pages', 'skaaa-data-pro' ); ?></label>
							<select id="cond-pages" multiple class="w-full h-32 p-2 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none">
								<?php foreach ( $pages as $p ) : ?>
									<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( in_array( $p->ID, $selected_pages, true ) ); ?>><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<!-- Target Apps -->
						<div>
							<label class="block text-xs font-medium text-slate-500 mb-1.5"><?php echo esc_html__( 'Load on specific Workspaces', 'skaaa-data-pro' ); ?></label>
							<select id="cond-apps" multiple class="w-full h-32 p-2 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none">
								<?php foreach ( $apps as $app_key => $app_info ) : ?>
									<option value="<?php echo esc_attr( $app_key ); ?>" <?php selected( in_array( $app_key, $selected_apps, true ) ); ?>><?php echo esc_html( $app_info['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<span class="text-[10px] text-slate-400 leading-relaxed block mt-1"><?php echo esc_html__( 'Hold Ctrl / Cmd to select multiple items.', 'skaaa-data-pro' ); ?></span>
					</div>
				</div>
			</div>
		</form>

		<!-- Interactive Vanilla JS for Editing Page -->
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const typeSelect = document.getElementById('script-type');
			const condSelect = document.getElementById('script-load-condition');
			const condWrapper = document.getElementById('skaaa-condition-wrapper');
			const contentLabel = document.getElementById('script-content-label');
			const contentArea = document.getElementById('script-content');
			const form = document.getElementById('skaaa-script-form');
			const saveBtn = document.getElementById('skaaa-save-script-btn');
			
			const nonce = '<?php echo esc_js( $nonce ); ?>';
			const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

			function toggleTypeFields() {
				const type = typeSelect.value;
				if (type === 'js_file' || type === 'css_file') {
					contentLabel.textContent = '<?php echo esc_js( __( 'File URL (External CDN)', 'skaaa-data-pro' ) ); ?>';
					contentArea.placeholder = 'e.g. https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
					contentArea.classList.remove('bg-slate-950', 'text-slate-100');
					contentArea.classList.add('bg-slate-50', 'text-slate-700');
				} else {
					contentLabel.textContent = '<?php echo esc_js( __( 'Code Content (Raw)', 'skaaa-data-pro' ) ); ?>';
					contentArea.placeholder = type === 'js_inline' ? 'console.log("Alpine store initialized");' : 'body { background-color: #f3f4f6; }';
					contentArea.classList.add('bg-slate-950', 'text-slate-100');
					contentArea.classList.remove('bg-slate-50', 'text-slate-700');
				}
			}

			function toggleConditionFields() {
				if (condSelect.value === 'conditional') {
					condWrapper.classList.remove('hidden');
				} else {
					condWrapper.classList.add('hidden');
				}
			}

			typeSelect.addEventListener('change', toggleTypeFields);
			condSelect.addEventListener('change', toggleConditionFields);

			// Chạy ngay lúc load
			toggleTypeFields();
			toggleConditionFields();

			// AJAX lưu script
			form.addEventListener('submit', function(e) {
				e.preventDefault();

				const saveBtnText = saveBtn.innerHTML;
				saveBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> <?php echo esc_js( __( 'Saving...', 'skaaa-data-pro' ) ); ?>';
				saveBtn.disabled = true;

				const formData = new FormData(form);
				formData.append('action', 'skaaa_data_save_script');
				formData.append('security', nonce);

				// Xử lý nạp điều kiện
				if (condSelect.value === 'conditional') {
					const selectedPages = Array.from(document.getElementById('cond-pages').selectedOptions).map(opt => opt.value);
					const selectedApps = Array.from(document.getElementById('cond-apps').selectedOptions).map(opt => opt.value);
					
					const conds = {
						pages: selectedPages,
						apps: selectedApps
					};
					formData.append('conditions', JSON.stringify(conds));
				}

				// Nếu status checkbox không check thì set explicit 0
				if (!document.getElementById('script-status').checked) {
					formData.set('status', '0');
				}

				// Nếu là chế độ edit, script_id bị disable nên FormData không tự lấy. Ta append thủ công
				const keyInput = document.getElementById('script-key');
				if (keyInput.disabled) {
					formData.append('script_id', keyInput.value);
				}

				fetch(ajaxUrl, {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						alert(data.data.message);
						window.location.href = '?page=skaaa-data-pro-scripts';
					} else {
						alert(data.data.message || 'Error occurred while saving script.');
						saveBtn.innerHTML = saveBtnText;
						saveBtn.disabled = false;
					}
				})
				.catch(err => {
					console.error(err);
					alert('Network error. Failed to save script.');
					saveBtn.innerHTML = saveBtnText;
					saveBtn.disabled = false;
				});
			});
		});
		</script>

	<!-- TIÊU CHÍ 2: TRANG DANH SÁCH (INDEX VIEW) -->
	<?php else : ?>
		<!-- Header -->
		<div class="flex justify-between items-center bg-white/80 backdrop-blur border border-slate-200/80 rounded-2xl p-6 shadow-sm mb-6">
			<div>
				<h1 class="font-bold text-slate-900 text-2xl m-0 flex items-center gap-2">
					<span class="material-symbols-outlined text-indigo-600 text-[28px]">code_blocks</span>
					<?php echo esc_html__( 'Scripts Library', 'skaaa-data-pro' ); ?>
				</h1>
				<p class="text-sm text-slate-500 mt-1">
					<?php echo esc_html__( 'Manage central JS/CSS scripts, CDN references, and control where they load.', 'skaaa-data-pro' ); ?>
				</p>
			</div>
			<a href="?page=skaaa-data-pro-scripts&action=new" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all hover:shadow-md cursor-pointer border-0 no-underline">
				<span class="material-symbols-outlined text-[18px]">add</span>
				<?php echo esc_html__( 'Add Script', 'skaaa-data-pro' ); ?>
			</a>
		</div>

		<!-- Datagrid Table -->
		<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
			<div class="overflow-x-auto">
				<table class="w-full border-collapse text-left text-sm">
					<thead>
						<tr class="bg-slate-50/75 border-b border-slate-200 text-slate-600 font-semibold">
							<th class="py-4 px-6"><?php echo esc_html__( 'Name / ID', 'skaaa-data-pro' ); ?></th>
							<th class="py-4 px-6"><?php echo esc_html__( 'Type', 'skaaa-data-pro' ); ?></th>
							<th class="py-4 px-6"><?php echo esc_html__( 'Location', 'skaaa-data-pro' ); ?></th>
							<th class="py-4 px-6"><?php echo esc_html__( 'Load Condition', 'skaaa-data-pro' ); ?></th>
							<th class="py-4 px-6 text-center"><?php echo esc_html__( 'Status', 'skaaa-data-pro' ); ?></th>
							<th class="py-4 px-6 text-right"><?php echo esc_html__( 'Actions', 'skaaa-data-pro' ); ?></th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100 text-slate-700">
						<?php if ( empty( $scripts ) ) : ?>
							<tr>
								<td colspan="6" class="py-12 text-center text-slate-400">
									<span class="material-symbols-outlined text-[48px] block mb-2 opacity-50">deployed_code_alert</span>
									<?php echo esc_html__( 'No scripts found. Click "Add Script" to get started.', 'skaaa-data-pro' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $scripts as $script ) : 
								$cond_desc = esc_html__( 'Global (All Pages)', 'skaaa-data-pro' );
								if ( 'conditional' === $script->load_condition && ! empty( $script->conditions ) ) {
									$conds = json_decode( $script->conditions, true );
									$parts = [];
									if ( ! empty( $conds['pages'] ) ) {
										$parts[] = sprintf( __( '%d pages', 'skaaa-data-pro' ), count( $conds['pages'] ) );
									}
									if ( ! empty( $conds['apps'] ) ) {
										$parts[] = sprintf( __( '%d apps', 'skaaa-data-pro' ), count( $conds['apps'] ) );
									}
									$cond_desc = ! empty( $parts ) ? implode( ' & ', $parts ) : esc_html__( 'No condition set', 'skaaa-data-pro' );
								}
								
								// Nhãn hiển thị loại Script
								$type_label = '';
								$type_color = '';
								switch ( $script->type ) {
									case 'js_file':
										$type_label = 'JS File';
										$type_color = 'bg-blue-50 text-blue-700 border-blue-100';
										break;
									case 'css_file':
										$type_label = 'CSS File';
										$type_color = 'bg-teal-50 text-teal-700 border-teal-100';
										break;
									case 'js_inline':
										$type_label = 'JS Inline';
										$type_color = 'bg-amber-50 text-amber-700 border-amber-100';
										break;
									case 'css_inline':
										$type_label = 'CSS Inline';
										$type_color = 'bg-purple-50 text-purple-700 border-purple-100';
										break;
								}
							?>
								<tr class="hover:bg-slate-50/50 transition-colors" data-id="<?php echo esc_attr( $script->id ); ?>">
									<td class="py-4 px-6">
										<div class="font-semibold text-slate-900"><?php echo esc_html( $script->name ); ?></div>
										<div class="text-xs text-slate-400 mt-0.5">ID: <code><?php echo esc_html( $script->script_id ); ?></code></div>
									</td>
									<td class="py-4 px-6">
										<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border <?php echo esc_attr( $type_color ); ?>">
											<?php echo esc_html( $type_label ); ?>
										</span>
									</td>
									<td class="py-4 px-6 capitalize">
										<?php echo esc_html( $script->location ); ?>
									</td>
									<td class="py-4 px-6 text-slate-500 text-xs">
										<?php echo esc_html( $cond_desc ); ?>
									</td>
									<td class="py-4 px-6 text-center">
										<label class="relative inline-flex items-center cursor-pointer justify-center">
											<input type="checkbox" class="sr-only peer skaaa-toggle-status" <?php checked( $script->status, 1 ); ?> data-id="<?php echo esc_attr( $script->id ); ?>">
											<div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
										</label>
									</td>
									<td class="py-4 px-6 text-right">
										<div class="flex justify-end gap-2">
											<a href="?page=skaaa-data-pro-scripts&action=edit&id=<?php echo esc_attr( $script->id ); ?>" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg border-0 bg-transparent cursor-pointer transition-colors no-underline inline-flex items-center justify-center" title="<?php echo esc_attr__( 'Edit Script', 'skaaa-data-pro' ); ?>">
												<span class="material-symbols-outlined text-[20px]">edit</span>
											</a>
											<button class="skaaa-delete-script-btn p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg border-0 bg-transparent cursor-pointer transition-colors" 
												data-id="<?php echo esc_attr( $script->id ); ?>" 
												data-name="<?php echo esc_attr( $script->name ); ?>"
												title="<?php echo esc_attr__( 'Delete Script', 'skaaa-data-pro' ); ?>">
												<span class="material-symbols-outlined text-[20px]">delete</span>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Interactive Vanilla JS for List View -->
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const nonce = '<?php echo esc_js( $nonce ); ?>';
			const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

			// Xóa Script
			const deleteBtns = document.querySelectorAll('.skaaa-delete-script-btn');
			deleteBtns.forEach(btn => {
				btn.addEventListener('click', function() {
					const id = this.getAttribute('data-id');
					const name = this.getAttribute('data-name');
					
					if (!confirm('<?php echo esc_js( __( 'Are you sure you want to permanently delete script [${name}]?', 'skaaa-data-pro' ) ); ?>'.replace('${name}', name))) {
						return;
					}

					const formData = new FormData();
					formData.append('action', 'skaaa_data_delete_script');
					formData.append('security', nonce);
					formData.append('id', id);

					fetch(ajaxUrl, {
						method: 'POST',
						body: formData
					})
					.then(res => res.json())
					.then(data => {
						if (data.success) {
							alert(data.data.message);
							window.location.reload();
						} else {
							alert(data.data.message || 'Failed to delete script.');
						}
					})
					.catch(err => {
						console.error(err);
						alert('Network error. Failed to delete script.');
					});
				});
			});

			// Toggle Switch Status
			const toggleInputs = document.querySelectorAll('.skaaa-toggle-status');
			toggleInputs.forEach(input => {
				input.addEventListener('change', function() {
					const id = this.getAttribute('data-id');
					const status = this.checked ? 1 : 0;

					const formData = new FormData();
					formData.append('action', 'skaaa_data_toggle_script_status');
					formData.append('security', nonce);
					formData.append('id', id);
					formData.append('status', status);

					fetch(ajaxUrl, {
						method: 'POST',
						body: formData
					})
					.then(res => res.json())
					.then(data => {
						if (!data.success) {
							alert(data.data.message || 'Failed to update status.');
							this.checked = !this.checked;
						}
					})
					.catch(err => {
						console.error(err);
						alert('Network error. Failed to update status.');
						this.checked = !this.checked;
					});
				});
			});
		});
		</script>
	<?php endif; ?>
</div>

<!-- Styles chung -->
<style>
.sk-scripts-wrapper {
	font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif !important;
}
.sk-scripts-wrapper input, .sk-scripts-wrapper select, .sk-scripts-wrapper textarea {
	font-family: 'Outfit', sans-serif !important;
}
.sk-scripts-wrapper .material-symbols-outlined {
	font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	vertical-align: middle;
}
.sk-scripts-wrapper textarea.font-mono {
	font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
}
/* Scrollbar Styling cho code editor */
.sk-scripts-wrapper ::-webkit-scrollbar { width: 8px; height: 8px; }
.sk-scripts-wrapper ::-webkit-scrollbar-track { background: #0f172a; }
.sk-scripts-wrapper ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
.sk-scripts-wrapper ::-webkit-scrollbar-thumb:hover { background: #475569; }

/* Reset WP Admin native notice spacing inside wrapper */
#wpcontent { padding-left: 0; }
</style>
