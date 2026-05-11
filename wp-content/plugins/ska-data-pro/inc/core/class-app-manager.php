<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class App_Manager
 * Quản lý vòng đời của Smart Object (App Blueprint).
 */
class App_Manager {

	const OPTION_NAME = 'ska_data_apps';
	const UNCATEGORIZED_APP = 'uncategorized';
	const SYSTEM_APP = 'ska_system';

	/**
	 * Khởi tạo Mặc định (Uncategorized)
	 */
	public static function init() {
		$apps = get_option( self::OPTION_NAME, false );
		if ( false === $apps || ! is_array( $apps ) ) {
			$apps = array();
		}

		$changed = false;
		if ( ! isset( $apps[ self::UNCATEGORIZED_APP ] ) ) {
			$apps[ self::UNCATEGORIZED_APP ] = array(
				'id'         => self::UNCATEGORIZED_APP,
				'name'       => 'Default Workspace',
				'icon'       => 'dashicons-portfolio',
				'created_at' => current_time( 'mysql' ),
			);
			$changed = true;
		}

		if ( ! isset( $apps[ self::SYSTEM_APP ] ) ) {
			$apps[ self::SYSTEM_APP ] = array(
				'id'         => self::SYSTEM_APP,
				'name'       => 'Site Management',
				'icon'       => 'dashicons-admin-generic',
				'created_at' => current_time( 'mysql' ),
			);
			$changed = true;
		}

		if ( $changed ) {
			update_option( self::OPTION_NAME, $apps );
		}
	}

	/**
	 * Tự động đồng bộ các bảng cũ (Migration)
	 */
	public static function maybe_run_migration() {
		$migrated = get_option( 'ska_data_app_migrated', false );
		if ( $migrated ) {
			self::maybe_create_system_tables();
			return;
		}

		self::init();
		
		$dictionary = get_option( 'ska_data_dictionary', array() );
		$apps       = get_option( self::OPTION_NAME, array() );
		$changed    = false;

		foreach ( $dictionary as $table_name => $schema ) {
			if ( ! isset( $schema['__table_info'] ) ) {
				continue;
			}

			$info = $schema['__table_info'];
			
			// Kiểm tra khóa 'group' cũ
			if ( isset( $info['group'] ) && ! isset( $info['app_id'] ) ) {
				$group_name = $info['group'];

				if ( empty( $group_name ) || $group_name === 'custom' ) {
					$app_id = self::UNCATEGORIZED_APP;
				} else {
					// Tạo một app_id dựa trên chuỗi group
					$app_id = 'app_' . substr( md5( $group_name ), 0, 8 );
					
					// Nếu chưa có, nạp mới vào hệ thống App
					if ( ! isset( $apps[ $app_id ] ) ) {
						$apps[ $app_id ] = array(
							'id'         => $app_id,
							'name'       => ucfirst( $group_name ),
							'icon'       => 'dashicons-portfolio',
							'created_at' => current_time( 'mysql' ),
						);
					}
				}

				// Ánh xạ sang cấu trúc mới
				$dictionary[ $table_name ]['__table_info']['app_id'] = $app_id;
				unset( $dictionary[ $table_name ]['__table_info']['group'] );
				$changed = true;
			}
		}

		if ( $changed ) {
			update_option( self::OPTION_NAME, $apps );
			update_option( 'ska_data_dictionary', $dictionary );
		}

		update_option( 'ska_data_app_migrated', true );
		self::maybe_create_system_tables();
	}

	/**
	 * Tự động tạo 3 Bảng Flat Tables hệ thống cho App_Site
	 */
	public static function maybe_create_system_tables() {
		$migrated_sys = get_option( 'ska_data_sys_tables_migrated', false );
		if ( $migrated_sys ) {
			// Tạm thời bỏ return để ép hệ thống quét lại dictionary và tạo bảng theme_templates
			// return;
		}

		$dictionary = get_option( 'ska_data_dictionary', array() );
		global $wpdb;

		$table_organisms = $wpdb->prefix . 'ska_data_sys_organisms';
		$table_templates = $wpdb->prefix . 'ska_data_sys_theme_templates';
		$table_presets   = $wpdb->prefix . 'ska_data_sys_presets';

		// Đảm bảo module Database đã sẵn sàng
		if ( ! class_exists( '\Ska\Data\Core\Database_Engine' ) ) {
			return;
		}

		self::init(); // Đảm bảo App ska_system đã tồn tại

		$db_engine = \Ska\Data\Core\Database_Engine::get_instance();
		$changed = false;

		// 1. Organisms Blocks
		$table_organisms_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_organisms ) );
		if ( $table_organisms_exists !== $table_organisms ) {
			// Nếu chưa có bảng trong MySQL thì tạo mới hoàn toàn
			$db_engine->create_custom_table( 'organisms', 'dashicons-layout', self::SYSTEM_APP );
			$db_engine->add_column( $table_organisms, 'Name', 'short_text' );
			$db_engine->add_column( $table_organisms, 'Title', 'short_text' );
			$db_engine->add_column( $table_organisms, 'Block_Name', 'short_text' );
			$db_engine->add_column( $table_organisms, 'JSON_Content', 'long_text' );
			$db_engine->add_column( $table_organisms, 'HTML_Content', 'long_text' );
			$changed = true;
		}

		// Luôn đảm bảo Dictionary có mặt
		if ( ! isset( $dictionary[ $table_organisms ] ) ) {
			$dictionary[ $table_organisms ] = array();
		}
		if ( ! isset( $dictionary[ $table_organisms ]['__table_info'] ) ) {
			$dictionary[ $table_organisms ]['__table_info'] = array(
				'name' => 'Sys Organisms',
				'icon' => 'dashicons-layout',
				'app_id' => self::SYSTEM_APP
			);
			$changed = true;
		}
		// Phục hồi Metadata các cột nếu bị mất
		$org_cols = array(
			'name' => array('label' => 'Name', 'type' => 'short_text', 'options' => ''),
			'title' => array('label' => 'Title', 'type' => 'short_text', 'options' => ''),
			'block_name' => array('label' => 'Block_Name', 'type' => 'short_text', 'options' => ''),
			'json_content' => array('label' => 'JSON_Content', 'type' => 'long_text', 'options' => ''),
			'html_content' => array('label' => 'HTML_Content', 'type' => 'long_text', 'options' => '')
		);
		foreach ( $org_cols as $col_slug => $col_data ) {
			if ( ! isset( $dictionary[ $table_organisms ][ $col_slug ] ) ) {
				$dictionary[ $table_organisms ][ $col_slug ] = $col_data;
				$changed = true;
			}
		}

		// Cập nhật ngay vào Database nếu có thay đổi
		if ( $changed ) {
			update_option( 'ska_data_dictionary', $dictionary );
			$changed = false;
		}

		// Tải lại dictionary mới nhất sau mỗi bước (phòng trường hợp db_engine thay đổi trực tiếp Database)
		$dictionary = get_option( 'ska_data_dictionary', array() );

		// 2. Theme Templates
		$table_templates_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_templates ) );
		if ( $table_templates_exists !== $table_templates ) {
			// Nếu chưa có bảng trong MySQL thì tạo mới hoàn toàn
			$db_engine->create_custom_table( 'theme_templates', 'dashicons-admin-appearance', self::SYSTEM_APP );
			$db_engine->add_column( $table_templates, 'Name', 'short_text' );
			$db_engine->add_column( $table_templates, 'Location', 'select', 'header, footer, single, archive, 404, app_layout, custom' );
			$db_engine->add_column( $table_templates, 'Organism_ID', 'number' );
			$db_engine->add_column( $table_templates, 'Conditions', 'long_text' );
			$db_engine->add_column( $table_templates, 'Is Active', 'boolean' );
			$changed = true;
		}

		// Luôn đảm bảo Dictionary có mặt
		if ( ! isset( $dictionary[ $table_templates ] ) ) {
			$dictionary[ $table_templates ] = array();
		}
		if ( ! isset( $dictionary[ $table_templates ]['__table_info'] ) ) {
			$dictionary[ $table_templates ]['__table_info'] = array(
				'name' => 'Theme Templates',
				'icon' => 'dashicons-admin-appearance',
				'app_id' => self::SYSTEM_APP
			);
			$changed = true;
		}
		// Phục hồi Metadata các cột nếu bị mất
		$tpl_cols = array(
			'name' => array('label' => 'Name', 'type' => 'short_text', 'options' => ''),
			'location' => array('label' => 'Location', 'type' => 'select', 'options' => 'header, footer, single, archive, 404, app_layout, custom'),
			'organism_id' => array('label' => 'Organism_ID', 'type' => 'number', 'options' => ''),
			'conditions' => array('label' => 'Conditions', 'type' => 'long_text', 'options' => ''),
			'is_active' => array('label' => 'Is Active', 'type' => 'boolean', 'options' => '')
		);
		foreach ( $tpl_cols as $col_slug => $col_data ) {
			if ( ! isset( $dictionary[ $table_templates ][ $col_slug ] ) ) {
				$dictionary[ $table_templates ][ $col_slug ] = $col_data;
				$changed = true;
			}
		}

		// Cập nhật ngay vào Database nếu có thay đổi
		if ( $changed ) {
			update_option( 'ska_data_dictionary', $dictionary );
			$changed = false;
		}

		// 3. Design Tokens (Presets)
		$table_presets_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_presets ) );
		if ( $table_presets_exists !== $table_presets ) {
			// Nếu chưa có bảng trong MySQL thì tạo mới hoàn toàn
			$db_engine->create_custom_table( 'presets', 'dashicons-art', self::SYSTEM_APP );
			$db_engine->add_column( $table_presets, 'Name', 'short_text' );
			$db_engine->add_column( $table_presets, 'Type', 'enum', 'token_color, token_font, token_spacing, token_radius, token_shadow, preset_typography, preset_component' );
			$db_engine->add_column( $table_presets, 'Value', 'long_text' );
			$changed = true;
		}

		// Luôn đảm bảo Dictionary có mặt
		if ( ! isset( $dictionary[ $table_presets ] ) ) {
			$dictionary[ $table_presets ] = array();
		}
		if ( ! isset( $dictionary[ $table_presets ]['__table_info'] ) ) {
			$dictionary[ $table_presets ]['__table_info'] = array(
				'name' => 'Design Tokens',
				'icon' => 'dashicons-art',
				'app_id' => self::SYSTEM_APP
			);
			$changed = true;
		}
		// Phục hồi Metadata các cột nếu bị mất
		$preset_cols = array(
			'name' => array('label' => 'Name', 'type' => 'short_text', 'options' => ''),
			'type' => array('label' => 'Type', 'type' => 'enum', 'options' => 'token_color, token_font, token_spacing, token_radius, token_shadow, preset_typography, preset_component'),
			'value' => array('label' => 'Value', 'type' => 'long_text', 'options' => '')
		);
		foreach ( $preset_cols as $col_slug => $col_data ) {
			if ( ! isset( $dictionary[ $table_presets ][ $col_slug ] ) ) {
				$dictionary[ $table_presets ][ $col_slug ] = $col_data;
				$changed = true;
			}
		}

		// Cập nhật ngay vào Database nếu có thay đổi
		if ( $changed ) {
			update_option( 'ska_data_dictionary', $dictionary );
			$changed = false;
		}

		// ... (các bước sau sẽ dùng dictionary hiện tại)
		if ( $changed || true ) { // trigger the next block anyway to ensure names
			$dictionary = get_option( 'ska_data_dictionary', array() );
			if ( isset( $dictionary[ $table_organisms ] ) ) {
				$dictionary[ $table_organisms ]['__table_info']['name'] = 'Organisms Blocks';
			}
			if ( isset( $dictionary[ $table_templates ] ) ) {
				$dictionary[ $table_templates ]['__table_info']['name'] = 'Theme Templates';
			}
			if ( isset( $dictionary[ $table_presets ] ) ) {
				$dictionary[ $table_presets ]['__table_info']['name'] = 'Design Tokens (Presets)';
			}
			update_option( 'ska_data_dictionary', $dictionary );
		}

		// Đảm bảo các bảng hệ thống luôn nằm đúng chỗ (Site Management)
		$sys_table_updates = false;
		$system_tables = array(
			$table_organisms => array( 'name' => 'Sys Organisms', 'icon' => 'dashicons-layout' ),
			$table_templates => array( 'name' => 'Theme Templates', 'icon' => 'dashicons-admin-appearance' ),
			$table_presets   => array( 'name' => 'Design Tokens', 'icon' => 'dashicons-admin-customizer' )
		);

		foreach ( $system_tables as $tb_name => $tb_config ) {
			if ( isset( $dictionary[ $tb_name ]['__table_info'] ) ) {
				if ( $dictionary[ $tb_name ]['__table_info']['app_id'] !== self::SYSTEM_APP ) {
					$dictionary[ $tb_name ]['__table_info']['app_id'] = self::SYSTEM_APP;
					$dictionary[ $tb_name ]['__table_info']['name'] = $tb_config['name'];
					$dictionary[ $tb_name ]['__table_info']['icon'] = $tb_config['icon'];
					$sys_table_updates = true;
				}
			}
		}

		if ( $sys_table_updates ) {
			update_option( 'ska_data_dictionary', $dictionary );
		}

		update_option( 'ska_data_sys_tables_migrated', true );
	}

	/**
	 * Lấy toàn bộ danh sách Workspace
	 */
	public static function get_apps() {
		$apps = get_option( self::OPTION_NAME, array() );
		if ( empty( $apps ) ) {
			self::init();
			$apps = get_option( self::OPTION_NAME, array() );
		}
		return $apps;
	}

	/**
	 * Tạo ứng dụng mới
	 * 
	 * @param string $name
	 * @param string $icon
	 * @return string|\WP_Error App ID
	 */
	public static function create_app( $name, $icon = 'dashicons-portfolio' ) {
		$name = sanitize_text_field( $name );
		if ( empty( $name ) ) {
			return new \WP_Error( 'invalid_name', 'Tên ứng dụng không được để trống.' );
		}

		$apps   = self::get_apps();
		
		// Semantic Slug Generator (Human-readable logic)
		$base_slug = sanitize_title( $name );
		$base_slug = str_replace( '-', '_', $base_slug ); // Đưa về chuẩn MySQL
		$app_id    = 'app_' . $base_slug;

		// Bức tường thép: Chặn đụng độ App name
		if ( isset( $apps[ $app_id ] ) ) {
			return new \WP_Error( 'app_exists', 'Tên Ứng dụng này đã được sử dụng (Ký danh đụng độ). Vui lòng chọn một tên Tôn sùng sự Độc nhất!' );
		}
		
		$apps[ $app_id ] = array(
			'id'         => $app_id,
			'name'       => $name,
			'icon'       => sanitize_text_field( $icon ),
			'created_at' => current_time( 'mysql' ),
		);

		update_option( self::OPTION_NAME, $apps );
		return $app_id;
	}

	/**
	 * Sửa tên App
	 */
	public static function update_app( $app_id, $name, $icon ) {
		$app_id = sanitize_text_field( $app_id );
		$name   = sanitize_text_field( $name );
		
		if ( $app_id === self::UNCATEGORIZED_APP || $app_id === self::SYSTEM_APP ) {
			return new \WP_Error( 'protected', 'Không thể sửa đổi Workspace Hệ thống.' );
		}

		$apps = self::get_apps();
		if ( ! isset( $apps[ $app_id ] ) ) {
			return new \WP_Error( 'not_found', 'Ứng dụng không tồn tại.' );
		}

		$apps[ $app_id ]['name'] = $name;
		$apps[ $app_id ]['icon'] = sanitize_text_field( $icon );
		update_option( self::OPTION_NAME, $apps );
		return true;
	}

	/**
	 * Xóa App: Đưa mọi table thuộc App này về "uncategorized"
	 */
	public static function drop_app( $app_id ) {
		$app_id = sanitize_text_field( $app_id );
		if ( $app_id === self::UNCATEGORIZED_APP || $app_id === self::SYSTEM_APP ) {
			return new \WP_Error( 'protected', 'Không thể xóa Workspace Hệ thống.' );
		}

		$apps = self::get_apps();
		if ( ! isset( $apps[ $app_id ] ) ) {
			return new \WP_Error( 'not_found', 'Ứng dụng không tồn tại.' );
		}

		// 1. Chuyển nhà (Tables)
		$dictionary = get_option( 'ska_data_dictionary', array() );
		$changed    = false;
		foreach ( $dictionary as $table_name => $schema ) {
			if ( isset( $schema['__table_info']['app_id'] ) && $schema['__table_info']['app_id'] === $app_id ) {
				$dictionary[ $table_name ]['__table_info']['app_id'] = self::UNCATEGORIZED_APP;
				$changed = true;
			}
		}

		if ( $changed ) {
			update_option( 'ska_data_dictionary', $dictionary );
		}

		// 2. Trảm App
		unset( $apps[ $app_id ] );
		update_option( self::OPTION_NAME, $apps );

		return true;
	}
}
