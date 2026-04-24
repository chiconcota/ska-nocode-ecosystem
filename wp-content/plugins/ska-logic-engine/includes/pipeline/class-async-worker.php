<?php
defined( 'ABSPATH' ) || exit;

class Ska_Logic_Async_Worker {
    
    public function __construct() {
        // Lắng nghe hook từ Action Scheduler hoặc WP-Cron
        add_action( 'ska_logic_async_task', [ $this, 'process_async_task' ], 10, 3 );
    }

    /**
     * Đưa một tác vụ vào hàng đợi chạy nền (Async)
     * Ưu tiên Action Scheduler nếu có, fallback về WP-Cron
     */
    public static function dispatch( $workflow_id, $node_id, $payload ) {
        if ( function_exists( 'as_enqueue_async_action' ) ) {
            as_enqueue_async_action( 'ska_logic_async_task', [ $workflow_id, $node_id, $payload ] );
        } else {
            // Fallback: WP-Cron
            wp_schedule_single_event( time(), 'ska_logic_async_task', [ $workflow_id, $node_id, $payload ] );
        }
    }

    /**
     * Worker Job: Khởi động lại Graph Traversal từ cái Node bị treo
     */
    public function process_async_task( $workflow_id, $node_id, $payload ) {
        // Đọc lại graph từ DB
        $workflows = get_option('ska_logic_simple_workflows', []);
        
        $graph = [];
        if ( isset($workflows[$workflow_id]) && isset($workflows[$workflow_id]['graph']) ) {
            $graph = $workflows[$workflow_id]['graph'];
        }

        // Nếu workflow bị xóa giữa chừng thì drop.
        if ( empty($graph) || ! isset($graph['nodes']) || ! isset($graph['edges']) ) {
            return; 
        }

        // Đẩy tiếp băng chuyền
        Ska_Workflow_Runner::traverse_graph( $graph, $node_id, $payload, $workflow_id );
    }
}

new Ska_Logic_Async_Worker();
