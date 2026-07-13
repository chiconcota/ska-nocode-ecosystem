<?php
namespace Skaaa\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Data_Fetcher
 * Đọc cấu trúc và dữ liệu thô (Raw) từ hệ thống cơ sở dữ liệu.
 */
class Data_Fetcher {

	/**
	 * Lấy danh sách toàn bộ các Bảng (Tables) thuộc hệ sinh thái Skaaa.
	 * 
	 * @return array Mảng chứa tên các bảng.
	 */
	public static function get_all_tables() {
		global $wpdb;
		
		// Tìm theo tiền tố mặc định + "skaaa_data_"
		$pattern = $wpdb->prefix . 'skaaa_data_%';
		
		// Tránh dùng LIKE vì một số engine phân phối không hỗ trợ tốt `_`.
		// Nhưng trong WP SHOW TABLES LIKE chuẩn là đủ mạnh.
		$tables = $wpdb->get_col( $wpdb->prepare( "SHOW TABLES LIKE %s", $pattern ) );
		
		return $tables ? $tables : array();
	}

	/**
	 * Lấy danh sách các Cột (Columns) và định dạng dữ liệu của bảng.
	 *
	 * @param string $table_name Tên bảng.
	 * @return array Mảng Object (Field, Type, Null, Key, Default, Extra).
	 */
	public static function get_table_columns( $table_name ) {
		global $wpdb;
		
		// Bảo mật chống SQL Injection (Kiểm tra cứng tiền tố)
		if ( strpos( $table_name, $wpdb->prefix . 'skaaa_data_' ) !== 0 ) {
			return array();
		}
		
		// Cú pháp DESCRIBE lấy meta bảng
		$sql = "DESCRIBE `{$table_name}`";
		$columns = $wpdb->get_results( $sql );
		
		return $columns ? $columns : array();
	}

	/**
	 * Lấy Danh sách các dòng dữ liệu (Rows) thô. Hỗ trợ Query Parameters nâng cao.
	 *
	 * @param string $table_name Tên bảng.
	 * @param array $args Tham số truy vấn phụ (orderby, order, filter_field, filter_val).
	 * @param int $limit Số dòng tối đa tải ban đầu.
	 * @return array Mảng kết hợp (Associative Array) các hàng.
	 */
	public static function get_table_rows( $table_name, $args = array(), $limit = 500 ) {
		global $wpdb;
		
		if ( strpos( $table_name, $wpdb->prefix . 'skaaa_data_' ) !== 0 ) {
			return array();
		}

		$limit = absint( $limit );
		
		// Phân trang
		$page   = isset( $args['page'] ) ? max( 1, absint( $args['page'] ) ) : 1;
		$offset = ( $page - 1 ) * $limit;
		
		$where_sql = "1=1";
		$prepare_values = array();

		// 1. FILTER: Lọc dữ liệu
		if ( ! empty( $args['filter_field'] ) && isset( $args['filter_val'] ) && $args['filter_val'] !== '' ) {
			$filter_field = sanitize_key( $args['filter_field'] );
			$filter_op    = ! empty( $args['filter_op'] ) ? strtolower( sanitize_text_field( $args['filter_op'] ) ) : 'like';
			
			if ( $filter_op === 'eq' || $filter_op === '=' ) {
				$val = $args['filter_val'];
				if ( is_numeric( $val ) ) {
					// Hỗ trợ tự động fallback sang JSON_CONTAINS nếu dữ liệu là Relation (lưu dạng mảng JSON)
					$where_sql .= " AND ( `{$filter_field}` = %s OR (JSON_VALID(`{$filter_field}`) AND (JSON_CONTAINS(`{$filter_field}`, %s, '$') OR JSON_CONTAINS(`{$filter_field}`, %s, '$'))) )";
					$prepare_values[] = $val;
					$prepare_values[] = wp_json_encode( (int) $val );
					$prepare_values[] = wp_json_encode( (string) $val );
				} else {
					$where_sql .= " AND `{$filter_field}` = %s";
					$prepare_values[] = $val;
				}
			} elseif ( $filter_op === '!=' ) {
				$where_sql .= " AND `{$filter_field}` != %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '>' ) {
				$where_sql .= " AND `{$filter_field}` > %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '>=' ) {
				$where_sql .= " AND `{$filter_field}` >= %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '<' ) {
				$where_sql .= " AND `{$filter_field}` < %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '<=' ) {
				$where_sql .= " AND `{$filter_field}` <= %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === 'in' ) {
				$in_vals = is_array( $args['filter_val'] ) ? $args['filter_val'] : array_map( 'trim', explode( ',', $args['filter_val'] ) );
				$placeholders = array_fill( 0, count( $in_vals ), '%s' );
				$where_sql .= " AND `{$filter_field}` IN (" . implode( ',', $placeholders ) . ")";
				foreach ( $in_vals as $v ) {
					$prepare_values[] = $v;
				}
			} elseif ( $filter_op === 'json_contains' ) {
				$val_str = (string) $args['filter_val'];
				$val_json = wp_json_encode( $val_str );
				if ( is_numeric( $val_str ) ) {
					// Hỗ trợ mảng lưu số [1] hoặc chuỗi ["1"]
					$where_sql .= " AND ( (JSON_VALID(`{$filter_field}`) AND (JSON_CONTAINS(`{$filter_field}`, %s, '$') OR JSON_CONTAINS(`{$filter_field}`, %s, '$'))) OR FIND_IN_SET(%s, REPLACE(`{$filter_field}`, ' ', '')) > 0 )";
					$prepare_values[] = $val_str;
					$prepare_values[] = $val_json;
					$prepare_values[] = $val_str;
				} else {
					$where_sql .= " AND ( (JSON_VALID(`{$filter_field}`) AND JSON_CONTAINS(`{$filter_field}`, %s, '$')) OR FIND_IN_SET(%s, REPLACE(`{$filter_field}`, ' ', '')) > 0 )";
					$prepare_values[] = $val_json;
					$prepare_values[] = $val_str; // Cho FIND_IN_SET
				}
			} else {
				$where_sql .= " AND `{$filter_field}` LIKE %s";
				$prepare_values[] = '%' . $wpdb->esc_like( $args['filter_val'] ) . '%';
			}
		}

		// 2. ORDER BY & GROUP BY: Gộp nhóm bản chất là sắp xếp nhóm đó trước
		$orderby_sql = "ORDER BY id DESC"; // Mặc định
		$order_parts = array();

		if ( ! empty( $args['group_by'] ) ) {
			$group_field = sanitize_key( $args['group_by'] );
			$order_parts[] = "`{$group_field}` ASC"; // Luôn đưa nhóm lên đầu
		}

		if ( ! empty( $args['orderby'] ) ) {
			$orderby = sanitize_key( $args['orderby'] );
			$order   = ( isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';
			$order_parts[] = "`{$orderby}` {$order}";
		}

		if ( ! empty( $order_parts ) ) {
			$orderby_sql = "ORDER BY " . implode( ", ", $order_parts );
		}
		
		$sql = "SELECT * FROM `{$table_name}` WHERE {$where_sql} {$orderby_sql} LIMIT {$limit} OFFSET {$offset}";

		if ( ! empty( $prepare_values ) ) {
			$final_sql = $wpdb->prepare( $sql, $prepare_values );
			$rows = $wpdb->get_results( $final_sql, ARRAY_A );
		} else {
			$rows = $wpdb->get_results( $sql, ARRAY_A );
		}
		
		$rows = $rows ? self::enrich_relations( $table_name, $rows ) : array();
		return $rows ? self::enrich_rollups( $table_name, $rows ) : array();
	}

	/**
	 * Đếm tổng số lượng dòng (phục vụ phân trang)
	 *
	 * @param string $table_name Tên bảng.
	 * @param array $args Tham số truy vấn phụ (filter_field, filter_val).
	 * @return int Tổng số dòng.
	 */
	public static function count_table_rows( $table_name, $args = array() ) {
		global $wpdb;
		
		if ( strpos( $table_name, $wpdb->prefix . 'skaaa_data_' ) !== 0 ) {
			return 0;
		}

		$where_sql = "1=1";
		$prepare_values = array();

		// 1. FILTER: Lọc dữ liệu
		if ( ! empty( $args['filter_field'] ) && isset( $args['filter_val'] ) && $args['filter_val'] !== '' ) {
			$filter_field = sanitize_key( $args['filter_field'] );
			$filter_op    = ! empty( $args['filter_op'] ) ? strtolower( sanitize_text_field( $args['filter_op'] ) ) : 'like';
			
			if ( $filter_op === 'eq' || $filter_op === '=' ) {
				$where_sql .= " AND `{$filter_field}` = %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '!=' ) {
				$where_sql .= " AND `{$filter_field}` != %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '>' ) {
				$where_sql .= " AND `{$filter_field}` > %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '>=' ) {
				$where_sql .= " AND `{$filter_field}` >= %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '<' ) {
				$where_sql .= " AND `{$filter_field}` < %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === '<=' ) {
				$where_sql .= " AND `{$filter_field}` <= %s";
				$prepare_values[] = $args['filter_val'];
			} elseif ( $filter_op === 'in' ) {
				$in_vals = is_array( $args['filter_val'] ) ? $args['filter_val'] : array_map( 'trim', explode( ',', $args['filter_val'] ) );
				$placeholders = array_fill( 0, count( $in_vals ), '%s' );
				$where_sql .= " AND `{$filter_field}` IN (" . implode( ',', $placeholders ) . ")";
				foreach ( $in_vals as $v ) {
					$prepare_values[] = $v;
				}
			} elseif ( $filter_op === 'json_contains' ) {
				$val_str = (string) $args['filter_val'];
				$val_json = wp_json_encode( $val_str );
				if ( is_numeric( $val_str ) ) {
					// Hỗ trợ mảng lưu số [1] hoặc chuỗi ["1"]
					$where_sql .= " AND ( (JSON_VALID(`{$filter_field}`) AND (JSON_CONTAINS(`{$filter_field}`, %s, '$') OR JSON_CONTAINS(`{$filter_field}`, %s, '$'))) OR FIND_IN_SET(%s, REPLACE(`{$filter_field}`, ' ', '')) > 0 )";
					$prepare_values[] = $val_str;
					$prepare_values[] = $val_json;
					$prepare_values[] = $val_str;
				} else {
					$where_sql .= " AND ( (JSON_VALID(`{$filter_field}`) AND JSON_CONTAINS(`{$filter_field}`, %s, '$')) OR FIND_IN_SET(%s, REPLACE(`{$filter_field}`, ' ', '')) > 0 )";
					$prepare_values[] = $val_json;
					$prepare_values[] = $val_str; // Cho FIND_IN_SET
				}
			} else {
				$where_sql .= " AND `{$filter_field}` LIKE %s";
				$prepare_values[] = '%' . $wpdb->esc_like( $args['filter_val'] ) . '%';
			}
		}

		$sql = "SELECT COUNT(*) FROM `{$table_name}` WHERE {$where_sql}";

		if ( ! empty( $prepare_values ) ) {
			$final_sql = $wpdb->prepare( $sql, $prepare_values );
			return (int) $wpdb->get_var( $final_sql );
		} else {
			return (int) $wpdb->get_var( $sql );
		}
	}

	/**
	 * Phân giải cột Relation thành Chuỗi JSON Array (Enrichment Layer)
	 */
	private static function enrich_relations( $table_name, $rows ) {
		$all_dict    = get_option('skaaa_data_dictionary', array());
		$table_dict  = isset($all_dict[$table_name]) ? $all_dict[$table_name] : array();
		
		$relation_cols = array();
		foreach( $table_dict as $col_name => $config ) {
			if ( isset($config['type']) && $config['type'] === 'relation' && ! empty( $config['options'] ) ) {
				$relation_cols[$col_name] = $config['options']; // options giữ chức năng Target Table
			}
		}

		if ( empty( $relation_cols ) ) {
			return $rows;
		}

		global $wpdb;

		// Duyệt từng cột Relation
		foreach ( $relation_cols as $col_name => $target_table ) {
			// Nới rào cản bảo mật cho bảng nguyên thủy Core WP
			$is_core_wp_table = ( $target_table === $wpdb->posts || $target_table === $wpdb->users );
			if ( strpos( $target_table, $wpdb->prefix . 'skaaa_data_' ) !== 0 && ! $is_core_wp_table ) continue; // Bảng không hợp lệ
			
			// Thu gom mảng IDs (Unique) từ tất cả Record
			$all_ids = array();
			foreach ( $rows as $row ) {
				if ( ! empty( $row[$col_name] ) ) {
					$val = $row[$col_name];
					$decoded = json_decode( $val, true );
					if ( is_array( $decoded ) ) {
						foreach ( $decoded as $item ) {
							if ( is_array( $item ) && isset( $item['id'] ) ) {
								$all_ids[] = (int) $item['id'];
							} elseif ( is_numeric( $item ) ) {
								$all_ids[] = (int) $item;
							}
						}
					} else {
						$ids = array_map( 'trim', explode( ',', $val ) );
						foreach ( $ids as $id ) {
							if ( is_numeric($id) ) $all_ids[] = (int) $id;
						}
					}
				}
			}
			$all_ids = array_unique($all_ids);

			if ( empty($all_ids) ) continue;

			// Cấu hình linh hoạt hoặc Lấy cột Name của bảng đích
			$display_col = 'id';
			$id_col      = 'id';
			
			if ( $target_table === $wpdb->posts ) {
				$id_col      = 'ID';
				$display_col = 'post_title';
			} elseif ( $target_table === $wpdb->users ) {
				$id_col      = 'ID';
				$display_col = 'display_name';
			} else {
				// Dò kiểu cũ cho Flat Table
				$columns = $wpdb->get_results( "DESCRIBE `{$target_table}`" );
				if ( $columns ) {
					foreach ( $columns as $col ) {
						if ( strpos( strtolower( $col->Type ), 'varchar' ) !== false || strpos( strtolower( $col->Type ), 'text' ) !== false ) {
							$display_col = $col->Field;
							break;
						}
					}
				}
			}

			// Lấy Tên thực tế của các IDs này trong 1 query (O(1) n+1 fix)
			$id_list_str = implode(',', $all_ids);
			$target_rows = $wpdb->get_results( "SELECT `{$id_col}` AS id_val, `{$display_col}` AS label FROM `{$target_table}` WHERE `{$id_col}` IN ({$id_list_str})", ARRAY_A );
			
			$map = array();
			if ( $target_rows ) {
				foreach ( $target_rows as $tr ) {
					$map[ $tr['id_val'] ] = $tr['label'];
				}
			}

			// Extract portal settings for the target table (for Dynamic Link generation)
			$portal_slug = '';
			if ( isset( $all_dict[ $target_table ]['__table_info']['portal_settings']['slug'] ) && ! empty( $all_dict[ $target_table ]['__table_info']['portal_settings']['active'] ) ) {
				$portal_slug = $all_dict[ $target_table ]['__table_info']['portal_settings']['slug'];
			}

			// Đắp Map (Enrich) vào kết quả thô của Rows
			foreach ( $rows as &$row ) {
				if ( ! empty( $row[$col_name] ) ) {
					$val = $row[$col_name];
					$decoded = json_decode( $val, true );
					$local_ids = array();
					if ( is_array( $decoded ) ) {
						foreach ( $decoded as $item ) {
							if ( is_array( $item ) && isset( $item['id'] ) ) {
								$local_ids[] = (int) $item['id'];
							} elseif ( is_numeric( $item ) ) {
								$local_ids[] = (int) $item;
							}
						}
					} else {
						$ids = array_map( 'trim', explode( ',', $val ) );
						foreach ( $ids as $id ) {
							if ( is_numeric($id) ) $local_ids[] = (int) $id;
						}
					}

					$enriched = array();
					$has_valid = false;
					foreach ( $local_ids as $id ) {
						if ( isset($map[ $id ]) ) {
							$item = array( 'id' => $id, 'label' => $map[ $id ] );
							
							// Sinh link động nếu bảng đích có khai báo Portal
							if ( $portal_slug !== '' ) {
								$item['url'] = home_url( '/' . $portal_slug . '/' . $id . '/' );
							}

							$enriched[] = $item;
							$has_valid = true;
						}
					}
					// Ép kiểu Data payload thành mảng JSON để đẩy qua REST/Formula Engine cho sướng
					if ( $has_valid ) {
						$row[$col_name] = wp_json_encode( $enriched );
					} else {
						$row[$col_name] = ''; // Xóa sạch rác
					}
				}
			}
			unset( $row );
		}

		return $rows;
	}

	/**
	 * Phân giải cột Rollup thành Giá trị đích tra cứu (Lookup).
	 */
	private static function enrich_rollups( $table_name, $rows ) {
		$all_dict    = get_option('skaaa_data_dictionary', array());
		$table_dict  = isset($all_dict[$table_name]) ? $all_dict[$table_name] : array();
		
		$rollup_cols = array();
		foreach( $table_dict as $col_name => $config ) {
			if ( isset($config['type']) && $config['type'] === 'rollup' && ! empty( $config['options'] ) ) {
				// options format mong đợi lúc này: "relation_col,target_col"
				$opts = array_map('trim', explode(',', $config['options']));
				if ( count($opts) >= 2 ) {
					$rollup_cols[$col_name] = array(
						'relation_col' => $opts[0],
						'target_col'   => $opts[1]
					);
				}
			}
		}

		if ( empty( $rollup_cols ) || empty( $rows ) ) {
			return $rows;
		}

		global $wpdb;

		// Duyệt từng cột Rollup cần xử lý
		foreach ( $rollup_cols as $col_name => $rollup_config ) {
			$rel_col    = $rollup_config['relation_col'];
			$target_col = $rollup_config['target_col'];

			// Kiểm tra cột relation có hợp lệ không (tìm bảng đích từ relation đó)
			if ( ! isset( $table_dict[$rel_col] ) || $table_dict[$rel_col]['type'] !== 'relation' ) {
				continue; // Cấu hình lỗi (không trỏ đúng cột relation)
			}

			// Bảng đích khai thác là đây
			$target_table = $table_dict[$rel_col]['options']; 
			$is_core_wp_table = ( $target_table === $wpdb->posts || $target_table === $wpdb->users );
			if ( strpos( $target_table, $wpdb->prefix . 'skaaa_data_' ) !== 0 && ! $is_core_wp_table ) continue;
			
			$id_col = 'id';
			if ( $target_table === $wpdb->posts || $target_table === $wpdb->users ) {
				$id_col = 'ID';
			}

			// Thu gom mảng IDs (Unique) từ tất cả Record hiện tại (thông qua cột relation)
			$all_ids = array();
			foreach ( $rows as $row ) {
				// Lưu ý: Dữ liệu relation ở mức DB là chuỗi CSV ID (vd: "32, 45").
				// NHƯNG nếu data đã đi qua hàm `enrich_relations` trước đó, nó có thể đã bị biến thành mảng JSON `[{"id":32,"label":"..."},...]`
				if ( ! empty( $row[$rel_col] ) ) {
					$rel_val = $row[$rel_col];
					$decoded = json_decode($rel_val, true);
					if ( is_array($decoded) ) {
						foreach ($decoded as $item) {
							if ( isset($item['id']) ) $all_ids[] = (int) $item['id'];
						}
					} else {
						// Fallback đọc raw CSV format
						$ids = array_map( 'trim', explode(',', $rel_val) );
						foreach ( $ids as $id ) {
							if ( is_numeric($id) ) $all_ids[] = (int) $id;
						}
					}
				}
			}
			$all_ids = array_unique($all_ids);

			if ( empty($all_ids) ) continue;

			$id_list_str = implode(',', $all_ids);
			$map = array();

			// Lấy Data thực tế từ bảng đích
			$is_post_meta = false;
			$is_user_meta = false;

			if ( $target_table === $wpdb->posts ) {
				$standard_cols = array( 'ID', 'post_title', 'post_content', 'post_excerpt', 'post_status', 'post_date', 'post_type', 'post_name', 'post_author' );
				if ( ! in_array( $target_col, $standard_cols, true ) ) {
					$is_post_meta = true;
				}
			} elseif ( $target_table === $wpdb->users ) {
				$standard_cols = array( 'ID', 'user_login', 'user_email', 'display_name' );
				if ( ! in_array( $target_col, $standard_cols, true ) ) {
					$is_user_meta = true;
				}
			}

			if ( $is_post_meta ) {
				$meta_rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id AS id_val, meta_value AS val FROM `{$wpdb->postmeta}` WHERE meta_key = %s AND post_id IN ({$id_list_str})", $target_col ), ARRAY_A );
				if ( $meta_rows && !is_wp_error($meta_rows) ) {
					foreach ( $meta_rows as $tr ) {
						if ( ! isset( $map[ $tr['id_val'] ] ) ) {
							$map[ $tr['id_val'] ] = $tr['val'];
						} else {
							$map[ $tr['id_val'] ] .= ', ' . $tr['val'];
						}
					}
				}
			} elseif ( $is_user_meta ) {
				$meta_rows = $wpdb->get_results( $wpdb->prepare( "SELECT user_id AS id_val, meta_value AS val FROM `{$wpdb->usermeta}` WHERE meta_key = %s AND user_id IN ({$id_list_str})", $target_col ), ARRAY_A );
				if ( $meta_rows && !is_wp_error($meta_rows) ) {
					foreach ( $meta_rows as $tr ) {
						if ( ! isset( $map[ $tr['id_val'] ] ) ) {
							$map[ $tr['id_val'] ] = $tr['val'];
						} else {
							$map[ $tr['id_val'] ] .= ', ' . $tr['val'];
						}
					}
				}
			} else {
				// Cột logic vật lý bình thường
				// Vì target_col do user gõ, cần sanitize để chống lỗi SQL (Chỉ cho phép alpha-numeric và underscore).
				$safe_target_col = preg_replace('/[^a-zA-Z0-9_]/', '', $target_col);
				
				// Bypass error output cho câu select dynamic column
				$suppress = $wpdb->suppress_errors( true );
				$target_rows = $wpdb->get_results( "SELECT `{$id_col}` AS id_val, `{$safe_target_col}` AS val FROM `{$target_table}` WHERE `{$id_col}` IN ({$id_list_str})", ARRAY_A );
				$wpdb->suppress_errors( $suppress );

				if ( $target_rows && !is_wp_error($target_rows) ) {
					foreach ( $target_rows as $tr ) {
						$map[ $tr['id_val'] ] = $tr['val'];
					}
				}
			}

			// Đắp dữ liệu (Lookup array strings) vào row
			foreach ( $rows as &$row ) {
				$matched_vals = array();
				if ( ! empty( $row[$rel_col] ) ) {
					$rel_val = $row[$rel_col];
					$decoded = json_decode($rel_val, true);
					$local_ids = array();
					
					if ( is_array($decoded) ) {
						foreach ($decoded as $item) {
							if ( isset($item['id']) ) $local_ids[] = (int) $item['id'];
						}
					} else {
						$ids = array_map( 'trim', explode(',', $rel_val) );
						foreach ( $ids as $id ) {
							if ( is_numeric($id) ) $local_ids[] = (int) $id;
						}
					}
					
					foreach($local_ids as $lid) {
						if (isset($map[$lid]) && $map[$lid] !== '') {
							$matched_vals[] = $map[$lid];
						}
					}
				}
				
				// Dán dữ liệu gộp chuỗi bằng dấu phẩy
				if ( !empty($matched_vals) ) {
					$row[$col_name] = implode(', ', $matched_vals);
				} else {
					$row[$col_name] = ''; 
				}
			}
			unset( $row );
		}

		return $rows;
	}
}
