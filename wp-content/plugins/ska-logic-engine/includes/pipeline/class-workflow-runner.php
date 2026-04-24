<?php
defined( 'ABSPATH' ) || exit;

class Ska_Workflow_Runner {
    
    /**
     * Vận hành Cỗ máy Băng Chuyền
     * 
     * @param array $payload Dữ liệu thô từ Lớp Vỏ dội vào (Input Form)
     * @param array|string $workflow_id_or_graph File cấu hình Workflow (JSON Graph sinh từ giao diện kéo thả)
     * @return array Dữ liệu chắt lọc (Output)
     */
    public static function execute( $payload, $workflow_id_or_graph ) {
        
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
            return $payload; // Không làm gì cả
        }

        // Tương thích ngược: Nếu là mảng tuần tự cũ (không có cấu trúc 'nodes', 'edges') -> Chạy Linear
        if ( ! isset($graph['nodes']) ) {
            return self::run_legacy_linear( $payload, $graph );
        }

        // Tìm các Node gốc (Trigger Nodes) - Node không có ai trỏ vào
        $trigger_node_id = self::find_trigger_node( $graph['nodes'] );
        if ( ! $trigger_node_id ) {
            return $payload; // Không có điểm vào
        }

        // Bắt đầu duyệt đồ thị
        self::traverse_graph( $graph, $trigger_node_id, $payload, $workflow_id );

        // Vì hệ thống DAG phân nhánh phức tạp, và trả về $payload gốc sau khi đã chạy đồng bộ
        return $payload;
    }

    /**
     * Chạy tương thích ngược cho dữ liệu cũ (Linear Array)
     */
    private static function run_legacy_linear( $payload, $graph ) {
        $current_payload = $payload;
        foreach ( $graph as $node ) {
            if ( isset( $node['class'] ) && class_exists( $node['class'] ) ) {
                $instance = new $node['class']();
                if ( $instance instanceof Ska_Logic_Node ) {
                    $result = $instance->execute( $current_payload, $node['config'] ?? [] );
                    $current_payload = (is_array($result) && isset($result['payload'])) ? $result['payload'] : $result;
                }
            }
        }
        return $current_payload;
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
    public static function traverse_graph( $graph, $current_node_id, &$payload, $workflow_id ) {
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

        $port = 'main'; // Cổng mặc định
        
        // Thực thi Logic Node (Ngoại trừ UI Node ko có class xử lý Backend)
        if ( isset( $current_node['class'] ) && class_exists( $current_node['class'] ) ) {
            $instance = new $current_node['class']();
            if ( $instance instanceof Ska_Logic_Node ) {
                $result = $instance->execute( $payload, $current_node['config'] ?? [] );
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
                        self::traverse_graph( $graph, $target_node_id, $payload, $workflow_id );
                    }
                }
            }
        }
    }
}
