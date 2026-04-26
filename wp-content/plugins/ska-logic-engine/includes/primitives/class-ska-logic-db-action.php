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
            error_log("Ska_Logic_DB_Action: Bỏ qua do chưa chọn Table.");
            return ['payload' => $payload, 'port' => 'main']; 
        }

        error_log("Ska_Logic_DB_Action: Bắt đầu xử lý cho Table {$table}. Action: {$actionType}");
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
                    
                    $resolved = \Ska\Logic\SkaFX\SkaFX_Engine::execute($expr, $payload);
                    
                    // Fallback thông minh:
                    // 1. Nếu có lỗi cú pháp (ví dụ gõ 'khách lẻ' mà không bọc nháy kép)
                    // 2. Hoặc kết quả là null VÀ chuỗi không chứa ngoặc vuông (gõ 'vip' -> thành tên biến, tìm ko thấy ra null)
                    if ( isset($resolved['error']) || ($resolved['last_val'] === null && strpos($expr, '[') === false) ) {
                        
                        // Xử lý nội suy biến đơn giản như Template (VD: "Mã khách: [id]")
                        $final_string = preg_replace_callback('/\[([a-zA-Z0-9_$.]+)\]/', function($matches) use ($payload) {
                            $var_name = $matches[1];
                            $res = \Ska\Logic\SkaFX\SkaFX_Engine::execute('[' . $var_name . ']', $payload);
                            return isset($res['last_val']) && !isset($res['error']) ? $res['last_val'] : '';
                        }, $expr);
                        
                        $data_to_save[$col] = $final_string;
                    } else {
                        $data_to_save[$col] = $resolved['last_val'];
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
                    error_log("Ska_Logic_DB_Action: Bỏ qua Insert do Data trống.");
                }
                break;

            case 'update':
                $resolved_record = \Ska\Logic\SkaFX\SkaFX_Engine::execute($recordIdExpr, $payload);
                $record_id = $resolved_record['last_val'] ?? null;
                if (!empty($record_id) && !empty($data_to_save)) {
                    $result = $wpdb->update($table, $data_to_save, ['id' => $record_id]);
                    if ($result === false) {
                        error_log("Ska_Logic_DB_Action: Lỗi Update Table {$table} - " . $wpdb->last_error);
                    } else {
                        error_log("Ska_Logic_DB_Action: Đã Update thành công bản ghi {$record_id} trong {$table}.");
                    }
                } else {
                    error_log("Ska_Logic_DB_Action: Bỏ qua Update do thiếu Record ID hoặc Data trống.");
                }
                break;

            case 'delete':
                $resolved_record = \Ska\Logic\SkaFX\SkaFX_Engine::execute($recordIdExpr, $payload);
                $record_id = $resolved_record['last_val'] ?? null;
                if (!empty($record_id)) {
                    $result = $wpdb->delete($table, ['id' => $record_id]);
                    if ($result === false) {
                        error_log("Ska_Logic_DB_Action: Lỗi Delete Table {$table} - " . $wpdb->last_error);
                    } else {
                        error_log("Ska_Logic_DB_Action: Đã Delete thành công bản ghi {$record_id} trong {$table}.");
                    }
                } else {
                    error_log("Ska_Logic_DB_Action: Bỏ qua Delete do thiếu Record ID.");
                }
                break;
        }

        // Node Action trả về payload và cổng main
        return ['payload' => $payload, 'port' => 'main'];
    }
}

