<?php
namespace Ska\Data\Admin;

use Ska\Data\Core\Database_Engine;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin_Ajax
 * Nơi xử lý tập trung mọi Request từ Dashboard gửi xuống (Bất Đồng Bộ).
 */
class Admin_Ajax {

	/**
	 * Instance
	 *
	 * @var Admin_Ajax
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Hook xử lý Cài đặt Mẫu dữ liệu (Template Gallery)
		add_action( 'wp_ajax_ska_install_data_template', array( $this, 'install_data_template' ) );

		// Hook xử lý DataGrid CRUD
		add_action( 'wp_ajax_ska_data_add_row', array( $this, 'data_add_row' ) );
		add_action( 'wp_ajax_ska_data_update_cell', array( $this, 'data_update_cell' ) );
		add_action( 'wp_ajax_ska_data_delete_row', array( $this, 'data_delete_row' ) );

		// Hook xử lý Schema Builder
		add_action( 'wp_ajax_ska_data_add_column', array( $this, 'data_add_column' ) );
		add_action( 'wp_ajax_ska_data_update_column', array( $this, 'data_update_column' ) );
		add_action( 'wp_ajax_ska_data_drop_column', array( $this, 'data_drop_column' ) );
		add_action( 'wp_ajax_ska_data_manage_select_option', array( $this, 'data_manage_select_option' ) );

		// Hook xử lý Table Builder
		add_action( 'wp_ajax_ska_data_create_table', array( $this, 'data_create_table' ) );
		add_action( 'wp_ajax_ska_data_rename_table', array( $this, 'data_rename_table' ) );
		add_action( 'wp_ajax_ska_data_drop_table', array( $this, 'data_drop_table' ) );

		// Hook xử lý UI & Data Relation
		add_action( 'wp_ajax_ska_data_search_relation', array( $this, 'data_search_relation' ) );
		add_action( 'wp_ajax_ska_data_get_table_columns', array( $this, 'data_get_table_columns' ) );
	}

	/**
	 * AJAX: Thêm Cột Mới (Schema Builder)
	 */
	public function data_add_column() {
		$this->verify_crud_request();
		
		$table   = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$label   = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'short_text';
		$options = isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '';

		if ( empty( $table ) || empty( $label ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Tên bảng và Tên cột không được để trống.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->add_column( $table, $label, $type, $options );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Đã thêm Cột thành công.' ) );
	}

	/**
	 * AJAX: Sửa Cột (Schema Builder)
	 */
	public function data_update_column() {
		$this->verify_crud_request();
		
		$table   = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$col     = isset( $_POST['col'] ) ? sanitize_text_field( wp_unslash( $_POST['col'] ) ) : '';
		$label   = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'short_text';
		$options = isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '';

		if ( empty( $table ) || empty( $col ) || empty( $label ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Tên bảng, Mã cột và Tên hiển thị không được để trống.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->update_column( $table, $col, $label, $type, $options );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Đã cập nhật Tên và Kiểu cột thành công.' ) );
	}

	/**
	 * AJAX: Xóa Cột (Schema Builder)
	 */
	public function data_drop_column() {
		$this->verify_crud_request();
		
		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$col   = isset( $_POST['col'] ) ? sanitize_text_field( wp_unslash( $_POST['col'] ) ) : '';

		if ( empty( $table ) || empty( $col ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Không xác định được Cột cần xóa.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->drop_column( $table, $col );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Đã vứt bỏ Cột này vĩnh viễn.' ) );
	}

	/**
	 * AJAX: Quản lý Option của Cột Select (Thêm/Sửa mass update)
	 */
	public function data_manage_select_option() {
		$this->verify_crud_request();

		$table   = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$col     = isset( $_POST['column'] ) ? sanitize_text_field( wp_unslash( $_POST['column'] ) ) : '';
		$action  = isset( $_POST['opt_action'] ) ? sanitize_text_field( wp_unslash( $_POST['opt_action'] ) ) : ''; 
		$old_val = isset( $_POST['old_val'] ) ? wp_unslash( $_POST['old_val'] ) : '';
		$new_val = isset( $_POST['new_val'] ) ? wp_unslash( $_POST['new_val'] ) : '';

		if ( empty( $table ) || empty( $col ) || empty( $action ) || empty( $new_val ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Thiếu dữ liệu cấu hình Option.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->manage_select_options( $table, $col, $action, $old_val, $new_val );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 
			'message' => 'Cập nhật thành công.',
			'options' => $result['options'] 
		) );
	}

	/**
	 * Nơi tiếp nhận Request khi User chọn Cài Mẫu (VD: Bấm Ecommerce).
	 */
	public function install_data_template() {
		// 1. Kiểm tra Quyền Hạn
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Bảo mật: Bạn không có quyền Administrator.' ) );
		}
		
		// 2. Kiểm tra Nonce tránh lỗi bảo mật giả mạo CSRF
		check_ajax_referer( 'ska_data_nonce', 'security' );

		// 3. Lọc ID Đầu Vào (Sanitize)
		$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';

		if ( empty( $template_id ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Không xác định được Mẫu dữ liệu (Template ID bị trống).' ) );
		}

		// 4. Kích hoạt Cỗ Máy cài đặt Database (Logic nặng chuyển sang Core)
		$engine = Database_Engine::get_instance();
		$result = $engine->install_schema( $template_id );

		if ( is_wp_error( $result ) ) {
			// Thất bại: Trả về lỗi do dbDelta hoặc khai báo Sai.
			wp_send_json_error( array( 'message' => 'Lỗi Đúc Schema CSDL: ' . $result->get_error_message() ) );
		}

		// 5. Build URL Chuyển Trang (Redirect) sau khi đúc Schema thành công
		$main_table   = $result['main_table'];
		$redirect_url = admin_url( 'admin.php?page=ska-data-pro-manage&table=' . $main_table );

		wp_send_json_success( array(
			'message'      => 'Khởi tạo Bảng dữ liệu thành công!',
			'redirect_url' => $redirect_url,
		) );
	}

	/**
	 * Hàm nội bộ: Xác thực Quyền và Nonce trước khi thực hiện CRUD.
	 */
	private function verify_crud_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Bảo mật: Bạn không có quyền thao tác dữ liệu.' ) );
		}
		check_ajax_referer( 'ska_data_nonce', 'security' );
	}

	/**
	 * AJAX: Thêm Dòng Trống
	 */
	public function data_add_row() {
		$this->verify_crud_request();
		
		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		if ( empty( $table ) ) {
			wp_send_json_error( array( 'message' => 'Tham số tên bảng bị thiếu.' ) );
		}

		$engine = Database_Engine::get_instance();
		$id = $engine->add_empty_row( $table );

		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}

		wp_send_json_success( array( 'id' => $id, 'message' => 'Thêm dòng thành công.' ) );
	}

	/**
	 * AJAX: Cập Nhật 1 Ô (Cell Inline Edit)
	 */
	public function data_update_cell() {
		$this->verify_crud_request();
		
		$table  = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$id     = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$column = isset( $_POST['column'] ) ? sanitize_text_field( wp_unslash( $_POST['column'] ) ) : '';
		
		// Chú ý: Value có thể là Text dài có chứa HTML hoặc kí tự đặc biệt, wp_unslash là cần thiết
		// Việc Escape an toàn (SQLi) sẽ được \wpdb->update lo liệu ở Backend.
		$value  = isset( $_POST['value'] ) ? wp_unslash( $_POST['value'] ) : '';

		if ( empty( $table ) || empty( $id ) || empty( $column ) ) {
			wp_send_json_error( array( 'message' => 'Sai cấu trúc tải dữ liệu lên Server.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->update_cell( $table, $id, $column, $value );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Đã tự động lưu.' ) );
	}

	/**
	 * AJAX: Xóa Dòng
	 */
	public function data_delete_row() {
		$this->verify_crud_request();

		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$id    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;

		if ( empty( $table ) || empty( $id ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi thiếu ID để xóa.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->delete_row( $table, $id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Xóa vĩnh viễn thành công.' ) );
	}

	/**
	 * AJAX: Tạo Bảng Mới
	 */
	public function data_create_table() {
		$this->verify_crud_request();

		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$icon  = isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : 'dashicons-admin-page';
		$group = isset( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : 'custom';

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Tên bảng không được để trống.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->create_custom_table( $name, $icon, $group );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Trả về slug của table mới để JS reload trang tới đúng bảng mới đó
		wp_send_json_success( array( 'table' => $result ) );
	}

	/**
	 * AJAX: Đổi Tên Bảng (Cập nhật Dictionary)
	 */
	public function data_rename_table() {
		$this->verify_crud_request();

		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';
		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$icon  = isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : 'dashicons-admin-page';
		$group = isset( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : 'custom';

		if ( empty( $table ) || empty( $name ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Thông tin bảng không hợp lệ.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->rename_custom_table( $table, $name, $icon, $group );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Cập nhật bảng thành công.' ) );
	}

	/**
	 * AJAX: Tàn Sát Bảng
	 */
	public function data_drop_table() {
		$this->verify_crud_request();

		$table = isset( $_POST['table'] ) ? sanitize_text_field( wp_unslash( $_POST['table'] ) ) : '';

		if ( empty( $table ) ) {
			wp_send_json_error( array( 'message' => 'Lỗi: Không xác định được bảng cần Xóa.' ) );
		}

		$engine = Database_Engine::get_instance();
		$result = $engine->drop_table( $table );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Đã tan biến.' ) );
	}

	/**
	 * AJAX: Tìm kiếm Relation (Popover DataGrid)
	 */
	public function data_search_relation() {
		$this->verify_crud_request();
		
		$target_table = isset( $_POST['target_table'] ) ? sanitize_text_field( wp_unslash( $_POST['target_table'] ) ) : '';
		$keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		
		if ( empty( $target_table ) ) {
			wp_send_json_error( array( 'message' => 'Thiếu thông số Bảng Đích.' ) );
		}
		
		global $wpdb;

		// Bảo mật SQL Injection mở rộng (Nới rào cản cho Core WP)
		$is_core_wp_table = ( $target_table === $wpdb->posts || $target_table === $wpdb->users );
		if ( strpos( $target_table, $wpdb->prefix . 'ska_data_' ) !== 0 && ! $is_core_wp_table ) {
			wp_send_json_error( array( 'message' => 'Bảng không hợp lệ trong Hệ sinh thái.' ) );
		}

		// 1. Phân tích cột Name/Title (Lấy cột Varchar đầu tiên làm Nhãn)
		$display_col = 'id'; // Fallback
		$extra_where = '';
		$id_col = 'id'; // Mặc định bảng Flat là id thường

		if ( $target_table === $wpdb->posts ) {
			$id_col      = 'ID'; // WP Core dùng chữ In Hoa
			$display_col = 'post_title';
			$extra_where = " AND post_status = 'publish'"; // Chỉ gọi bài Published để an toàn
		} elseif ( $target_table === $wpdb->users ) {
			$id_col      = 'ID';
			$display_col = 'display_name';
		} else {
			// Cơ chế Dò Schema rùa bò dành cho Custom Flat Tables
			$columns = $wpdb->get_results( "DESCRIBE `{$target_table}`" );
			if ( empty( $columns ) ) {
				wp_send_json_error( array( 'message' => 'Bảng đích không tồn tại hoặc rỗng Schema.' ) );
			}
			foreach ( $columns as $col ) {
				if ( strpos( strtolower( $col->Type ), 'varchar' ) !== false || strpos( strtolower( $col->Type ), 'text' ) !== false ) {
					$display_col = $col->Field;
					break;
				}
			}
		}

		// 2. Tác nghiệp Truy vấn Tìm Kiếm
		$results = array();
		if ( ! empty( $keyword ) ) {
			$sql = $wpdb->prepare( "SELECT `{$id_col}` AS id_val, `{$display_col}` AS label FROM `{$target_table}` WHERE `{$display_col}` LIKE %s {$extra_where} LIMIT 15", '%' . $wpdb->esc_like( $keyword ) . '%' );
		} else {
			$sql = "SELECT `{$id_col}` AS id_val, `{$display_col}` AS label FROM `{$target_table}` WHERE 1=1 {$extra_where} ORDER BY `{$id_col}` DESC LIMIT 15";
		}

		$rows = $wpdb->get_results( $sql, ARRAY_A );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				$results[] = array(
					'id'    => (int) $row['id_val'],
					'label' => ! empty( $row['label'] ) ? $row['label'] : 'Bản ghi #' . $row['id_val'],
				);
			}
		}

		wp_send_json_success( array( 'items' => $results ) );
	}

	/**
	 * AJAX: Lấy danh sách Toàn bộ Cột Đích (Vật lý) cho tính năng Rollup
	 */
	public function data_get_table_columns() {
		$this->verify_crud_request();
		
		$target_table = isset( $_POST['target_table'] ) ? sanitize_text_field( wp_unslash( $_POST['target_table'] ) ) : '';
		if ( empty( $target_table ) ) {
			wp_send_json_error( array( 'message' => 'Thiếu thông số Bảng Đích.' ) );
		}
		
		global $wpdb;
		
		// Xử lý nhánh WP Core
		if ( $target_table === $wpdb->posts ) {
			$columns = array(
				array( 'slug' => 'ID', 'label' => 'ID' ),
				array( 'slug' => 'post_title', 'label' => 'Tiêu đề (post_title)' ),
				array( 'slug' => 'post_name', 'label' => 'Link bài viết Slug (post_name)' ),
				array( 'slug' => 'post_author', 'label' => 'ID Tác giả (post_author)' ),
				array( 'slug' => 'post_content', 'label' => 'Nội dung (post_content)' ),
				array( 'slug' => 'post_excerpt', 'label' => 'Mô tả ngắn (post_excerpt)' ),
				array( 'slug' => 'post_date', 'label' => 'Ngày tạo (post_date)' ),
			);
			
			// Lấy các meta_key từ postmeta (bỏ các meta ẩn của hệ thống WP cho gọn, chỉ giữ lại các Meta sạch từ ACF/WooCommerce/Thumnbail)
			$meta_keys = $wpdb->get_col( "
				SELECT DISTINCT meta_key 
				FROM `{$wpdb->postmeta}` 
				WHERE meta_key NOT LIKE '\_oembed\_%' 
				  AND meta_key NOT LIKE '\_edit\_%' 
				  AND meta_key NOT LIKE '\_wp\_%' 
				  AND meta_key NOT LIKE '\_transient\_%'
				  AND meta_key NOT IN ('_pingme', '_encloseme')
				ORDER BY meta_key ASC 
				LIMIT 200
			" );
			if ( $meta_keys ) {
				foreach ( $meta_keys as $key ) {
					$columns[] = array( 'slug' => $key, 'label' => 'Meta: ' . $key );
				}
			}
			wp_send_json_success( array( 'columns' => $columns ) );
		}
		
		if ( $target_table === $wpdb->users ) {
			$columns = array(
				array( 'slug' => 'ID', 'label' => 'ID' ),
				array( 'slug' => 'user_login', 'label' => 'Tài khoản (user_login)' ),
				array( 'slug' => 'user_email', 'label' => 'Email (user_email)' ),
				array( 'slug' => 'display_name', 'label' => 'Tên hiển thị (display_name)' ),
			);
			
			// Lấy usermeta, lọc các meta hệ thống (session, dashboard, nav_menu...)
			$meta_keys = $wpdb->get_col( "
				SELECT DISTINCT meta_key 
				FROM `{$wpdb->usermeta}` 
				WHERE meta_key NOT LIKE 'session\_%' 
				  AND meta_key NOT LIKE 'closedpostboxes\_%'
				  AND meta_key NOT LIKE 'metaboxhidden\_%'
				  AND meta_key NOT LIKE 'meta-box-order\_%'
				  AND meta_key NOT LIKE 'wp\_dashboard\_%'
				  AND meta_key NOT LIKE 'nav\_menu\_%'
				  AND meta_key NOT LIKE 'managenav-%'
				ORDER BY meta_key ASC 
				LIMIT 200
			" );
			if ( $meta_keys ) {
				foreach ( $meta_keys as $key ) {
					$columns[] = array( 'slug' => $key, 'label' => 'Meta: ' . $key );
				}
			}
			wp_send_json_success( array( 'columns' => $columns ) );
		}

		// Lấy Schema Vật lý bù trừ cho lỗi rớt Dictionary
		$columns = $wpdb->get_results( "DESCRIBE `{$target_table}`" );
		if ( empty( $columns ) ) {
			wp_send_json_error( array( 'message' => 'Bảng đích không tồn tại hoặc Rỗng.' ) );
		}

		$all_dict    = get_option('ska_data_dictionary', array());
		$target_dict = isset($all_dict[$target_table]) ? $all_dict[$target_table] : array();

		$results = array();
		foreach ( $columns as $col ) {
			$slug = $col->Field;
			$label = isset($target_dict[$slug]['label']) ? $target_dict[$slug]['label'] : $slug;
			$results[] = array(
				'slug'  => $slug,
				'label' => $label
			);
		}

		wp_send_json_success( array( 'columns' => $results ) );
	}
}
