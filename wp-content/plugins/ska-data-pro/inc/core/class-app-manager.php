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

	/**
	 * Khởi tạo Mặc định (Uncategorized)
	 */
	public static function init() {
		$apps = get_option( self::OPTION_NAME, false );
		if ( false === $apps || ! is_array( $apps ) ) {
			$default_apps = array(
				self::UNCATEGORIZED_APP => array(
					'id'         => self::UNCATEGORIZED_APP,
					'name'       => 'Default Workspace',
					'icon'       => 'dashicons-portfolio',
					'created_at' => current_time( 'mysql' ),
				)
			);
			update_option( self::OPTION_NAME, $default_apps );
		}
	}

	/**
	 * Tự động đồng bộ các bảng cũ (Migration)
	 */
	public static function maybe_run_migration() {
		$migrated = get_option( 'ska_data_app_migrated', false );
		if ( $migrated ) {
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
		$app_id = 'app_' . wp_generate_password( 8, false, false ); // UUID random
		
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
		
		if ( $app_id === self::UNCATEGORIZED_APP ) {
			return new \WP_Error( 'protected', 'Không thể sửa đổi Workspace Mặc định.' );
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
		if ( $app_id === self::UNCATEGORIZED_APP ) {
			return new \WP_Error( 'protected', 'Không thể xóa Workspace Mặc định.' );
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
