<?php
defined('ABSPATH') || exit;

class Ska_Logic_DB_Action implements Ska_Logic_Node {

    /**
     * Thực thi các thao tác Cơ sở dữ liệu (Insert, Update, Delete)
     * Trả về định dạng chuẩn của DAG Node.
     */
    public function execute($payload, $config) {
        global $wpdb;

        $actionType = isset($config['actionType']) ? $config['actionType'] : 'insert';
        $table      = isset($config['table']) ? $config['table'] : '';
        $mappings   = isset($config['mappings']) ? $config['mappings'] : [];
        $recordIdExpr = isset($config['recordId']) ? $config['recordId'] : '';

        // Phải có table mới chạy được
        if (empty($table)) {
            error_log(__( 'Ska_Logic_DB_Action: Skipped because Table was not selected.', 'ska-logic-engine' ));
            return ['payload' => $payload, 'port' => 'main']; 
        }

        error_log(__( 'Ska_Logic_DB_Action: Start processing for Table {$table}. ', 'ska-logic-engine' ));
        error_log("Mappings: " . print_r($mappings, true));

        // Tự động giải nén các ánh xạ (Auto-map) qua SkaFX
        $data_to_save = [];
        if ($actionType !== 'delete') {
            // 1. AUTO-MAP: Lấy danh sách cột thực tế từ Database
            // Dùng DESCRIBE thay vì SHOW COLUMNS cho tương thích, lấy cột Field (index 0)
            $columns = $wpdb->get_col("DESCRIBE {$table}", 0);
            
            if (!empty($columns)) {
                foreach ($columns as $col) {
                    // Thường không tự động ghi đè id tự tăng
                    if (strtolower($col) === 'id') continue; 
                    
                    if (array_key_exists($col, $payload)) {
                        $data_to_save[$col] = $payload[$col];
                    }
                }
            }

            // 2. MAPPING TỪ UI: Ghi đè lên auto-map nếu user có set custom expression
            if (!empty($mappings) && is_array($mappings)) {
                foreach ($mappings as $col => $expr) {
                    if (trim($expr) === '') {
                        continue; // Bỏ qua nếu mapping rỗng (để Auto-map làm việc)
                    }
                    
                    $data_to_save[$col] = $this->evaluate_field($expr, $payload);
                }
            }

            // 3. CHUẨN HÓA DỮ LIỆU: Chuẩn hóa trường Relation và các dữ liệu dạng mảng trước khi ghi DB
            $all_dict = get_option('ska_data_dictionary', []);
            $table_dict = isset($all_dict[$table]) ? $all_dict[$table] : [];
            
            foreach ($data_to_save as $col => $val) {
                if (isset($table_dict[$col])) {
                    $col_type = isset($table_dict[$col]['type']) ? $table_dict[$col]['type'] : '';
                    if ($col_type === 'relation') {
                        if (is_array($val)) {
                            $ids = [];
                            foreach ($val as $item) {
                                if (is_array($item) && isset($item['id'])) {
                                    $ids[] = $item['id'];
                                } elseif (is_object($item) && isset($item->id)) {
                                    $ids[] = $item->id;
                                } else {
                                    $ids[] = $item;
                                }
                            }
                            $data_to_save[$col] = implode(',', array_filter(array_map('trim', $ids)));
                        } elseif (is_string($val)) {
                            $trimmed_val = trim($val);
                            if (str_starts_with($trimmed_val, '[') || str_starts_with($trimmed_val, '{')) {
                                $decoded = json_decode($trimmed_val, true);
                                if (is_array($decoded)) {
                                    $ids = [];
                                    foreach ($decoded as $item) {
                                        if (is_array($item) && isset($item['id'])) {
                                            $ids[] = $item['id'];
                                        } else {
                                            $ids[] = $item;
                                        }
                                    }
                                    $data_to_save[$col] = implode(',', array_filter(array_map('trim', $ids)));
                                }
                            }
                        }
                    } else {
                        if (is_array($val)) {
                            $data_to_save[$col] = wp_json_encode($val);
                        }
                    }
                }
            }
        }

        switch ($actionType) {
            case 'insert':
                if (!empty($data_to_save)) {
                    $result = $wpdb->insert($table, $data_to_save);
                    if ($result !== false && $wpdb->insert_id) {
                        // Nhồi ngược ID vừa tạo vào Payload để Node sau xài
                        $payload['db_last_insert_id'] = $wpdb->insert_id;
                        error_log("Ska_Logic_DB_Action: Đã Insert thành công vào {$table}, ID: " . $wpdb->insert_id);
                    } else {
                        error_log("Ska_Logic_DB_Action: Lỗi Insert Table {$table} - " . $wpdb->last_error);
                        $payload['_latest_insert'] = ['result' => false, 'table_name' => $table];
                    }
                } else {
                    error_log(__( 'Ska_Logic_DB_Action: Skip Insert due to empty data.', 'ska-logic-engine' ));
                }
                break;

            case 'update':
                $resolved_record = $this->evaluate_field($recordIdExpr, $payload);
                $record_id = $resolved_record;
                if (!empty($record_id) && !empty($data_to_save)) {
                    $result = $wpdb->update($table, $data_to_save, ['id' => $record_id]);
                    if ($result === false) {
                        error_log("Ska_Logic_DB_Action: Lỗi Update Table {$table} - " . $wpdb->last_error);
                    } else {
                        error_log(__( 'Ska_Logic_DB_Action: Successfully updated record {$record_id} in {$table}.', 'ska-logic-engine' ));
                    }
                } else {
                    error_log(__( 'Ska_Logic_DB_Action: Aborted Update due to missing Record ID or empty Data.', 'ska-logic-engine' ));
                }
                break;

            case 'delete':
                $resolved_record = $this->evaluate_field($recordIdExpr, $payload);
                $record_id = $resolved_record;
                if (!empty($record_id)) {
                    $result = $wpdb->delete($table, ['id' => $record_id]);
                    if ($result === false) {
                        error_log("Ska_Logic_DB_Action: Lỗi Delete Table {$table} - " . $wpdb->last_error);
                    } else {
                        error_log(__( 'Ska_Logic_DB_Action: Successfully deleted record {$record_id} in {$table}.', 'ska-logic-engine' ));
                    }
                } else {
                    error_log(__( 'Ska_Logic_DB_Action: Skip Delete due to missing Record ID.', 'ska-logic-engine' ));
                }
                break;
        }

        // Node Action trả về payload và cổng main
        return ['payload' => $payload, 'port' => 'main'];
    }

    /**
     * Helper để xử lý nội suy template hoặc biểu thức
     */
    private function evaluate_field($expr, $payload) {
        if (empty($expr)) return '';

        // Template {{ ... }}
        if (strpos($expr, '{{') !== false) {
            return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) use ($payload) {
                $expression = trim($matches[1]);
                $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute($expression, $payload);
                return $eval_result['last_val'] ?? '';
            }, $expr);
        }

        // Biểu thức trực tiếp
        $resolved = \Ska\Logic\SkaFX\SkaFX_Engine::execute($expr, $payload);
        
        // Fallback cho [...]
        if (isset($resolved['error']) || ($resolved['last_val'] === null && strpos($expr, '[') !== false)) {
             return preg_replace_callback('/\[([a-zA-Z0-9_$.]+)\]/', function($matches) use ($payload) {
                $var_name = $matches[1];
                $res = \Ska\Logic\SkaFX\SkaFX_Engine::execute($var_name, $payload);
                return $res['last_val'] ?? '';
            }, $expr);
        }

        return $resolved['last_val'] ?? $expr;
    }
}

