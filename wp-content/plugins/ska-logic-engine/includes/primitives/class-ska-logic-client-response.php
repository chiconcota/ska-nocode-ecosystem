<?php
defined( 'ABSPATH' ) || exit;

/**
 * Node: Client Response
 * Trả về các lệnh điều khiển UI cho Giao diện Frontend (Ska Event Bus)
 * Hỗ trợ các hành động: Hiển thị Toast, Mở Modal, Chuyển trang (Redirect), Bắn Custom Event
 */
class Ska_Logic_Client_Response implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        // Lấy loại phản hồi (toast, redirect, open_modal, fire_event)
        $response_type = isset($config['response_type']) ? $config['response_type'] : 'toast';
        
        $event = [
            'type' => $response_type
        ];

        // Dựa vào type, tính toán các tham số cấu hình bằng SkaFX
        switch ($response_type) {
            case 'redirect':
                $event['url'] = $this->evaluate_template( $config['url'] ?? '', $payload );
                break;
                
            case 'open_modal':
                $event['modal_id'] = $this->evaluate_template( $config['modal_id'] ?? '', $payload );
                $event['modal_content'] = $this->evaluate_template( $config['modal_content'] ?? '', $payload );
                break;
                
            case 'fire_event':
                $event['event_name'] = $this->evaluate_template( $config['event_name'] ?? '', $payload );
                // Có thể map thêm các thuộc tính payload custom vào event nếu cần
                $event['payload'] = $payload; 
                break;
                
            case 'remove_row':
                $event['message'] = $this->evaluate_template( $config['message'] ?? __( 'Record deleted successfully!', 'ska-logic-engine' ), $payload );
                $event['toast_type'] = isset($config['toast_type']) ? $config['toast_type'] : 'success';
                break;
                
            case 'toast':
            default:
                $event['type'] = 'toast';
                $event['message'] = $this->evaluate_template( $config['message'] ?? __( 'Operation successful!', 'ska-logic-engine' ), $payload );
                $event['toast_type'] = isset($config['toast_type']) ? $config['toast_type'] : 'success';
                break;
        }

        // Khởi tạo mảng _ska_events nếu chưa có
        if ( ! isset($payload['_ska_events']) || ! is_array($payload['_ska_events']) ) {
            $payload['_ska_events'] = [];
        }
        
        // Nạp lệnh vào hàng đợi để Backend trả về mảng JSON
        $payload['_ska_events'][] = $event;

        return [
            'payload' => $payload,
            'port'    => 'main'
        ];
    }

    /**
     * Nạp các template string có chứa {{ ... }} thông qua SkaFX_Engine
     */
    private function evaluate_template( $template_string, $payload ) {
        if ( empty( $template_string ) ) {
            return '';
        }

        // Nếu chuỗi không chứa {{, trả về nguyên bản
        if ( strpos( $template_string, '{{' ) === false ) {
            return $template_string;
        }

        return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) use ($payload) {
            $expression = trim($matches[1]);
            try {
                $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expression, $payload );
                if ( isset($eval_result['last_val']) && !is_array($eval_result['last_val']) && !is_object($eval_result['last_val']) ) {
                    return $eval_result['last_val'];
                }
            } catch ( Exception $e ) {
                error_log('SkaFX Eval Error in Client Response Node: ' . $e->getMessage());
                return '';
            }
            return '';
        }, $template_string);
    }
}
