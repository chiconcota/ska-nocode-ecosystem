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
		global $wpdb;
		$table_apps = $wpdb->prefix . 'ska_data_sys_apps';

		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_apps ) );
		if ( $table_exists !== $table_apps ) {
			return;
		}

		// Đọc từ option cũ để di trú
		$old_apps = get_option( 'ska_data_apps', array() );
		if ( ! is_array( $old_apps ) ) {
			$old_apps = array();
		}

		// Đảm bảo có Default Workspace và Site Management
		if ( ! isset( $old_apps[ self::UNCATEGORIZED_APP ] ) ) {
			$old_apps[ self::UNCATEGORIZED_APP ] = array(
				'id'         => self::UNCATEGORIZED_APP,
				'name'       => 'Default Workspace',
				'icon'       => 'dashicons-portfolio',
				'created_at' => current_time( 'mysql' ),
			);
		}
		if ( ! isset( $old_apps[ self::SYSTEM_APP ] ) ) {
			$old_apps[ self::SYSTEM_APP ] = array(
				'id'         => self::SYSTEM_APP,
				'name'       => 'Site Management',
				'icon'       => 'dashicons-admin-generic',
				'created_at' => current_time( 'mysql' ),
			);
		}

		foreach ( $old_apps as $app_id => $app_data ) {
			$exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_apps} WHERE app_id = %s", $app_id ) );
			if ( ! $exists ) {
				$wpdb->insert( $table_apps, array(
					'app_id'                    => $app_id,
					'name'                      => isset( $app_data['name'] ) ? $app_data['name'] : $app_id,
					'icon'                      => isset( $app_data['icon'] ) ? $app_data['icon'] : 'dashicons-portfolio',
					'unauthorized_redirect_url' => isset( $app_data['unauthorized_redirect_url'] ) ? $app_data['unauthorized_redirect_url'] : '',
					'created_at'                => isset( $app_data['created_at'] ) ? $app_data['created_at'] : current_time( 'mysql' ),
				) );
			}
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
		$table_apps      = $wpdb->prefix . 'ska_data_sys_apps';

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
			$db_engine->add_column( $table_organisms, 'Category', 'short_text' );
			$db_engine->add_column( $table_organisms, 'JSON_Content', 'long_text' );
			$db_engine->add_column( $table_organisms, 'HTML_Content', 'long_text' );
			$changed = true;
		} else {
			// Migration: Nếu bảng đã có, kiểm tra xem cột category đã có chưa
			$column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM {$table_organisms} LIKE %s", 'category' ) );
			if ( empty( $column_exists ) ) {
				$wpdb->query( "ALTER TABLE {$table_organisms} ADD COLUMN category VARCHAR(255) DEFAULT NULL" );
			}
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
			'category' => array('label' => 'Category', 'type' => 'short_text', 'options' => ''),
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
			$db_engine->add_column( $table_templates, 'Location', 'select', 'header, footer, single, archive, 404, 403, app_layout, custom' );
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
			'location' => array('label' => 'Location', 'type' => 'select', 'options' => 'header, footer, single, archive, 404, 403, app_layout, custom'),
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

		// Cập nhật lại options của cột location để luôn có '403' cho cả các cài đặt hiện tại
		if ( isset( $dictionary[ $table_templates ]['location'] ) ) {
			if ( strpos( $dictionary[ $table_templates ]['location']['options'], '403' ) === false ) {
				$dictionary[ $table_templates ]['location']['options'] = 'header, footer, single, archive, 404, 403, app_layout, custom';
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

		$dictionary = get_option( 'ska_data_dictionary', array() );

		// 4. Workspaces (Apps)
		$table_apps_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_apps ) );
		if ( $table_apps_exists !== $table_apps ) {
			// Tạo mới bảng qua db_engine
			$db_engine->create_custom_table( 'apps', 'dashicons-portfolio', self::SYSTEM_APP );
			$db_engine->add_column( $table_apps, 'App ID', 'short_text' );
			$db_engine->add_column( $table_apps, 'Name', 'short_text' );
			$db_engine->add_column( $table_apps, 'Icon', 'short_text' );
			$db_engine->add_column( $table_apps, 'Unauthorized Redirect URL', 'short_text' );
			$changed = true;
		} else {
			// Đảm bảo các cột luôn tồn tại (phòng trường hợp bảng bị lỗi khởi tạo dở dang)
			$required_columns = array(
				'app_id'                    => 'VARCHAR(255) DEFAULT NULL',
				'name'                      => 'VARCHAR(255) DEFAULT NULL',
				'icon'                      => 'VARCHAR(255) DEFAULT NULL',
				'unauthorized_redirect_url' => 'VARCHAR(255) DEFAULT NULL'
			);
			foreach ( $required_columns as $col => $definition ) {
				$column_exists = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM {$table_apps} LIKE %s", $col ) );
				if ( empty( $column_exists ) ) {
					$wpdb->query( "ALTER TABLE {$table_apps} ADD COLUMN {$col} {$definition}" );
				}
			}
		}

		// Luôn đảm bảo Dictionary có mặt
		if ( ! isset( $dictionary[ $table_apps ] ) ) {
			$dictionary[ $table_apps ] = array();
		}
		if ( ! isset( $dictionary[ $table_apps ]['__table_info'] ) ) {
			$dictionary[ $table_apps ]['__table_info'] = array(
				'name' => 'Workspaces',
				'icon' => 'dashicons-portfolio',
				'app_id' => self::SYSTEM_APP
			);
			$changed = true;
		}
		// Phục hồi Metadata các cột nếu bị mất
		$app_cols = array(
			'app_id' => array('label' => 'App ID', 'type' => 'short_text', 'options' => ''),
			'name' => array('label' => 'Name', 'type' => 'short_text', 'options' => ''),
			'icon' => array('label' => 'Icon', 'type' => 'short_text', 'options' => ''),
			'unauthorized_redirect_url' => array('label' => 'Unauthorized Redirect URL', 'type' => 'short_text', 'options' => '')
		);
		foreach ( $app_cols as $col_slug => $col_data ) {
			if ( ! isset( $dictionary[ $table_apps ][ $col_slug ] ) ) {
				$dictionary[ $table_apps ][ $col_slug ] = $col_data;
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
			if ( isset( $dictionary[ $table_apps ] ) ) {
				$dictionary[ $table_apps ]['__table_info']['name'] = 'Workspaces';
			}
			update_option( 'ska_data_dictionary', $dictionary );
		}

		// Đảm bảo các bảng hệ thống luôn nằm đúng chỗ (Site Management)
		$sys_table_updates = false;
		$system_tables = array(
			$table_organisms => array( 'name' => 'Sys Organisms', 'icon' => 'dashicons-layout' ),
			$table_templates => array( 'name' => 'Theme Templates', 'icon' => 'dashicons-admin-appearance' ),
			$table_presets   => array( 'name' => 'Design Tokens', 'icon' => 'dashicons-admin-customizer' ),
			$table_apps      => array( 'name' => 'Workspaces', 'icon' => 'dashicons-portfolio' )
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
		global $wpdb;
		$table_apps = $wpdb->prefix . 'ska_data_sys_apps';

		// Đảm bảo bảng tồn tại trước khi select
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_apps ) );
		if ( $table_exists !== $table_apps ) {
			self::maybe_create_system_tables();
		}

		$results = $wpdb->get_results( "SELECT * FROM {$table_apps}", ARRAY_A );
		$apps = array();
		if ( ! empty( $results ) && is_array( $results ) ) {
			foreach ( $results as $row ) {
				$apps[ $row['app_id'] ] = array(
					'id'                        => $row['app_id'],
					'name'                      => $row['name'],
					'icon'                      => $row['icon'],
					'unauthorized_redirect_url' => isset( $row['unauthorized_redirect_url'] ) ? $row['unauthorized_redirect_url'] : '',
					'created_at'                => $row['created_at'],
				);
			}
		}

		// Nếu danh sách trống, khởi tạo lại
		if ( empty( $apps ) ) {
			self::init();
			$results = $wpdb->get_results( "SELECT * FROM {$table_apps}", ARRAY_A );
			if ( ! empty( $results ) && is_array( $results ) ) {
				foreach ( $results as $row ) {
					$apps[ $row['app_id'] ] = array(
						'id'                        => $row['app_id'],
						'name'                      => $row['name'],
						'icon'                      => $row['icon'],
						'unauthorized_redirect_url' => isset( $row['unauthorized_redirect_url'] ) ? $row['unauthorized_redirect_url'] : '',
						'created_at'                => $row['created_at'],
					);
				}
			}
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
			return new \WP_Error( 'invalid_name', __( 'Application name cannot be empty.', 'ska-data-pro' ) );
		}

		global $wpdb;
		$table_apps = $wpdb->prefix . 'ska_data_sys_apps';
		
		// Semantic Slug Generator (Human-readable logic)
		$base_slug = sanitize_title( $name );
		$base_slug = str_replace( '-', '_', $base_slug ); // Đưa về chuẩn MySQL
		$app_id    = 'app_' . $base_slug;

		// Bức tường thép: Chặn đụng độ App name
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_apps} WHERE app_id = %s", $app_id ) );
		if ( $exists > 0 ) {
			return new \WP_Error( 'app_exists', __( 'This Application name is already in use (Clash Alias). ', 'ska-data-pro' ) );
		}
		
		$inserted = $wpdb->insert( $table_apps, array(
			'app_id'                    => $app_id,
			'name'                      => $name,
			'icon'                      => sanitize_text_field( $icon ),
			'unauthorized_redirect_url' => '',
			'created_at'                => current_time( 'mysql' ),
		) );

		if ( false === $inserted ) {
			return new \WP_Error( 'db_error', __( 'Could not create Workspace in database.', 'ska-data-pro' ) );
		}

		return $app_id;
	}

	/**
	 * Sửa tên App và cấu hình chuyển hướng
	 */
	public static function update_app( $app_id, $name, $icon, $unauthorized_redirect_url = '' ) {
		$app_id                    = sanitize_text_field( $app_id );
		$name                      = sanitize_text_field( $name );
		$icon                      = sanitize_text_field( $icon );
		$unauthorized_redirect_url = sanitize_text_field( $unauthorized_redirect_url );
		
		global $wpdb;
		$table_apps = $wpdb->prefix . 'ska_data_sys_apps';

		// Đảm bảo workspace tồn tại
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_apps} WHERE app_id = %s", $app_id ) );
		if ( ! $exists ) {
			return new \WP_Error( 'not_found', __( 'Application does not exist.', 'ska-data-pro' ) );
		}

		// Nếu là system workspace thì giữ nguyên tên mặc định để tránh phá vỡ giao diện
		if ( $app_id === self::SYSTEM_APP ) {
			$name = 'Site Management';
		} elseif ( $app_id === self::UNCATEGORIZED_APP ) {
			$name = 'Default Workspace';
		}

		$updated = $wpdb->update(
			$table_apps,
			array(
				'name'                      => $name,
				'icon'                      => $icon,
				'unauthorized_redirect_url' => $unauthorized_redirect_url,
			),
			array( 'app_id' => $app_id )
		);

		if ( false === $updated ) {
			return new \WP_Error( 'db_error', __( 'Could not update Workspace in database.', 'ska-data-pro' ) );
		}

		return true;
	}

	/**
	 * Xóa App: Đưa mọi table thuộc App này về "uncategorized"
	 */
	public static function drop_app( $app_id ) {
		$app_id = sanitize_text_field( $app_id );
		if ( $app_id === self::UNCATEGORIZED_APP || $app_id === self::SYSTEM_APP ) {
			return new \WP_Error( 'protected', __( 'Cannot delete System Workspace.', 'ska-data-pro' ) );
		}

		global $wpdb;
		$table_apps = $wpdb->prefix . 'ska_data_sys_apps';

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_apps} WHERE app_id = %s", $app_id ) );
		if ( ! $exists ) {
			return new \WP_Error( 'not_found', __( 'Application does not exist.', 'ska-data-pro' ) );
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
		$deleted = $wpdb->delete( $table_apps, array( 'app_id' => $app_id ) );
		if ( false === $deleted ) {
			return new \WP_Error( 'db_error', __( 'Could not delete Workspace from database.', 'ska-data-pro' ) );
		}

		return true;
	}
}
