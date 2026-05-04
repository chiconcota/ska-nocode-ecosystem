<?php
defined( 'ABSPATH' ) || exit;

class Ska_Logic_Iterator implements Ska_Logic_Node {

    private $graph = [];
    private $node_id = '';
    private $workflow_id = '';

    /**
     * Nhận Graph Context từ Workflow Runner
     */
    public function set_graph_context( $graph, $node_id, $workflow_id ) {
        $this->graph       = $graph;
        $this->node_id     = $node_id;
        $this->workflow_id = $workflow_id;
    }

    public function execute( $payload, $config ) {
        $array_source = $config['array_source'] ?? '';
        
        $items = [];
        if ( ! empty( $array_source ) ) {
            try {
                if ( strpos( $array_source, '{{' ) !== false ) {
                    $array_source = trim(str_replace(['{{', '}}'], '', $array_source));
                }
                $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $array_source, $payload );
                $resolved = $eval_result['last_val'] ?? null;
                
                // Nếu kết quả là string JSON, thử decode
                if ( is_string( $resolved ) ) {
                    $decoded = json_decode( $resolved, true );
                    if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
                        $resolved = $decoded;
                    }
                }
                $items = is_array( $resolved ) ? $resolved : [];
            } catch ( \Exception $e ) {
                error_log( 'Ska Logic Iterator: Lỗi khi evaluate array_source - ' . $e->getMessage() );
                $items = [];
            }
        }

        if ( ! is_array( $items ) || empty( $items ) ) {
            return [ 'payload' => $payload, 'port' => 'main' ]; // Cổng ra chính
        }

        // 1. Trích xuất Node Con
        $child_nodes = [];
        $nodes = $this->graph['nodes'] ?? [];
        foreach ( $nodes as $node ) {
            $parent = $node['parentId'] ?? ( $node['parentNode'] ?? '' );
            if ( $parent === $this->node_id ) {
                $child_nodes[] = $node;
            }
        }

        if ( empty( $child_nodes ) ) {
            return [ 'payload' => $payload, 'port' => 'main' ];
        }

        // 2. JIT Compilation: Biến DAG cục bộ thành Linear Pipeline
        $edges = $this->graph['edges'] ?? [];
        $pipeline = $this->build_linear_pipeline( $child_nodes, $edges );

        // 3. Thực thi Vòng Lặp trên RAM (Bỏ qua recursion)
        $total = count( $items );
        $current_index = 0;

        foreach ( $items as $key => $item ) {
            // Bơm biến ngữ cảnh lặp vào Payload
            $payload['$item']  = $item;
            $payload['$index'] = $key;
            $payload['$first'] = ( $current_index === 0 );
            $payload['$last']  = ( $current_index === $total - 1 );

            // Chạy tuyến tính qua từng node trong Pipeline
            foreach ( $pipeline as $node ) {
                if ( isset( $node['class'] ) && class_exists( $node['class'] ) ) {
                    $instance = new $node['class']();
                    if ( $instance instanceof Ska_Logic_Node ) {
                        // Kế thừa context cho node con (Hỗ trợ Iterator lồng nhau)
                        if ( method_exists( $instance, 'set_graph_context' ) ) {
                            $instance->set_graph_context( $this->graph, $node['id'], $this->workflow_id );
                        }
                        
                        $node_config = $node['data'] ?? ( $node['config'] ?? [] );
                        
                        $result = $instance->execute( $payload, $node_config );
                        if ( is_array( $result ) && isset( $result['payload'] ) ) {
                            $payload = $result['payload'];
                            // Chạy tuyến tính nên ta mặc kệ Output Port của node con.
                        } else {
                            $payload = $result; // Fallback
                        }
                    }
                }
            }

            $current_index++;
        }

        // 4. Dọn dẹp Context Variables để tránh ô nhiễm vùng nhớ sau Loop
        unset( $payload['$item'], $payload['$index'], $payload['$first'], $payload['$last'] );

        // Ra cổng main sau khi lặp xong
        return [ 'payload' => $payload, 'port' => 'main' ];
    }

    /**
     * Dịch đồ thị con (Sub-DAG) thành một mảng tuần tự (Topological Sort)
     */
    private function build_linear_pipeline( $child_nodes, $edges ) {
        $child_ids = array_column( $child_nodes, 'id' );
        
        $adj = [];
        $in_degree = [];
        foreach ( $child_ids as $id ) {
            $adj[ $id ] = [];
            $in_degree[ $id ] = 0;
        }
        
        foreach ( $edges as $edge ) {
            $source = $edge['source'] ?? '';
            $target = $edge['target'] ?? '';
            // Chỉ quan tâm các edge nối giữa các node con
            if ( in_array( $source, $child_ids ) && in_array( $target, $child_ids ) ) {
                $adj[ $source ][] = $target;
                $in_degree[ $target ]++;
            }
        }
        
        $queue = [];
        foreach ( $in_degree as $id => $degree ) {
            if ( $degree === 0 ) {
                $queue[] = $id;
            }
        }
        
        $sorted_ids = [];
        while ( ! empty( $queue ) ) {
            $u = array_shift( $queue );
            $sorted_ids[] = $u;
            foreach ( $adj[ $u ] as $v ) {
                $in_degree[ $v ]--;
                if ( $in_degree[ $v ] === 0 ) {
                    $queue[] = $v;
                }
            }
        }
        
        // Fallback: nếu có node rời rạc (hoặc chu trình ko sort dc hết)
        foreach ( $child_ids as $id ) {
            if ( ! in_array( $id, $sorted_ids ) ) {
                $sorted_ids[] = $id;
            }
        }
        
        $pipeline = [];
        $node_map = array_column( $child_nodes, null, 'id' );
        foreach ( $sorted_ids as $id ) {
            $pipeline[] = $node_map[ $id ];
        }
        
        return $pipeline;
    }
}
