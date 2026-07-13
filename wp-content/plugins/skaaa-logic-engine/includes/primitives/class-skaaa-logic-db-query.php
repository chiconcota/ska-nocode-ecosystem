<?php
defined('ABSPATH') || exit;

class Skaaa_Logic_DB_Query implements Skaaa_Logic_Node {

    /**
     * Thực thi các thao tác Cơ sở dữ liệu (Select)
     * Trả về định dạng chuẩn của DAG Node.
     */
    public function execute($payload, $config) {
        global $wpdb;

        $table      = isset($config['table']) ? $config['table'] : '';
        $returnType = isset($config['returnType']) ? $config['returnType'] : 'multiple';
        $conditions = isset($config['conditions']) ? $config['conditions'] : [];
        $orderBy    = isset($config['orderBy']) ? $config['orderBy'] : '';
        $orderDir   = isset($config['orderDir']) ? $config['orderDir'] : 'DESC';
        $limit      = isset($config['limit']) ? $config['limit'] : '';
        $resultVar  = isset($config['resultVar']) ? trim($config['resultVar']) : 'query_results';

        if (empty($table)) {
            error_log(__( 'Skaaa_Logic_DB_Query: Skipped because Table was not selected.', 'skaaa-logic-engine' ));
            return ['payload' => $payload, 'port' => 'main']; 
        }

        error_log(__( 'Skaaa_Logic_DB_Query: Start processing for Table {$table}. ', 'skaaa-logic-engine' ));

        $where_clauses = [];
        $query_values = [];

        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $cond) {
                if (empty($cond['column'])) continue;
                
                $col = sanitize_text_field($cond['column']);
                $op  = isset($cond['operator']) ? sanitize_text_field($cond['operator']) : '=';
                $val_expr = isset($cond['value']) ? $cond['value'] : '';

                // Xử lý giá trị qua SkaaaFX
                $resolved = \Skaaa\Logic\SkaaaFX\SkaaaFX_Engine::execute($val_expr, $payload);
                
                // Smart fallback tương tự DB_Action:
                // Nếu có lỗi parse hoặc tìm không ra biến (và chuỗi ko chứa [ ]), ta coi đó là literal / template
                if ( isset($resolved['error']) || ($resolved['last_val'] === null && strpos($val_expr, '[') === false) ) {
                    $final_val = preg_replace_callback('/\[([a-zA-Z0-9_$.]+)\]/', function($matches) use ($payload) {
                        $var_name = $matches[1];
                        $res = \Skaaa\Logic\SkaaaFX\SkaaaFX_Engine::execute($var_name, $payload);
                        return isset($res['last_val']) && !isset($res['error']) ? $res['last_val'] : '';
                    }, $val_expr);
                } else {
                    $final_val = $resolved['last_val'];
                }

                // Chặn SQL Injection với prepare
                $where_clauses[] = "{$col} {$op} %s";
                $query_values[] = $final_val;
            }
        }

        $sql = "SELECT * FROM {$table}";
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if (!empty($orderBy)) {
            $col_order = sanitize_text_field($orderBy);
            $dir = (strtoupper($orderDir) === 'ASC') ? 'ASC' : 'DESC';
            $sql .= " ORDER BY {$col_order} {$dir}";
        }

        if ($returnType === 'single') {
            $sql .= " LIMIT 1";
        } else {
            if (!empty($limit)) {
                $limit_resolved = \Skaaa\Logic\SkaaaFX\SkaaaFX_Engine::execute($limit, $payload);
                $final_limit = $limit_resolved['last_val'] ?? $limit;
                $sql .= " LIMIT " . absint($final_limit);
            }
        }

        if (!empty($query_values)) {
            $prepared_sql = $wpdb->prepare($sql, $query_values);
        } else {
            $prepared_sql = $sql;
        }

        if ($returnType === 'single') {
            $result = $wpdb->get_row($prepared_sql, ARRAY_A);
            error_log("Skaaa_Logic_DB_Query: SQL -> " . $prepared_sql);
            error_log(__( 'Skaaa_Logic_DB_Query: Get 1 record from {$table}.', 'skaaa-logic-engine' ));
        } else {
            $result = $wpdb->get_results($prepared_sql, ARRAY_A);
            error_log("Skaaa_Logic_DB_Query: SQL -> " . $prepared_sql);
            error_log("Skaaa_Logic_DB_Query: Lấy " . count($result) . " bản ghi từ {$table}.");
        }

        // Lưu vào payload
        // Chuẩn hoá resultVar (xoá 'payload.' nếu người dùng vô tình nhập vào)
        $clean_var = str_replace('payload.', '', $resultVar);
        if (empty($clean_var)) {
            $clean_var = 'query_results';
        }

        $payload[$clean_var] = $result;

        return ['payload' => $payload, 'port' => 'main'];
    }
}
