<?php
/**
 * Ska Portal Generator
 * Lắng nghe từ Ska Data Pro để tự động sinh Theme Templates & Organisms
 *
 * @package Ska_No_Code_Design\Theme_Builder
 */

namespace Ska_No_Code_Design\Theme_Builder;

defined('ABSPATH') || exit;

class Ska_Portal_Generator
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
		// Hook do Ska Data Pro (Database_Engine) gọi tới
		add_filter('ska_design_generate_portal_assets', array($this, 'generate_assets'), 10, 3);
		
		// Lắng nghe khi một bảng bị xóa để dọn rác
		add_action('ska_data_table_deleted', array($this, 'cleanup_assets_on_table_delete'), 10, 1);
	}

	/**
	 * Dọn dẹp Organism, List View, Detail View khi bảng Data bị xóa
	 */
	public function cleanup_assets_on_table_delete($table_name)
	{
		$table_slug = str_replace(get_option('ska_data_prefix', 'ska_data_'), '', $table_name);
		global $wpdb;

		$table_templates = $wpdb->prefix . 'ska_data_sys_theme_templates';
		$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';

		// 1. Tìm các templates có điều kiện liên quan đến table_slug này
		$like_slug = '%' . $wpdb->esc_like($table_slug) . '%';
		$templates = $wpdb->get_results($wpdb->prepare(
			"SELECT id, organism_id FROM $table_templates WHERE conditions LIKE %s",
			$like_slug
		));

		$organism_ids = array();
		if (!empty($templates)) {
			foreach ($templates as $tpl) {
				if (!empty($tpl->organism_id)) {
					$organism_ids[] = intval($tpl->organism_id);
				}
			}
		}

		// 2. Xóa các Organisms được liên kết trực tiếp với templates đó
		if (!empty($organism_ids)) {
			$ids_placeholders = implode(',', array_fill(0, count($organism_ids), '%d'));
			$wpdb->query($wpdb->prepare(
				"DELETE FROM $table_organisms WHERE id IN ($ids_placeholders)",
				...$organism_ids
			));
		}

		// 3. Xóa các Organisms rác khác (như Row hoặc Form chứa content) có json_content liên quan tới table_slug
		$wpdb->query($wpdb->prepare(
			"DELETE FROM $table_organisms WHERE json_content LIKE %s OR json_content LIKE %s OR json_content LIKE %s",
			'%' . $wpdb->esc_like('"' . $table_slug . '.id"') . '%',
			'%' . $wpdb->esc_like('"sourceTable":"' . $table_slug . '"') . '%',
			'%' . $wpdb->esc_like('update_' . $table_slug) . '%'
		));

		// 4. Xóa các Templates đó khỏi bảng templates
		$wpdb->query($wpdb->prepare(
			"DELETE FROM $table_templates WHERE conditions LIKE %s",
			$like_slug
		));

		// 5. Làm mới (Flush) JSON Cache cho Editor sau khi xóa organism hàng loạt
		if (class_exists('\Ska\Design\Api\Organisms_API')) {
			\Ska\Design\Api\Organisms_API::get_instance()->export_physical_cache();
		}
	}

	/**
	 * Xử lý sinh giao diện cho một bảng (Table)
	 *
	 * @param mixed $result Kết quả mặc định của filter
	 * @param string $table_name Tên bảng
	 * @param array $options Options (add_gutenberg, etc.)
	 * @return array|\WP_Error
	 */
	public function generate_assets($result, $table_name, $options)
	{
		// Lấy schema của bảng
		$dictionary = get_option('ska_data_dictionary', array());
		if (!isset($dictionary[$table_name])) {
			return new \WP_Error('table_not_found', 'Không tìm thấy cấu trúc bảng.');
		}

		$schema = $dictionary[$table_name];
		$table_info = isset($schema['__table_info']) ? $schema['__table_info'] : array();
		$table_label = isset($table_info['label']) ? $table_info['label'] : $table_name;
		$table_slug = str_replace(get_option('ska_data_prefix', 'ska_data_'), '', $table_name);

		// Tự động xác định hoặc khởi tạo Folder tương ứng cho App trong Theme Builder
		$app_id = isset($table_info['app_id']) ? $table_info['app_id'] : 'uncategorized';
		$folder_id = '';

		if ($app_id !== 'uncategorized' && $app_id !== 'ska_system') {
			$apps = get_option('ska_data_apps', array());
			$app_name = isset($apps[$app_id]['name']) ? $apps[$app_id]['name'] : '';

			if (!empty($app_name)) {
				// Đọc folders hiện tại của Theme Builder
				$folders_json = get_option('ska_theme_builder_folders', '[]');
				$folders = json_decode($folders_json, true);
				if (!is_array($folders)) {
					$folders = array();
				}

				// Kiểm tra xem đã tồn tại folder cho app này chưa
				$found = false;
				foreach ($folders as $f) {
					if ($f['id'] === $app_id) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$folders[] = array(
						'id' => $app_id,
						'name' => $app_name
					);
					update_option('ska_theme_builder_folders', wp_json_encode($folders), false);
				}

				$folder_id = $app_id;
			}
		}

		// 1. Sinh Organism (List Row)
		$organism_id = $this->create_organism($table_slug, $table_label, $schema);
		if (is_wp_error($organism_id)) {
			return $organism_id;
		}

		$portal_slug = isset($table_info['portal_settings']['slug']) && !empty($table_info['portal_settings']['slug']) ? $table_info['portal_settings']['slug'] : $table_slug;

		// 2. Sinh List View Template
		$list_view_id = $this->create_list_view($table_name, $table_slug, $table_label, $organism_id, $folder_id, $portal_slug, $schema);
		if (is_wp_error($list_view_id)) {
			return $list_view_id;
		}

		// 3. Sinh Detail View Template
		$detail_view_id = $this->create_detail_view($table_slug, $table_label, $schema, $folder_id, $portal_slug);
		if (is_wp_error($detail_view_id)) {
			return $detail_view_id;
		}

		// 4. Sinh Create View Template
		$insert_view_id = $this->create_insert_view($table_slug, $table_label, $schema, $folder_id, $portal_slug);
		if (is_wp_error($insert_view_id)) {
			return $insert_view_id;
		}

		// 5. Làm mới JSON Cache cho Organisms sau khi tạo mới để Tailwind Compiler nhận diện class
		if (class_exists('\Ska\Design\Api\Organisms_API')) {
			\Ska\Design\Api\Organisms_API::get_instance()->export_physical_cache();
		}

		return array(
			'organism_id' => $organism_id,
			'list_view_id' => $list_view_id,
			'detail_view_id' => $detail_view_id,
			'insert_view_id' => $insert_view_id,
			'portal_url' => home_url('/' . $portal_slug . '/'),
			'theme_builder_url' => admin_url('admin.php?page=ska-theme-builder&folder=' . $folder_id),
			'list_view_editor_url' => admin_url('admin.php?page=ska-theme-builder-editor&template_id=' . $list_view_id),
			'detail_view_editor_url' => admin_url('admin.php?page=ska-theme-builder-editor&template_id=' . $detail_view_id),
			'insert_view_editor_url' => admin_url('admin.php?page=ska-theme-builder-editor&template_id=' . $insert_view_id),
			'success' => true
		);
	}

	private function compute_list_grid($schema)
	{
		$list_columns = array();
		foreach ($schema as $col_slug => $col_data) {
			if ($col_slug === '__table_info' || $col_slug === 'id' || $col_slug === 'created_at') {
				continue;
			}
			$type = isset($col_data['type']) ? $col_data['type'] : 'text';
			if ($type === 'long_text' || $type === 'json' || $type === 'textarea') {
				continue;
			}
			$list_columns[$col_slug] = $col_data;
		}

		$title_slug = '';
		foreach ($list_columns as $slug => $data) {
			if (isset($data['type']) && in_array($data['type'], array('text', 'email'))) {
				$title_slug = $slug;
				break;
			}
		}
		if (empty($title_slug) && !empty($list_columns)) {
			reset($list_columns);
			$title_slug = key($list_columns);
		}
		
		if ($title_slug) {
			unset($list_columns[$title_slug]);
		}

		$col_count = count($list_columns);
		if ($col_count === 0) {
			$grid_class = 'grid-cols-[1fr_40px]';
		} else {
			$grid_class = 'grid-cols-[2fr_repeat(' . $col_count . ',minmax(0,1fr))_40px]';
		}

		return array(
			'title_slug' => $title_slug,
			'columns' => $list_columns,
			'grid_class' => $grid_class
		);
	}

	private function create_organism($table_slug, $table_label, $schema)
	{
		$grid_info = $this->compute_list_grid($schema);
		$title_slug = $grid_info['title_slug'];
		$columns = $grid_info['columns'];
		$grid_class = $grid_info['grid_class'];

		$portal_slug = isset($schema['__table_info']['portal_settings']['slug']) && !empty($schema['__table_info']['portal_settings']['slug']) ? $schema['__table_info']['portal_settings']['slug'] : $table_slug;

		$html_content = '<!-- wp:ska-builder/container {"tagName":"a","tailwindClasses":"ska-organism-row grid ' . $grid_class . ' gap-4 items-center p-4 border-b border-slate-100 bg-white hover:bg-slate-50 transition-colors cursor-pointer group no-underline","htmlAttributes":[{"key":"href","value":"/' . esc_attr($portal_slug) . '/{{id}}/"}]} -->' . "\n";
		
		// Col 1 Blocks & HTML
		$col1_blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center gap-3 overflow-hidden'),
				'innerBlocks' => array(
					array(
						'name' => 'ska-builder/container',
						'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0'),
						'innerBlocks' => array(
							array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '#', 'tailwindClasses' => 'font-bold text-sm'))
						)
					),
					array(
						'name' => 'ska-builder/container',
						'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex flex-col gap-0.5 min-w-0'),
						'innerBlocks' => array()
					)
				)
			)
		);
		
		$col1_html = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center gap-3 overflow-hidden"} -->' . "\n";
		$col1_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"} -->' . "\n";
		$col1_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"#","tailwindClasses":"font-bold text-sm"} /-->' . "\n";
		$col1_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$col1_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col gap-0.5 min-w-0"} -->' . "\n";
		
		if ($title_slug) {
			$col1_blocks[0]['innerBlocks'][1]['innerBlocks'][] = array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '{{' . $title_slug . '}}', 'tailwindClasses' => 'font-bold text-slate-800 line-clamp-1'));
			$col1_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"{{' . $title_slug . '}}","tailwindClasses":"font-bold text-slate-800 line-clamp-1"} /-->' . "\n";
		}
		
		$col1_blocks[0]['innerBlocks'][1]['innerBlocks'][] = array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => 'ID: [' . $table_slug . '.id]', 'tailwindClasses' => 'text-xs text-slate-500 line-clamp-1'));
		$col1_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"ID: [' . $table_slug . '.id]","tailwindClasses":"text-xs text-slate-500 line-clamp-1"} /-->' . "\n";
		
		$col1_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$col1_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		$html_content .= $col1_html;

		// Other Columns
		$other_cols_blocks = array();
		$other_cols_html = '';

		foreach ($columns as $slug => $data) {
			$type = isset($data['type']) ? $data['type'] : 'text';
			if ($type === 'select' || $type === 'radio' || $type === 'boolean') {
				$other_cols_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center"} -->' . "\n";
				$other_cols_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"{{' . $slug . '}}","tailwindClasses":"px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700"} /-->' . "\n";
				$other_cols_html .= '<!-- /wp:ska-builder/container -->' . "\n";

				$other_cols_blocks[] = array(
					'name' => 'ska-builder/container',
					'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center'),
					'innerBlocks' => array(
						array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '{{' . $slug . '}}', 'tailwindClasses' => 'px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700'))
					)
				);
			} else {
				$other_cols_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center"} -->' . "\n";
				$other_cols_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"{{' . $slug . '}}","tailwindClasses":"text-sm text-slate-600 line-clamp-1"} /-->' . "\n";
				$other_cols_html .= '<!-- /wp:ska-builder/container -->' . "\n";

				$other_cols_blocks[] = array(
					'name' => 'ska-builder/container',
					'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center'),
					'innerBlocks' => array(
						array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '{{' . $slug . '}}', 'tailwindClasses' => 'text-sm text-slate-600 line-clamp-1'))
					)
				);
			}
		}

		// Action Column
		$action_html = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center justify-end"} -->' . "\n";
		$action_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":">","tailwindClasses":"text-slate-400 group-hover:translate-x-1 group-hover:text-indigo-600 transition-all font-bold"} /-->' . "\n";
		$action_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		$other_cols_blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center justify-end'),
			'innerBlocks' => array(
				array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '>', 'tailwindClasses' => 'text-slate-400 group-hover:translate-x-1 group-hover:text-indigo-600 transition-all font-bold'))
			)
		);

		$html_content .= $other_cols_html . $action_html;
		$html_content .= '<!-- /wp:ska-builder/container -->';

		$blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array(
					'tagName' => 'a',
					'tailwindClasses' => 'ska-organism-row grid ' . $grid_class . ' gap-4 items-center p-4 border-b border-slate-100 bg-white hover:bg-slate-50 transition-colors cursor-pointer group no-underline',
					'htmlAttributes' => array(
						array('key' => 'href', 'value' => '/' . $portal_slug . '/{{id}}/')
					)
				),
				'innerBlocks' => array_merge($col1_blocks, $other_cols_blocks)
			)
		);

		$record_data = array(
			'type' => 'organism',
			'name' => 'Row: ' . $table_label,
			'json_content' => wp_json_encode($blocks),
			'html_content' => $html_content
		);

		$result = apply_filters('ska_data_insert_record', false, $record_data, 'ska_data_sys_organisms');
		if (!$result) {
			return new \WP_Error('insert_failed', 'Không thể tạo Organism.');
		}

		return $result;
	}

	private function create_list_view($table_name, $table_slug, $table_label, $organism_id, $folder_id = '', $portal_slug = '', $schema = array())
	{
		$grid_info = $this->compute_list_grid($schema);
		$columns = $grid_info['columns'];
		$grid_class = $grid_info['grid_class'];
		$real_portal_slug = !empty($portal_slug) ? $portal_slug : $table_slug;

		// 1. App Header (Title + Add Button)
		$header_html = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6"} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col"} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"h2","content":"Quản lý ' . esc_attr($table_label) . '","tailwindClasses":"text-2xl font-bold text-slate-900"} /-->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"p","content":"Quản lý và theo dõi tiến độ các tác vụ.","tailwindClasses":"text-slate-500 text-sm"} /-->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/container {"tagName":"a","tailwindClasses":"px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition no-underline flex items-center justify-center","htmlAttributes":[{"key":"href","value":"/' . esc_attr($real_portal_slug) . '/create/"}]} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"+ Thêm Bản Ghi Mới","tailwindClasses":""} /-->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		// 2. Table Card Container
		$card_html = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden"} -->' . "\n";
		
		// 3. Table Header Row (Grid)
		$thead_html = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"hidden md:grid ' . $grid_class . ' gap-4 items-center p-4 border-b border-slate-200 bg-slate-50/50"} -->' . "\n";
		$thead_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"TÊN BẢN GHI / THÔNG TIN","tailwindClasses":"text-xs font-bold text-slate-400 uppercase tracking-wider"} /-->' . "\n";
		foreach ($columns as $slug => $data) {
			$col_label = isset($data['label']) ? $data['label'] : $slug;
			$thead_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"' . esc_attr($col_label) . '","tailwindClasses":"text-xs font-bold text-slate-400 uppercase tracking-wider"} /-->' . "\n";
		}
		$thead_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"HÀNH ĐỘNG","tailwindClasses":"text-xs font-bold text-slate-400 uppercase tracking-wider text-right"} /-->' . "\n";
		$thead_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		// 4. The Loop
		$loop_html = '<!-- wp:ska-builder/loop {"sourceTable":"' . esc_attr($table_name) . '","slots":[{"organismId":"' . $organism_id . '","condition":""}]} /-->' . "\n";

		$card_html .= $thead_html . $loop_html;
		$card_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		$html_content = $header_html . $card_html;

		$blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6'),
				'innerBlocks' => array()
			),
			array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden'),
				'innerBlocks' => array(
					array(
						'name' => 'ska-builder/loop',
						'attributes' => array(
							'sourceTable' => $table_name,
							'slots' => array(
								array('organismId' => (string)$organism_id, 'condition' => '')
							)
						),
						'innerBlocks' => array()
					)
				)
			)
		);

		// 1. Tạo Organism chứa nội dung List View
		$org_data = array(
			'type' => 'archive',
			'name' => 'List View: ' . $table_label,
			'json_content' => wp_json_encode($blocks),
			'html_content' => $html_content
		);

		$list_org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');
		if (!$list_org_id) {
			return new \WP_Error('insert_failed', 'Không thể tạo List View Organism.');
		}

		// 2. Tạo Theme Template ánh xạ tới Organism vừa tạo
		$template_data = array(
			'name' => 'List View: ' . $table_label,
			'location' => 'app_layout',
			'organism_id' => $list_org_id,
			'conditions' => wp_json_encode(array(
				'rules' => array(
					array('rule' => 'specific_portal_list', 'value' => $real_portal_slug, 'type' => 'include')
				),
				'folder_id' => $folder_id
			)),
			'is_active' => 1
		);

		$result = apply_filters('ska_data_insert_record', false, $template_data, 'ska_data_sys_theme_templates');
		if (!$result) {
			return new \WP_Error('insert_failed', 'Không thể tạo List View Template.');
		}

		return $result;
	}

	private function build_form_layout($table_slug, $table_label, $schema, $portal_slug = '', $mode = 'insert')
	{
		$real_portal_slug = !empty($portal_slug) ? $portal_slug : $table_slug;
		$form_action_id = ($mode === 'insert') ? 'insert_' . $table_slug : 'update_' . $table_slug;
		
		$blocks = array();
		
		// 1. Header Navigation
		$header_blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center gap-3 text-sm text-slate-500'),
				'innerBlocks' => array(
					array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => 'SKA', 'tailwindClasses' => 'font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-500')),
					array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '|', 'tailwindClasses' => 'text-slate-300 font-light text-sm mx-1')),
					array(
						'name' => 'ska-builder/container',
						'attributes' => array('tagName' => 'a', 'tailwindClasses' => 'hover:bg-slate-100 px-2 py-1 rounded transition-colors flex items-center gap-1.5 no-underline', 'htmlAttributes' => array(array('key' => 'href', 'value' => '/' . $real_portal_slug . '/'))),
						'innerBlocks' => array(
							array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '← Quay lại danh sách', 'tailwindClasses' => ''))
						)
					),
					array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => '/', 'tailwindClasses' => 'text-slate-300')),
					array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => 'Chi tiết: ' . $table_label, 'tailwindClasses' => 'text-slate-700 font-medium truncate max-w-[200px]'))
				)
			),
			array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center gap-2'),
				'innerBlocks' => array(
					array(
						'name' => 'ska-builder/button',
						'attributes' => array(
							'tagName' => 'button',
							'actionType' => 'submit',
							'text' => 'Lưu Thay Đổi',
							'tailwindClasses' => 'bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-500 bg-[length:200%_auto] hover:bg-right text-white px-5 py-2 rounded shadow-sm text-sm font-bold transition-all min-w-[120px] opacity-90 hover:opacity-100 border-none cursor-pointer'
						),
						'innerBlocks' => array()
					)
				)
			)
		);
		
		$header_html = '<!-- wp:ska-builder/container {"tagName":"header","tailwindClasses":"h-14 border-b border-slate-100 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md sticky top-0 z-50 shrink-0"} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center gap-3 text-sm text-slate-500"} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"SKA","tailwindClasses":"font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-500"} /-->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"|","tailwindClasses":"text-slate-300 font-light text-sm mx-1"} /-->' . "\n";
		$header_html .= '<!-- wp:ska-builder/container {"tagName":"a","tailwindClasses":"hover:bg-slate-100 px-2 py-1 rounded transition-colors flex items-center gap-1.5 no-underline","htmlAttributes":[{"key":"href","value":"/' . esc_attr($real_portal_slug) . '/"}]} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"← Quay lại danh sách","tailwindClasses":""} /-->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"/","tailwindClasses":"text-slate-300"} /-->' . "\n";
		$header_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"Chi tiết: ' . esc_attr($table_label) . '","tailwindClasses":"text-slate-700 font-medium truncate max-w-[200px]"} /-->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center gap-2"} -->' . "\n";
		$header_html .= '<!-- wp:ska-builder/button {"tagName":"button","actionType":"submit","text":"Lưu Thay Đổi","tailwindClasses":"bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-500 bg-[length:200%_auto] hover:bg-right text-white px-5 py-2 rounded shadow-sm text-sm font-bold transition-all min-w-[120px] opacity-90 hover:opacity-100 border-none cursor-pointer"} /-->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$header_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		// 2. Main Content
		$main_blocks = array();
		$main_html = '';

		// Page Title
		$title_text = ($mode === 'insert') ? 'Tạo mới: ' . $table_label : $table_label;
		$main_blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'mb-8 border-b border-slate-100 pb-6'),
			'innerBlocks' => array(
				array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'h1', 'content' => $title_text, 'tailwindClasses' => 'w-full text-4xl font-extrabold text-slate-900 border-none outline-none bg-transparent resize-none py-2'))
			)
		);
		$main_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"mb-8 border-b border-slate-100 pb-6"} -->' . "\n";
		$main_html .= '<!-- wp:ska-builder/text {"tagName":"h1","content":"' . esc_attr($title_text) . '","tailwindClasses":"w-full text-4xl font-extrabold text-slate-900 border-none outline-none bg-transparent resize-none py-2"} /-->' . "\n";
		$main_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		// Form Fields
		$meta_blocks = array();
		$meta_html = "";
		$long_text_blocks = array();
		$long_text_html = "";

		if ($mode === 'update') {
			$meta_blocks[] = array('name' => 'ska-builder/input', 'attributes' => array('fieldName' => 'id', 'inputType' => 'hidden', 'fieldValue' => '{{id}}'), 'innerBlocks' => array());
			$meta_html .= '<!-- wp:ska-builder/input {"fieldName":"id","inputType":"hidden","fieldValue":"{{id}}"} /-->' . "\n";
		}

		foreach ($schema as $col_slug => $col_data) {
			if ($col_slug === '__table_info' || $col_slug === 'id' || $col_slug === 'created_at') continue;
			$col_label = isset($col_data['label']) ? $col_data['label'] : $col_slug;
			$type = isset($col_data['type']) ? $col_data['type'] : 'text';
			$is_required = !empty($col_data['required']);
			$val_binding = ($mode === 'update') ? '{{' . $col_slug . '}}' : '';

			if ($type === 'long_text' || $type === 'textarea') {
				$long_text_blocks[] = array(
					'name' => 'ska-builder/form-rich-text',
					'attributes' => array(
						'field' => $col_slug,
						'label' => $col_label,
						'tailwindClasses' => 'w-full mb-12'
					),
					'innerBlocks' => array()
				);
				$long_text_html .= '<!-- wp:ska-builder/form-rich-text {"field":"' . esc_attr($col_slug) . '","label":"' . esc_attr($col_label) . '","tailwindClasses":"w-full mb-12"} /-->' . "\n";
			} else {
				$input_classes = 'w-full py-1.5 px-3 rounded-md appearance-none text-slate-800 font-medium hover:bg-slate-100 focus:bg-white focus:ring-1 focus:ring-slate-200 border border-transparent focus:border-slate-300 transition-colors cursor-pointer focus:cursor-text outline-none';
				$field_block = array();
				$field_html = "";
				
				if ($type === 'select') {
					$options_raw = isset($col_data['options']) ? $col_data['options'] : '';
					$options_lines = array();
					if (!empty($options_raw)) {
						$opts = array_map('trim', explode(',', $options_raw));
						foreach ($opts as $o) {
							if (!empty($o)) $options_lines[] = "{$o}:{$o}";
						}
					}
					$options_text = implode("\n", $options_lines);
					$field_block = array('name' => 'ska-builder/select', 'attributes' => array('fieldName' => $col_slug, 'optionsText' => $options_text, 'displayStyle' => 'dropdown', 'isRequired' => $is_required, 'tailwindClasses' => $input_classes), 'innerBlocks' => array());
					$opt_escaped = str_replace("\n", "\\n", $options_text);
					$field_html = '<!-- wp:ska-builder/select {"fieldName":"' . esc_attr($col_slug) . '","optionsText":"' . esc_attr($opt_escaped) . '","displayStyle":"dropdown","isRequired":' . ($is_required ? 'true' : 'false') . ',"tailwindClasses":"' . esc_attr($input_classes) . '"} /-->' . "\n";
				} elseif ($type === 'relation') {
					$target_table = isset($col_data['options']) ? $col_data['options'] : '';
					$clean_target_table = str_replace(get_option('ska_data_prefix', 'ska_data_'), '', $target_table);
					$field_block = array('name' => 'ska-builder/select', 'attributes' => array('fieldName' => $col_slug, 'optionsText' => '', 'displayStyle' => 'dropdown', 'isRequired' => $is_required, 'skaDynamicBinding' => '{{#foreach ' . $clean_target_table . '.id}}', 'tailwindClasses' => $input_classes), 'innerBlocks' => array());
					$field_html = '<!-- wp:ska-builder/select {"fieldName":"' . esc_attr($col_slug) . '","optionsText":"","displayStyle":"dropdown","isRequired":' . ($is_required ? 'true' : 'false') . ',"skaDynamicBinding":"{{#foreach ' . esc_attr($clean_target_table) . '.id}}","tailwindClasses":"' . esc_attr($input_classes) . '"} /-->' . "\n";
				} else {
					$input_type = in_array($type, array('number', 'date', 'email', 'media_gallery')) ? $type : 'text';
					if ($input_type === 'media_gallery') $input_type = 'text'; // Fallback
					$field_block = array('name' => 'ska-builder/input', 'attributes' => array('fieldName' => $col_slug, 'inputType' => $input_type, 'placeholder' => 'Trống', 'isRequired' => $is_required, 'fieldValue' => $val_binding, 'tailwindClasses' => $input_classes), 'innerBlocks' => array());
					$field_html = '<!-- wp:ska-builder/input {"fieldName":"' . esc_attr($col_slug) . '","inputType":"' . esc_attr($input_type) . '","placeholder":"Trống","isRequired":' . ($is_required ? 'true' : 'false') . ',"fieldValue":"' . esc_attr($val_binding) . '","tailwindClasses":"' . esc_attr($input_classes) . '"} /-->' . "\n";
				}

				$meta_blocks[] = array(
					'name' => 'ska-builder/container',
					'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex items-center group min-h-[40px]'),
					'innerBlocks' => array(
						array(
							'name' => 'ska-builder/container',
							'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'w-48 text-slate-500 flex items-center gap-2 shrink-0'),
							'innerBlocks' => array(
								array('name' => 'ska-builder/text', 'attributes' => array('tagName' => 'span', 'content' => $col_label, 'tailwindClasses' => 'text-sm font-medium'))
							)
						),
						array(
							'name' => 'ska-builder/container',
							'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex-1'),
							'innerBlocks' => array($field_block)
						)
					)
				);
				$meta_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center group min-h-[40px]"} -->' . "\n";
				$meta_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"w-48 text-slate-500 flex items-center gap-2 shrink-0"} -->' . "\n";
				$meta_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"' . esc_attr($col_label) . '","tailwindClasses":"text-sm font-medium"} /-->' . "\n";
				$meta_html .= '<!-- /wp:ska-builder/container -->' . "\n";
				$meta_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex-1"} -->' . "\n";
				$meta_html .= $field_html;
				$meta_html .= '<!-- /wp:ska-builder/container -->' . "\n";
				$meta_html .= '<!-- /wp:ska-builder/container -->' . "\n";
			}
		}

		if (!empty($meta_blocks)) {
			$main_blocks[] = array(
				'name' => 'ska-builder/container',
				'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'flex flex-col gap-1.5 mb-10 text-sm border-b border-slate-100 pb-8'),
				'innerBlocks' => $meta_blocks
			);
			$main_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col gap-1.5 mb-10 text-sm border-b border-slate-100 pb-8"} -->' . "\n";
			$main_html .= $meta_html;
			$main_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		}

		if (!empty($long_text_blocks)) {
			foreach ($long_text_blocks as $lt_block) {
				$main_blocks[] = $lt_block;
			}
			$main_html .= $long_text_html;
		}

		// End max-w-4xl and main tags are now handled in $final_html

		$blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array('tagName' => 'header', 'tailwindClasses' => 'h-14 border-b border-slate-100 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md sticky top-0 z-50 shrink-0'),
			'innerBlocks' => $header_blocks
		);
		$blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array('tagName' => 'main', 'tailwindClasses' => 'flex-1 bg-slate-50'),
			'innerBlocks' => array(
				array(
					'name' => 'ska-builder/container',
					'attributes' => array('tagName' => 'div', 'tailwindClasses' => 'max-w-4xl mx-auto px-6 md:px-12 pt-12 pb-32 bg-white min-h-screen shadow-sm'),
					'innerBlocks' => $main_blocks
				)
			)
		);

		$master_blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array(
					'tagName' => 'form',
					'isSkaForm' => true,
					'formActionId' => $form_action_id,
					'tailwindClasses' => 'flex flex-col min-h-screen'
				),
				'innerBlocks' => $blocks
			)
		);

		$final_html = '<!-- wp:ska-builder/container {"tagName":"form","isSkaForm":true,"formActionId":"' . esc_attr($form_action_id) . '","tailwindClasses":"flex flex-col min-h-screen"} -->' . "\n";
		$final_html .= $header_html;
		$final_html .= '<!-- wp:ska-builder/container {"tagName":"main","tailwindClasses":"flex-1 bg-slate-50"} -->' . "\n";
		$final_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"max-w-4xl mx-auto px-6 md:px-12 pt-12 pb-32 bg-white min-h-screen shadow-sm"} -->' . "\n";
		$final_html .= $main_html;
		$final_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$final_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$final_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		return array(
			'json_content' => wp_json_encode($master_blocks),
			'html_content' => $final_html
		);
	}

	private function create_detail_view($table_slug, $table_label, $schema, $folder_id = '', $portal_slug = '')
	{
		$layout = $this->build_form_layout($table_slug, $table_label, $schema, $portal_slug, 'update');

		$org_data = array(
			'type' => 'single',
			'name' => 'Detail View: ' . $table_label,
			'json_content' => $layout['json_content'],
			'html_content' => $layout['html_content']
		);

		$detail_org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');
		if (!$detail_org_id) return new \WP_Error('insert_failed', 'Không thể tạo Detail View Organism.');

		$template_data = array(
			'name' => 'Detail View: ' . $table_label,
			'location' => 'app_layout',
			'organism_id' => $detail_org_id,
			'conditions' => wp_json_encode(array(
				'rules' => array(
					array('rule' => 'specific_portal_detail', 'value' => !empty($portal_slug) ? $portal_slug : $table_slug, 'type' => 'include')
				),
				'folder_id' => $folder_id
			)),
			'is_active' => 1
		);

		$result = apply_filters('ska_data_insert_record', false, $template_data, 'ska_data_sys_theme_templates');
		if (!$result) return new \WP_Error('insert_failed', 'Không thể tạo Detail View Template.');

		return $result;
	}

	private function create_insert_view($table_slug, $table_label, $schema, $folder_id = '', $portal_slug = '')
	{
		$layout = $this->build_form_layout($table_slug, $table_label, $schema, $portal_slug, 'insert');

		$org_data = array(
			'type' => 'single',
			'name' => 'Create View: ' . $table_label,
			'json_content' => $layout['json_content'],
			'html_content' => $layout['html_content']
		);

		$create_org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');
		if (!$create_org_id) return new \WP_Error('insert_failed', 'Không thể tạo Create View Organism.');

		$template_data = array(
			'name' => 'Create View: ' . $table_label,
			'location' => 'app_layout',
			'organism_id' => $create_org_id,
			'conditions' => wp_json_encode(array(
				'rules' => array(
					array('rule' => 'specific_portal_create', 'value' => !empty($portal_slug) ? $portal_slug : $table_slug, 'type' => 'include')
				),
				'folder_id' => $folder_id
			)),
			'is_active' => 1
		);

		$result = apply_filters('ska_data_insert_record', false, $template_data, 'ska_data_sys_theme_templates');
		if (!$result) return new \WP_Error('insert_failed', 'Không thể tạo Create View Template.');

		return $result;
	}
}

