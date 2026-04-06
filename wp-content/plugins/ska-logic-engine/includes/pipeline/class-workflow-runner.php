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
        
        // ĐỌC THIẾT LẬP GRAPH JSON TỪ DATABASE
        $form_id = is_string($workflow_id_or_graph) ? $workflow_id_or_graph : 'default';
        $workflows = get_option('ska_logic_simple_workflows', []);
        
        $graph = [];
        if ( isset($workflows[$form_id]) && isset($workflows[$form_id]['graph']) ) {
            $graph = $workflows[$form_id]['graph'];
        }

        // Ưu tiên nạp Graph trực tiếp nếu truyền thẳng array qua filter
        if ( is_array( $workflow_id_or_graph ) ) {
            $graph = $workflow_id_or_graph; 
        }

        if ( empty($graph) ) {
            return $payload; // Không làm gì cả
        }

        // --- CỖ MÁY CHẠY ĐIỆN VẬN HÀNH --- //
        $current_payload = $payload;

        foreach ( $graph as $node ) {
            if ( isset( $node['class'] ) && class_exists( $node['class'] ) ) {
                $instance = new $node['class']();
                
                // Polymorphism: Miễn là mài cắm đúng Interface
                if ( $instance instanceof Ska_Logic_Node ) {
                    // Dòng điện truyền qua Cục Node -> Cho ra Payload mới tinh khiết hơn
                    $current_payload = $instance->execute( $current_payload, $node['config'] ?? [] );
                }
            }
        }

        // Chạy hết bằng chuyền cất Kho, Trả Payload đã có ID Insert về cho API vứt ra Frontend hiển thị (VD: "Mã đơn hàng của bạn là 12")
        return $current_payload; 
    }
}
