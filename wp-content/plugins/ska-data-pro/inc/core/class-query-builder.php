<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Query_Builder
 * Đảm nhiệm việc truy xuất dữ liệu từ các Bảng phẳng (Flat Tables) theo yêu cầu.
 * Đóng vai trò làm bộ não Nội Suy (Dynamic Content Backend).
 */
class Query_Builder {

	/**
	 * Instance
	 *
	 * @var Query_Builder
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Hook chính để các Plugin khác gọi Data mà không phụ thuộc Class Name cứng
		add_filter( 'ska_data_query', array( $this, 'execute_query' ), 10, 2 );
		add_filter( 'ska_data_get_row', array( $this, 'get_single_row' ), 10, 3 );
	}

	/**
	 * Thực thi truy vấn danh sách (Array) dựa trên tham số linh hoạt.
	 *
	 * @param array $results Dữ liệu kết quả truyền chéo (mặc định rỗng).
	 * @param array $args Tham số Query (table, where, limit, orderby, order).
	 * @return array
	 */
	public function execute_query( $results, $args ) {
		global $wpdb;

		if ( empty( $args['table'] ) ) {
			return $results; // Không có table thì trả về mảng gốc
		}

		// Bảo mật và tự động hóa tên bảng
		$raw_table  = sanitize_text_field( $args['table'] );
		
		// Tự động đệm 'ska_data_' nếu dev chỉ gõ tên ngắn
		if ( strpos( $raw_table, 'ska_data_' ) !== 0 && strpos( $raw_table, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			$raw_table = 'ska_data_' . ltrim( $raw_table, '_' );
		}

		$table_name = ( strpos( $raw_table, $wpdb->prefix ) === 0 ) ? $raw_table : $wpdb->prefix . $raw_table;

		// Ngăn chặn SQL Injection thông qua tên bảng
		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return $results;
		}

		// Xây dựng câu lệnh
		$sql = "SELECT * FROM `{$table_name}`";
		$where_clauses = array();
		$prepare_values = array();

		// Xử lý WHERE - Đơn giản hóa cho MVP
		// $args['where'] = array( 'status' => 'publish', 'category_id' => 5 )
		if ( ! empty( $args['where'] ) && is_array( $args['where'] ) ) {
			foreach ( $args['where'] as $col => $val ) {
				$clean_col = sanitize_key( $col );
				$where_clauses[] = "`{$clean_col}` = %s";
				$prepare_values[] = $val;
			}
		}

		if ( ! empty( $where_clauses ) ) {
			$sql .= " WHERE " . implode( " AND ", $where_clauses );
		}

		// Xử lý ORDER BY
		if ( ! empty( $args['orderby'] ) ) {
			$orderby = sanitize_key( $args['orderby'] );
			$order   = ( isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';
			$sql .= " ORDER BY `{$orderby}` {$order}";
		} else {
			$sql .= " ORDER BY id DESC"; // Mặc định bảng phẳng Ska luôn có cột id
		}

		// Xử lý LIMIT & OFFSET
		if ( ! empty( $args['limit'] ) ) {
			$limit  = absint( $args['limit'] );
			$offset = ! empty( $args['offset'] ) ? absint( $args['offset'] ) : 0;
			$sql .= " LIMIT {$offset}, {$limit}";
		}

		// Prepare và Thực thi
		if ( ! empty( $prepare_values ) ) {
			$final_sql = $wpdb->prepare( $sql, $prepare_values );
			$data = $wpdb->get_results( $final_sql, ARRAY_A );
		} else {
			$data = $wpdb->get_results( $sql, ARRAY_A );
		}

		return $data ? $data : array();
	}

	/**
	 * Lấy một Dòng duy nhất dựa vào Khóa chính ID.
	 * 
	 * @param array|null $row Dòng khởi tạo (mặc định null).
	 * @param string $table Tên bảng.
	 * @param int $id Khóa chính.
	 * @return array|null
	 */
	public function get_single_row( $row, $table, $id ) {
		global $wpdb;

		if ( empty( $table ) || empty( $id ) ) return $row;

		$raw_table  = sanitize_text_field( $table );
		
		// Tự động đệm 'ska_data_' nếu dev chỉ gõ tên ngắn
		if ( strpos( $raw_table, 'ska_data_' ) !== 0 && strpos( $raw_table, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			$raw_table = 'ska_data_' . ltrim( $raw_table, '_' );
		}

		$table_name = ( strpos( $raw_table, $wpdb->prefix ) === 0 ) ? $raw_table : $wpdb->prefix . $raw_table;

		if ( strpos( $table_name, $wpdb->prefix . 'ska_data_' ) !== 0 ) {
			return $row;
		}

		$sql = $wpdb->prepare( "SELECT * FROM `{$table_name}` WHERE id = %d LIMIT 1", absint( $id ) );
		$result = $wpdb->get_row( $sql, ARRAY_A );

		return $result ? $result : $row;
	}
}
