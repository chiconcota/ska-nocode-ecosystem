<?php
defined( 'ABSPATH' ) || exit;

class Ska_Workflow_Runner {
    
    /**
     * Ngưỡng ngắt mạch (Circuit Breaker)
     */
    private static $step_count = 0;
    private static $max_steps = 1000;

    /**
     * Vận hành Cỗ máy Băng Chuyền
     * 
     * @param array $payload Dữ liệu thô từ Lớp Vỏ dội vào (Input Form)
     * @param array|string $workflow_id_or_graph File cấu hình Workflow (JSON Graph sinh từ giao diện kéo thả)
     * @return array Dữ liệu chắt lọc (Output)
     */
    public static function execute( $payload, $workflow_id_or_graph ) {
        self::$step_count = 0; // Reset circuit breaker
        
        $workflow_id = is_string($workflow_id_or_graph) ? $workflow_id_or_graph : 'default';
        $graph = [];

        if ( is_array( $workflow_id_or_graph ) ) {
            $graph = $workflow_id_or_graph; 
        } else {
            $workflows = get_option('ska_logic_simple_workflows', []);
            if ( isset($workflows[$workflow_id]) && isset($workflows[$workflow_id]['graph']) ) {
                $graph = $workflows[$workflow_id]['graph'];
            }
        }

        if ( empty($graph) ) {
            error_log("Ska_Workflow_Runner: Graph rỗng hoặc không tìm thấy workflow_id = " . $workflow_id);
            return $payload; // Không làm gì cả
        }
        error_log("Ska_Workflow_Runner: Đã load graph cho " . $workflow_id . ". Nodes count: " . count($graph['nodes'] ?? []));

        // Đảm bảo có cấu trúc DAG hợp lệ
        if ( ! isset($graph['nodes']) || ! is_array($graph['nodes']) ) {
            return $payload; // Graph không hợp lệ
        }

        // Tìm các Node gốc (Trigger Nodes) - Node không có ai trỏ vào
        $trigger_node_id = self::find_trigger_node( $graph['nodes'] );
        if ( ! $trigger_node_id ) {
            return $payload; // Không có điểm vào
        }

        // Bắt đầu duyệt đồ thị
        self::traverse_graph( $graph, $trigger_node_id, $payload, $workflow_id, 0 );

        // Vì hệ thống DAG phân nhánh phức tạp, và trả về $payload gốc sau khi đã chạy đồng bộ
        return $payload;
    }

    private static function find_trigger_node( $nodes ) {
        // Tìm node đầu tiên không có input hoặc type = Trigger
        foreach ( $nodes as $node ) {
            $class_name = $node['class'] ?? '';
            $type_name  = $node['type'] ?? '';
            if ( strpos( strtolower($class_name), 'trigger' ) !== false || strpos(strtolower($type_name), 'trigger') !== false ) {
                return $node['id'];
            }
        }
        // Fallback lấy node đầu tiên
        return isset($nodes[0]['id']) ? $nodes[0]['id'] : null;
    }

    /**
     * Thuật toán duyệt DAG (Graph Traversal)
     * Vừa chạy vừa truyền Reference biến $payload
     */
    public static function traverse_graph( $graph, $current_node_id, &$payload, $workflow_id, $depth = 0 ) {
        self::$step_count++;
        
        if ( self::$step_count > self::$max_steps ) {
            error_log("Ska_Workflow_Runner: CIRCUIT BREAKER TRIGGERED - Max steps exceeded (" . self::$max_steps . ") tại node " . $current_node_id);
            return; // Ngắt mạch
        }

        if ( $depth > 50 ) {
            error_log("Ska_Workflow_Runner: CIRCUIT BREAKER TRIGGERED - Stack depth exceeded (50) tại node " . $current_node_id);
            return; // Tránh Stack Overflow
        }

        $nodes = $graph['nodes'] ?? [];
        $edges = $graph['edges'] ?? [];

        // Lấy thông tin node hiện tại
        $current_node = null;
        foreach ( $nodes as $n ) {
            if ( isset($n['id']) && $n['id'] === $current_node_id ) {
                $current_node = $n;
                break;
            }
        }

        if ( ! $current_node ) return;

        // Bỏ qua các Node con đã thuộc về Iterator (chúng được chạy nội bộ bởi JIT Pipeline)
        $parent_id = $current_node['parentId'] ?? ( $current_node['parentNode'] ?? '' );
        if ( ! empty( $parent_id ) ) {
            // Xác minh parent_id có thực sự là Iterator không (tránh lỗi rác React Flow cũ lưu lại parentNode ảo)
            $is_iterator_child = false;
            foreach ( $nodes as $p_node ) {
                if ( isset($p_node['id']) && $p_node['id'] === $parent_id ) {
                    if ( ($p_node['type'] ?? '') === 'IteratorNode' || strpos(strtolower($p_node['class'] ?? ''), 'iterator') !== false ) {
                        $is_iterator_child = true;
                    }
                    break;
                }
            }
            if ( $is_iterator_child ) {
                error_log( "Ska_Workflow_Runner: SKIP child node {$current_node_id} (parent={$parent_id})" );
                return;
            }
        }

        error_log( "Ska_Workflow_Runner: EXEC node {$current_node_id} | type=" . ($current_node['type'] ?? '?') . " | class=" . ($current_node['class'] ?? 'none') );

        $port = 'main'; // Cổng mặc định
        
        // Thực thi Logic Node (Ngoại trừ UI Node ko có class xử lý Backend)
        if ( isset( $current_node['class'] ) && class_exists( $current_node['class'] ) ) {
            $instance = new $current_node['class']();
            if ( $instance instanceof Ska_Logic_Node ) {
                if ( method_exists( $instance, 'set_graph_context' ) ) {
                    $instance->set_graph_context( $graph, $current_node_id, $workflow_id );
                }
                
                $node_config = $current_node['data'] ?? ($current_node['config'] ?? []);
                $result = $instance->execute( $payload, $node_config );
                if ( is_array($result) && isset($result['payload']) && isset($result['port']) ) {
                    $payload = $result['payload'];
                    $port    = $result['port'];
                } else {
                    $payload = $result; // Fallback nếu node chưa refactor chuẩn
                }
            }
        }

        // Tìm các Edge đi ra từ Node này, khớp với cổng $port
        foreach ( $edges as $edge ) {
            if ( isset($edge['source']) && $edge['source'] === $current_node_id ) {
                
                // Mặc định React Flow nối bằng `sourceHandle`
                $source_handle = $edge['sourceHandle'] ?? 'main';
                
                // Đi tiếp nếu trùng khớp cổng (main -> main, error -> error)
                if ( $source_handle === $port || empty($edge['sourceHandle']) ) {
                    
                    $target_node_id = $edge['target'];

                    // Cờ Async bật -> Tống vào Background Worker, không cản trở thread này
                    if ( !empty($edge['async']) && class_exists('Ska_Logic_Async_Worker') ) {
                        Ska_Logic_Async_Worker::dispatch( $workflow_id, $target_node_id, $payload );
                    } else {
                        // Gọi đệ quy duyệt tiếp nhánh đó
                        self::traverse_graph( $graph, $target_node_id, $payload, $workflow_id, $depth + 1 );
                    }
                }
            }
        }
    }
}
