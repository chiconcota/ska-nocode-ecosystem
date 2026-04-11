<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Database_Engine
 * Xử lý mọi tương tác lõi với Database: Cài đặt Schema, Thực thi dbDelta, Nạp dữ liệu giả.
 */
class Database_Engine {

	/**
	 * Instance
	 *
	 * @var Database_Engine
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cài đặt một Template Schema hoàn chỉnh.
	 *
	 * @param string $template_id
	 * @return array|\WP_Error
	 */
	public function install_schema( $template_id ) {
		$template = Template_Registry::get_template( $template_id );

		if ( ! $template ) {
			return new \WP_Error( 'invalid_template', 'Template dữ liệu không tồn tại.' );
		}

		// 1. Chạy dbDelta để đúc Bảng (Flat Tables)
		$create_result = $this->run_dbdelta( $template['tables'] );
		
		if ( is_wp_error( $create_result ) ) {
			return $create_result;
		}

		// 2. Bơm Dữ liệu giả (Dummy Data) nếu có
		if ( ! empty( $template['dummy_data'] ) ) {
			$this->insert_dummy_data( $template['dummy_data'] );
		}

		return array(
			'success'    => true,
			'main_table' => $template['main_table']
		);
	}

	/**
	 * Thực thi hàm dbDelta của WordPress.
	 *
	 * @param array $tables Mảng chứa Key (tên bảng) và Value (chuỗi SQL Schema).
	 * @return true|\WP_Error
	 */
	private function run_dbdelta( $tables ) {
		global $wpdb;

		// Bắt buộc require file upgrade.php của WP để dùng dbDelta
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		$charset_collate = $wpdb->get_charset_collate();
		$errors = array();

		foreach ( $tables as $table_name => $sql_schema ) {
			// Lưu ý: Chúng ta dùng trực tiếp tên bảng dạng "ska_data_xyz", KHÔNG cần nối $wpdb->prefix
			// Lý do: Các bảng này là bảng nghiệp vụ độc lập của hệ sinh thái Ska.
			// Tuy nhiên, để tránh xung đột trên các hệ thống Multisite, ta CÓ dùng prefix của bảng.
			$table_name_with_prefix = $wpdb->prefix . $table_name;
			
			$sql = "CREATE TABLE {$table_name_with_prefix} (
{$sql_schema}
			) {$charset_collate};";

			// dbDelta sẽ tự động nhận diện Tạo mới hoặc Cập nhật bảng
			$result = dbDelta( $sql );
			
			if ( empty( $result ) && $wpdb->last_error ) {
				$errors[] = $wpdb->last_error;
			}
		}

		if ( ! empty( $errors ) ) {
			return new \WP_Error( 'dbdelta_failed', 'Lỗi tạo bảng: ' . implode( ', ', $errors ) );
		}

		return true;
	}

	/**
	 * Nạp dữ liệu giả vào các bảng vừa tạo.
	 *
	 * @param array $data_groups Mảng dữ liệu tương ứng với từng bảng.
	 */
	private function insert_dummy_data( $data_groups ) {
		global $wpdb;

		foreach ( $data_groups as $table_name => $rows ) {
			$table_name_with_prefix = $wpdb->prefix . $table_name;

			// Kiểm tra xem bảng đã có dữ liệu chưa. Nếu có rồi thì bỏ qua (tránh duplicate khi user bấm cài lại nhiều lần).
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name_with_prefix}" );
			if ( $count > 0 ) {
				continue;
			}

			// Thực hiện Insert trực tiếp
			foreach ( $rows as $row_data ) {
				$wpdb->insert( $table_name_with_prefix, $row_data );
			}
		}
	}

	/**
	 * Thêm một bản ghi trống (Empty Row) vào bảng.
	 *
	 * @param string $table_name Tên bảng đã có prefix.
	 * @return int|\WP_Error ID của bản ghi vừa tạo hoặc WP_Error.
	 */
	public function add_empty_row( $table_name ) {
		global $wpdb;

		// Kiểm tra an toàn bảo mật tên bảng
		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		// Dùng SQL raw vì $wpdb->insert không hiểu array rỗng (Báo lỗi Unknown column '')
		$result = $wpdb->query( "INSERT INTO `{$table_name}` () VALUES ()" );

		if ( false === $result ) {
			return new \WP_Error( 'insert_failed', 'Lỗi Database khi mồi dòng mới: ' . $wpdb->last_error );
		}

		// Trả về ID tự tăng vừa được đúc xong
		return $wpdb->insert_id;
	}

	/**
	 * Cập nhật một ô (Cell) bất kỳ trong hệ thống.
	 *
	 * @param string $table_name
	 * @param int $row_id
	 * @param string $column_name
	 * @param mixed $value
	 * @return true|\WP_Error
	 */
	public function update_cell( $table_name, $row_id, $column_name, $value ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		$row_id = absint( $row_id );

		// Kiểm tra $column_name có hợp lệ không (Lá chắn SQL Injection qua Column Name)
		$columns = \Ska\Data\Core\Data_Fetcher::get_table_columns( $table_name );
		
		// Map danh sách các tên cột
		$valid_columns = array();
		foreach ( $columns as $col ) {
			$valid_columns[] = $col->Field;
		}
		
		if ( ! in_array( $column_name, $valid_columns ) ) {
			return new \WP_Error( 'invalid_column', 'Bảo mật: Cột không tồn tại.' );
		}

		// Sử dụng cơ chế Update chuẩn của WP (Đã bao hàm cơ chế Escape/Sanitize SQL an toàn cho `$value`)
		$result = $wpdb->update(
			$table_name,
			array( $column_name => $value ),
			array( 'id' => $row_id )
		);

		if ( false === $result ) {
			return new \WP_Error( 'update_failed', 'Lỗi lưu dữ liệu: ' . $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Tạo Cột Mới (Add Schema Column) và lưu Từ Điển (Dictionary) Ánh Xạ sang Nhãn Tiếng Việt.
	 *
	 * @param string $table_name
	 * @param string $label Tên Tiếng Việt Hiển Thị (Ví dụ: Giá Bán)
	 * @param string $options Tùy chọn mở rộng (Ví dụ List cho Dropdown Select)
	 * @return true|\WP_Error
	 */
	public function add_column( $table_name, $label, $field_type, $options = '' ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		$label = sanitize_text_field( $label );
		if ( empty( $label ) ) {
			return new \WP_Error( 'empty_label', 'Tên cột không được để trống.' );
		}

		// Phiên dịch Tiếng Việt -> Cấu trúc không dấu cách (Ví dụ: "Giá Bán" -> "gia-ban" -> "gia_ban")
		$col_slug = str_replace( '-', '_', sanitize_title( $label ) );
		
		// Phòng hờ Slug không được rỗng sau khi sanitize (Vd user gõ toàn ký tự đặc biệt)
		if ( empty( $col_slug ) ) {
			$col_slug = 'column_' . wp_generate_password( 6, false, false ); // Fallback hên xui
		}

		// Đảm bảo Cột này chưa bị trùng trong hệ thống
		$existing_cols  = \Ska\Data\Core\Data_Fetcher::get_table_columns( $table_name );
		$existing_slugs = array();
		foreach( $existing_cols as $c ) { $existing_slugs[] = $c->Field; }
		
		if ( in_array( $col_slug, $existing_slugs ) ) {
			return new \WP_Error( 'column_exists', "Hệ thống báo: Đã có một cột bị trùng tên [{$col_slug}] trong bảng." );
		}

		// Map Mệnh Lệnh của User sang Ngôn Ngữ MySQL Vật lý
		$mysql_type = 'VARCHAR(255)'; // Fallback An toàn
		$default_sql = 'DEFAULT NULL';
		switch ( $field_type ) {
			case 'short_text':
				$mysql_type = 'VARCHAR(255)';
				break;
			case 'long_text':
				$mysql_type = 'LONGTEXT'; // Phục vụ Editor WYSIWYG và Đoạn văn dài siêu hạng
				break;
			case 'media':
				$mysql_type = 'VARCHAR(500)'; // Chứa File URL
				break;
			case 'media_gallery':
				$mysql_type = 'TEXT'; // Chứa mảng các File URL cách nhau bằng dấu phẩy
				break;
			case 'boolean':
				$mysql_type = 'TINYINT(1)';
				$default_sql = "DEFAULT '0'";
				break;
			case 'select':
				$mysql_type = 'VARCHAR(255)';
				break;
			case 'multi_select':
			case 'relation':
			case 'rollup':
				$mysql_type = 'TEXT';
				break;
			case 'number':
				$mysql_type = 'INT(11)';
				break;
			case 'currency':
				$mysql_type = 'DECIMAL(15,2)';
				break;
			case 'date':
				$mysql_type = 'DATE';
				break;
			case 'url':
				$mysql_type = 'VARCHAR(500)';
				break;
		}

		// Chọc thẳng vào Cấu Trúc Đĩa Cứng: THÊM CỘT
		$sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$col_slug}` {$mysql_type} {$default_sql}";
		$result = $wpdb->query( $sql );

		if ( false === $result ) {
			return new \WP_Error( 'alter_failed', 'Lỗi chèn cột ổ cứng: ' . $wpdb->last_error );
		}

		// LƯU CẤU TRÚC ÁNH XẠ (Dictionary) VỚI CÁI TÊN ĐẸP ĐẼ CỦA USER VÀO JSON OPTIONS
		$dictionary = get_option( 'ska_data_dictionary', array() );
		
		if ( ! isset( $dictionary[ $table_name ] ) || ! is_array( $dictionary[ $table_name ] ) ) {
			$dictionary[ $table_name ] = array();
		}

		$dictionary[ $table_name ][ $col_slug ] = array(
			'label'   => $label,
			'type'    => $field_type,
			'options' => sanitize_text_field( $options )
		);
		update_option( 'ska_data_dictionary', $dictionary );

		return true;
	}

	/**
	 * Cập nhật định dạng/nhãn của Cột (Schema Modify).
	 *
	 * @param string $table_name
	 * @param string $col_slug
	 * @param string $new_label
	 * @param string $options Tùy chọn mở rộng
	 * @return true|\WP_Error
	 */
	public function update_column( $table_name, $col_slug, $new_label, $new_type, $options = '' ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		if ( $col_slug === 'id' ) {
			return new \WP_Error( 'protected_col', 'Không thể sửa đổi Cột ID (Khóa chính hệ thống).' );
		}

		$new_label = sanitize_text_field( $new_label );
		if ( empty( $new_label ) ) {
			return new \WP_Error( 'empty_label', 'Tên hiển thị không được để trống.' );
		}

		// Ánh xạ Type
		$mysql_type = 'VARCHAR(255)';
		$default_sql = 'DEFAULT NULL';
		switch ( $new_type ) {
			case 'short_text':
				$mysql_type = 'VARCHAR(255)';
				break;
			case 'long_text':
				$mysql_type = 'LONGTEXT'; // WYSIWYG
				break;
			case 'media':
				$mysql_type = 'VARCHAR(500)';
				break;
			case 'media_gallery':
				$mysql_type = 'TEXT';
				break;
			case 'boolean':
				$mysql_type = 'TINYINT(1)';
				$default_sql = "DEFAULT '0'";
				break;
			case 'select':
				$mysql_type = 'VARCHAR(255)';
				break;
			case 'multi_select':
			case 'relation':
			case 'rollup':
				$mysql_type = 'TEXT';
				break;
			case 'number':
				$mysql_type = 'INT(11)';
				break;
			case 'currency':
				$mysql_type = 'DECIMAL(15,2)';
				break;
			case 'date':
				$mysql_type = 'DATE';
				break;
			case 'url':
				$mysql_type = 'VARCHAR(500)';
				break;
		}

		// Đổi Cấu trúc Data Type bằng MODIFY COLUMN
		// NẾU TỪ CỘT CHỮ VỀ CỘT SỐ => MYSQL COI CHỪNG ÉP KIỂU
		$sql = "ALTER TABLE `{$table_name}` MODIFY COLUMN `{$col_slug}` {$mysql_type} {$default_sql}";
		$result = $wpdb->query( $sql );

		if ( false === $result ) {
			// MySQL Chặn khi Data format rỗng.
			return new \WP_Error( 'alter_failed', 'Không thể cập nhật kiểu dữ liệu cột này: ' . $wpdb->last_error );
		}

		// Cập nhật Dictionary (Đổi "Mặt nạ" Label đẹp)
		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( ! isset( $dictionary[ $table_name ] ) || ! is_array( $dictionary[ $table_name ] ) ) {
			$dictionary[ $table_name ] = array();
		}

		$dictionary[ $table_name ][ $col_slug ] = array(
			'label'   => $new_label,
			'type'    => $new_type,
			'options' => sanitize_text_field( $options )
		);
		update_option( 'ska_data_dictionary', $dictionary );

		return true;
	}

	/**
	 * Xóa vĩnh viễn Cột cứng (Drop Schema Field).
	 * Tàn nhẫn không phục hồi. 
	 *
	 * @param string $table_name
	 * @param string $col_slug
	 * @return true|\WP_Error
	 */
	public function drop_column( $table_name, $col_slug ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		if ( $col_slug === 'id' ) {
			return new \WP_Error( 'protected_col', 'Tuyệt đối không được xóa Cột ID khóa chính.' );
		}

		// Cưa đổ cột DB
		$sql = "ALTER TABLE `{$table_name}` DROP COLUMN `{$col_slug}`";
		$result = $wpdb->query( $sql );

		if ( false === $result ) {
			return new \WP_Error( 'alter_failed', 'Lại MySQL gặp lỗi cắt cột: ' . $wpdb->last_error );
		}

		// Dọn rác JSON Dictionary
		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( isset( $dictionary[ $table_name ][ $col_slug ] ) ) {
			unset( $dictionary[ $table_name ][ $col_slug ] );
			update_option( 'ska_data_dictionary', $dictionary );
		}

		return true;
	}

	/**
	 * Xóa cứng (Hard Delete) một dòng dữ liệu.
	 *
	 * @param string $table_name
	 * @param int $row_id
	 * @return true|\WP_Error
	 */
	public function delete_row( $table_name, $row_id ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => absint( $row_id ) ),
			array( '%d' ) // Format số nguyên cho ID
		);

		if ( false === $result ) {
			return new \WP_Error( 'delete_failed', 'Lỗi xóa dữ liệu: ' . $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Tạo bảng Custom Mới.
	 *
	 * @param string $friendly_name
	 * @param string $icon
	 * @param string $group
	 * @return string|\WP_Error Tên Table Slug hoàn chỉnh
	 */
	public function create_custom_table( $friendly_name, $icon = 'dashicons-admin-page', $app_id = 'uncategorized' ) {
		global $wpdb;

		// 1. Tạo Slug chuẩn
		$slug = sanitize_title( $friendly_name );
		if ( empty( $slug ) ) {
			return new \WP_Error( 'invalid_name', 'Tên bảng không hợp lệ.' );
		}
		
		// 1A. Nhúng App ID vào để ngăn chặn dẫm đạp Schema (Collision Resolving)
		$clean_app_id = sanitize_key( $app_id );
		if ( empty( $clean_app_id ) || $clean_app_id === 'uncategorized' ) {
			$base_slug = 'ska_data_' . str_replace( '-', '_', $slug );
		} else {
			$base_slug = 'ska_data_' . $clean_app_id . '_' . str_replace( '-', '_', $slug );
		}
		
		$table_name = $wpdb->prefix . $base_slug;

		// Kiểm tra Trùng
		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		if ( $exists === $table_name ) {
			return new \WP_Error( 'table_exists', 'Bảng này đã tồn tại.' );
		}

		// 2. Chạy Lệnh Tạo (Base Columns: id, created_at)
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE `{$table_name}` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`created_at` datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// 3. Cập nhật Dictionary (__table_info)
		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( ! is_array( $dictionary ) ) {
			$dictionary = array();
		}
		$dictionary[ $table_name ] = array(
			'__table_info' => array(
				'name'   => sanitize_text_field( $friendly_name ),
				'icon'   => sanitize_text_field( $icon ),
				'app_id' => sanitize_text_field( $app_id )
			)
		);
		update_option( 'ska_data_dictionary', $dictionary );

		return $table_name;
	}

	/**
	 * Xóa sạch sẽ một Bảng dữ liệu.
	 *
	 * @param string $table_name
	 * @return true|\WP_Error
	 */
	public function drop_table( $table_name ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		// Xóa DB
		$sql = "DROP TABLE IF EXISTS `{$table_name}`";
		$wpdb->query( $sql );

		// Xóa Option
		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( isset( $dictionary[ $table_name ] ) ) {
			unset( $dictionary[ $table_name ] );
			update_option( 'ska_data_dictionary', $dictionary );
		}

		return true;
	}

	public function rename_custom_table( $table_name, $new_name, $new_icon, $app_id = 'uncategorized' ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( ! isset( $dictionary[ $table_name ] ) || ! is_array($dictionary[ $table_name ]) ) {
			$dictionary[ $table_name ] = array();
		}

		$dictionary[ $table_name ]['__table_info'] = array(
			'name'   => sanitize_text_field( $new_name ),
			'icon'   => sanitize_text_field( $new_icon ),
			'app_id' => sanitize_text_field( $app_id )
		);

		update_option( 'ska_data_dictionary', $dictionary );
		return true;
	}

	/**
	 * Tương tác chuyên sâu với danh sách Options của Cột Select.
	 * Có khả năng Mass Update (Cập nhật hàng loạt dữ liệu cũ).
	 *
	 * @param string $table_name
	 * @param string $col_slug
	 * @param string $action ('add' hoặc 'edit')
	 * @param string $old_val
	 * @param string $new_val
	 * @return array|\WP_Error
	 */
	public function manage_select_options( $table_name, $col_slug, $action, $old_val, $new_val ) {
		global $wpdb;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return new \WP_Error( 'invalid_table', 'Bảo mật: Tên bảng không hợp lệ.' );
		}

		$dictionary = get_option( 'ska_data_dictionary', array() );
		if ( ! isset( $dictionary[ $table_name ][ $col_slug ] ) ) {
			return new \WP_Error( 'invalid_column', 'Cột này chưa nằm trong từ điển.' );
		}

		$meta = $dictionary[ $table_name ][ $col_slug ];
		if ( $meta['type'] !== 'select' && $meta['type'] !== 'multi_select' ) {
			return new \WP_Error( 'not_select', 'Chỉ được phép gọi trên cột dạng (Multi) Select.' );
		}

		$opts_str = isset( $meta['options'] ) ? $meta['options'] : '';
		$opts_arr = array_map( 'trim', explode( ',', $opts_str ) );
		$new_val  = sanitize_text_field( $new_val );

		if ( $action === 'add' ) {
			if ( ! in_array( $new_val, $opts_arr ) ) {
				$opts_arr[] = $new_val;
			}
		} elseif ( $action === 'edit' ) {
			$old_val = sanitize_text_field( $old_val );
			$index = array_search( $old_val, $opts_arr );
			if ( $index !== false ) {
				$opts_arr[$index] = $new_val;
			} else {
				// Đôi khi user sửa option mà chưa kịp lưu, cứ thêm mới.
				$opts_arr[] = $new_val; 
			}

			// MASS UPDATE BẢNG CỨNG DỰA THEO KIỂU MẢNH
			if ( $meta['type'] === 'select' ) {
				$sql = $wpdb->prepare(
					"UPDATE `{$table_name}` SET `{$col_slug}` = %s WHERE `{$col_slug}` = %s",
					$new_val,
					$old_val
				);
			} else {
				// multi_select: Thay thế an toàn chuỗi CSV (Tránh bị dính chữ liền kề, vd 'Light Red' -> sửa 'Red' ko bị ảnh hưởng)
				$safe_old = esc_sql( $old_val );
				$safe_new = esc_sql( $new_val );
				$sql = "UPDATE `{$table_name}` 
						SET `{$col_slug}` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `{$col_slug}`, ','), ',{$safe_old},', ',{$safe_new},')) 
						WHERE FIND_IN_SET('{$safe_old}', `{$col_slug}`) > 0";
			}
			$wpdb->query( $sql );
		}

		// Xóa phần tử rỗng và Cập nhật lại chuỗi
		$opts_arr = array_filter( $opts_arr, function($v) { return $v !== ''; } );
		$dictionary[ $table_name ][ $col_slug ]['options'] = implode( ', ', $opts_arr );
		update_option( 'ska_data_dictionary', $dictionary );

		return array(
			'success' => true,
			'options' => $dictionary[ $table_name ][ $col_slug ]['options']
		);
	}
}
