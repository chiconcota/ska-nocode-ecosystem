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

		// 4. Làm mới JSON Cache cho Organisms sau khi tạo mới để Tailwind Compiler nhận diện class
		if (class_exists('\Ska\Design\Api\Organisms_API')) {
			\Ska\Design\Api\Organisms_API::get_instance()->export_physical_cache();
		}

		return array(
			'organism_id' => $organism_id,
			'list_view_id' => $list_view_id,
			'detail_view_id' => $detail_view_id,
			'portal_url' => home_url('/' . $portal_slug . '/'),
			'theme_builder_url' => admin_url('admin.php?page=ska-theme-builder&folder=' . $folder_id),
			'list_view_editor_url' => admin_url('admin.php?page=ska-theme-builder-editor&template_id=' . $list_view_id),
			'detail_view_editor_url' => admin_url('admin.php?page=ska-theme-builder-editor&template_id=' . $detail_view_id),
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

	private function create_detail_view($table_slug, $table_label, $schema, $folder_id = '', $portal_slug = '')
	{
		$form_blocks = array();
		$form_html = "";

		// 1. Thêm input hidden cho ID
		$form_blocks[] = array(
			'name' => 'ska-builder/input',
			'attributes' => array(
				'fieldName' => 'id',
				'inputType' => 'hidden',
				'fieldValue' => '{{id}}'
			),
			'innerBlocks' => array()
		);
		$form_html .= '<!-- wp:ska-builder/input {"fieldName":"id","inputType":"hidden","fieldValue":"{{id}}"} /-->' . "\n";

		// 2. Quét schema để sinh các input fields
		$temp_grid_blocks = array();
		$temp_grid_html = "";

		foreach ($schema as $col_slug => $col_data) {
			if ($col_slug === '__table_info' || $col_slug === 'id' || $col_slug === 'created_at') {
				continue;
			}

			$col_label = isset($col_data['label']) ? $col_data['label'] : $col_slug;
			$type = isset($col_data['type']) ? $col_data['type'] : 'text';

			if ($type === 'long_text') {
				// Đóng grid cũ nếu có
				if (!empty($temp_grid_blocks)) {
					$form_blocks[] = array(
						'name' => 'ska-builder/container',
						'attributes' => array(
							'tagName' => 'div',
							'tailwindClasses' => 'grid grid-cols-1 md:grid-cols-2 gap-6'
						),
						'innerBlocks' => $temp_grid_blocks
					);
					$form_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"grid grid-cols-1 md:grid-cols-2 gap-6"} -->' . "\n";
					$form_html .= $temp_grid_html;
					$form_html .= '<!-- /wp:ska-builder/container -->' . "\n";

					$temp_grid_blocks = array();
					$temp_grid_html = "";
				}

				// Thêm block Rich Text
				$form_blocks[] = array(
					'name' => 'ska-builder/form-rich-text',
					'attributes' => array(
						'field' => $col_slug,
						'label' => $col_label
					),
					'innerBlocks' => array()
				);
				$form_html .= '<!-- wp:ska-builder/form-rich-text {"field":"' . esc_attr($col_slug) . '","label":"' . esc_attr($col_label) . '"} /-->' . "\n";
			} else {
				$field_block = array();
				$field_html = "";

				if ($type === 'select') {
					$options_raw = isset($col_data['options']) ? $col_data['options'] : '';
					$options_lines = array();
					if (!empty($options_raw)) {
						$opts = array_map('trim', explode(',', $options_raw));
						foreach ($opts as $o) {
							if (!empty($o)) {
								$options_lines[] = "{$o}:{$o}";
							}
						}
					}
					$options_text = implode("\n", $options_lines);

					$field_block = array(
						'name' => 'ska-builder/select',
						'attributes' => array(
							'fieldName' => $col_slug,
							'optionsText' => $options_text,
							'displayStyle' => 'dropdown',
							'isRequired' => !empty($col_data['required']),
							'tailwindClasses' => 'w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none cursor-pointer bg-slate-50/50 focus:bg-white'
						),
						'innerBlocks' => array()
					);
					$opt_escaped = str_replace("\n", "\\n", $options_text);
					$field_html = '<!-- wp:ska-builder/select {"fieldName":"' . esc_attr($col_slug) . '","optionsText":"' . esc_attr($opt_escaped) . '","displayStyle":"dropdown","isRequired":' . (!empty($col_data['required']) ? 'true' : 'false') . ',"tailwindClasses":"w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none cursor-pointer bg-slate-50/50 focus:bg-white"} /-->';
				} elseif ($type === 'relation') {
					$target_table = isset($col_data['options']) ? $col_data['options'] : '';
					global $wpdb;
					$clean_target_table = str_replace($wpdb->prefix, '', $target_table);

					$field_block = array(
						'name' => 'ska-builder/select',
						'attributes' => array(
							'fieldName' => $col_slug,
							'optionsText' => '',
							'displayStyle' => 'dropdown',
							'isRequired' => !empty($col_data['required']),
							'skaDynamicBinding' => '{{#foreach ' . $clean_target_table . '.id}}',
							'tailwindClasses' => 'w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none cursor-pointer bg-slate-50/50 focus:bg-white'
						),
						'innerBlocks' => array()
					);
					$field_html = '<!-- wp:ska-builder/select {"fieldName":"' . esc_attr($col_slug) . '","optionsText":"","displayStyle":"dropdown","isRequired":' . (!empty($col_data['required']) ? 'true' : 'false') . ',"skaDynamicBinding":"{{#foreach ' . esc_attr($clean_target_table) . '.id}}","tailwindClasses":"w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none cursor-pointer bg-slate-50/50 focus:bg-white"} /-->';
				} else {
					$input_type = in_array($type, array('number', 'date', 'email')) ? $type : 'text';
					
					$field_block = array(
						'name' => 'ska-builder/input',
						'attributes' => array(
							'fieldName' => $col_slug,
							'inputType' => $input_type,
							'placeholder' => 'Nhập ' . $col_label,
							'isRequired' => !empty($col_data['required']),
							'fieldValue' => '{{' . $col_slug . '}}',
							'tailwindClasses' => 'w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none bg-slate-50/50 focus:bg-white'
						),
						'innerBlocks' => array()
					);
					$field_html = '<!-- wp:ska-builder/input {"fieldName":"' . esc_attr($col_slug) . '","inputType":"' . esc_attr($input_type) . '","placeholder":"Nhập ' . esc_attr($col_label) . '","isRequired":' . (!empty($col_data['required']) ? 'true' : 'false') . ',"fieldValue":"{{' . esc_attr($col_slug) . '}}","tailwindClasses":"w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all outline-none bg-slate-50/50 focus:bg-white"} /-->';
				}

				// Đưa vào mảng tạm grid
				$temp_grid_blocks[] = array(
					'name' => 'ska-builder/container',
					'attributes' => array(
						'tagName' => 'div',
						'tailwindClasses' => 'flex flex-col gap-2'
					),
					'innerBlocks' => array(
						array(
							'name' => 'ska-builder/text',
							'attributes' => array(
								'tagName' => 'label',
								'content' => $col_label,
								'tailwindClasses' => 'text-xs font-bold uppercase tracking-wider text-slate-500'
							),
							'innerBlocks' => array()
						),
						$field_block
					)
				);

				$temp_grid_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col gap-2"} -->' . "\n";
				$temp_grid_html .= '<!-- wp:ska-builder/text {"tagName":"label","content":"' . esc_attr($col_label) . '","tailwindClasses":"text-xs font-bold uppercase tracking-wider text-slate-500"} /-->' . "\n";
				$temp_grid_html .= $field_html . "\n";
				$temp_grid_html .= '<!-- /wp:ska-builder/container -->' . "\n";
			}
		}

		// Đóng grid cuối cùng nếu còn
		if (!empty($temp_grid_blocks)) {
			$form_blocks[] = array(
				'name' => 'ska-builder/container',
				'attributes' => array(
					'tagName' => 'div',
					'tailwindClasses' => 'grid grid-cols-1 md:grid-cols-2 gap-6'
				),
				'innerBlocks' => $temp_grid_blocks
			);
			$form_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"grid grid-cols-1 md:grid-cols-2 gap-6"} -->' . "\n";
			$form_html .= $temp_grid_html;
			$form_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		}

		// 3. Actions Container ở cuối form
		$actions_blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array(
					'tagName' => 'a',
					'tailwindClasses' => 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition cursor-pointer no-underline flex items-center justify-center',
					'htmlAttributes' => array(
						array('key' => 'href', 'value' => '/' . (!empty($portal_slug) ? $portal_slug : $table_slug) . '/')
					)
				),
				'innerBlocks' => array(
					array(
						'name' => 'ska-builder/text',
						'attributes' => array(
							'tagName' => 'span',
							'content' => 'Hủy bỏ',
							'tailwindClasses' => ''
						),
						'innerBlocks' => array()
					)
				)
			),
			array(
				'name' => 'ska-builder/button',
				'attributes' => array(
					'tagName' => 'button',
					'actionType' => 'submit',
					'text' => 'Lưu Thay Đổi',
					'tailwindClasses' => 'px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm hover:shadow transition cursor-pointer flex items-center justify-center gap-2'
				),
				'innerBlocks' => array()
			)
		);

		$form_blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array(
				'tagName' => 'div',
				'tailwindClasses' => 'flex items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-8'
			),
			'innerBlocks' => $actions_blocks
		);

		$form_html .= '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-8"} -->' . "\n";
		$form_html .= '<!-- wp:ska-builder/container {"tagName":"a","tailwindClasses":"px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition cursor-pointer no-underline flex items-center justify-center","htmlAttributes":[{"key":"href","value":"/' . esc_attr(!empty($portal_slug) ? $portal_slug : $table_slug) . '/"}]} -->' . "\n";
		$form_html .= '<!-- wp:ska-builder/text {"tagName":"span","content":"Hủy bỏ","tailwindClasses":""} /-->' . "\n";
		$form_html .= '<!-- /wp:ska-builder/container -->' . "\n";
		$form_html .= '<!-- wp:ska-builder/button {"tagName":"button","actionType":"submit","text":"Lưu Thay Đổi","tailwindClasses":"px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm hover:shadow transition cursor-pointer flex items-center justify-center gap-2"} /-->' . "\n";
		$form_html .= '<!-- /wp:ska-builder/container -->' . "\n";

		// 4. Bọc tất cả vào Layout
		$blocks = array();

		// Header Container
		$header_blocks = array(
			array(
				'name' => 'ska-builder/container',
				'attributes' => array(
					'tagName' => 'a',
					'tailwindClasses' => 'text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition flex items-center gap-1 no-underline w-fit mb-2',
					'htmlAttributes' => array(
						array('key' => 'href', 'value' => '/' . (!empty($portal_slug) ? $portal_slug : $table_slug) . '/')
					)
				),
				'innerBlocks' => array(
					array(
						'name' => 'ska-builder/text',
						'attributes' => array(
							'tagName' => 'span',
							'content' => '← Quay lại danh sách',
							'tailwindClasses' => ''
						),
						'innerBlocks' => array()
					)
				)
			),
			array(
				'name' => 'ska-builder/text',
				'attributes' => array(
					'tagName' => 'h2',
					'content' => 'Chi tiết: ' . $table_label,
					'tailwindClasses' => 'text-2xl font-extrabold text-slate-900 tracking-tight'
				),
				'innerBlocks' => array()
			)
		);

		$blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array(
				'tagName' => 'div',
				'tailwindClasses' => 'flex flex-col gap-1 pb-6 mb-8 border-b border-slate-100'
			),
			'innerBlocks' => $header_blocks
		);

		$blocks[] = array(
			'name' => 'ska-builder/container',
			'attributes' => array(
				'tagName' => 'form',
				'isSkaForm' => true,
				'formActionId' => 'update_' . $table_slug,
				'tailwindClasses' => 'space-y-6 max-w-4xl bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm mx-auto'
			),
			'innerBlocks' => $form_blocks
		);

		$html_content = '<!-- wp:ska-builder/container {"tagName":"div","tailwindClasses":"flex flex-col gap-1 pb-6 mb-8 border-b border-slate-100"} -->' . "\n";
		$html_content .= '<!-- wp:ska-builder/container {"tagName":"a","tailwindClasses":"text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition flex items-center gap-1 no-underline w-fit mb-2","htmlAttributes":[{"key":"href","value":"/' . esc_attr(!empty($portal_slug) ? $portal_slug : $table_slug) . '/"}]} -->' . "\n";
		$html_content .= '<!-- wp:ska-builder/text {"tagName":"span","content":"← Quay lại danh sách","tailwindClasses":""} /-->' . "\n";
		$html_content .= '<!-- /wp:ska-builder/container -->' . "\n";
		$html_content .= '<!-- wp:ska-builder/text {"tagName":"h2","content":"Chi tiết: ' . esc_attr($table_label) . '","tailwindClasses":"text-2xl font-extrabold text-slate-900 tracking-tight"} /-->' . "\n";
		$html_content .= '<!-- /wp:ska-builder/container -->' . "\n";

		$html_content .= '<!-- wp:ska-builder/container {"tagName":"form","isSkaForm":true,"formActionId":"update_' . esc_attr($table_slug) . '","tailwindClasses":"space-y-6 max-w-4xl bg-white p-8 rounded-2xl border border-slate-200/80 shadow-sm mx-auto"} -->' . "\n";
		$html_content .= $form_html;
		$html_content .= '<!-- /wp:ska-builder/container -->' . "\n";

		// 5. Tạo Organism chứa nội dung Detail View
		$org_data = array(
			'type' => 'single',
			'name' => 'Detail View: ' . $table_label,
			'json_content' => wp_json_encode($blocks),
			'html_content' => $html_content
		);

		$detail_org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');
		if (!$detail_org_id) {
			return new \WP_Error('insert_failed', 'Không thể tạo Detail View Organism.');
		}

		// 6. Tạo Theme Template ánh xạ tới Organism vừa tạo
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
		if (!$result) {
			return new \WP_Error('insert_failed', 'Không thể tạo Detail View Template.');
		}

		return $result;
	}
}
