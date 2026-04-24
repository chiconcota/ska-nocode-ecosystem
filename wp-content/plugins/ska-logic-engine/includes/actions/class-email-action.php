<?php
defined( 'ABSPATH' ) || exit;

class Ska_Email_Action implements Ska_Logic_Node {
    
    public function execute( $payload, $config ) {
        $to      = $config['to'] ?? get_option( 'admin_email' );
        $subject = $config['subject'] ?? 'Thông báo từ Ska Logic Engine';
        $body    = $config['body'] ?? '';

        // Tự động tìm {{ten_truong}} và replace bằng biến tương ứng trong form
        foreach( $payload as $k => $v ) {
            if ( is_string($v) || is_numeric($v) ) {
                $subject = str_replace( '{{'.$k.'}}', $v, $subject );
                $body    = str_replace( '{{'.$k.'}}', $v, $body );
            }
        }

        if ( empty( trim($body) ) ) {
            $body = "Dữ liệu được bắn từ The Trinity (Ska-xi măng):\n\n" . print_r( $payload, true );
        }

        $sent = wp_mail( $to, $subject, $body );
        
        $payload['_action_response_email'] = $sent ? 'Email Sent' : 'Email Failed';

        $port = $sent ? 'main' : 'error';

        return [ 'payload' => $payload, 'port' => $port ];
    }
}
