<?php
defined( 'ABSPATH' ) || exit;

class Ska_Form_Receiver {
    
    public static function register_routes() {
        register_rest_route( 'ska-logic/v1', '/submit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'handle_submit' ],
            // @todo: Tạm mở cửa cho Khách tham quan Form nhập liệu. Cần Nonce-auth ở Phase sau!
            'permission_callback' => '__return_true', 
        ]);
    }

    public static function handle_submit( WP_REST_Request $request ) {
        $data = $request->get_params(); // Payload gốc Frontend đẩy sang
        $form_id = $data['ska_form_id'] ?? 'unknown_workflow';

        // 1. Phễu Khử Độc: Chặn XSS Injection cơ bản nhất 
        // Lớp Kho còn 1 bộ vệ sinh SQL nữa
        $clean_data = [];
        foreach ( $data as $k => $v ) {
            // Loại bỏ cái tham số route rác của REST API
            if ( $k === 'rest_route' ) continue;
            $clean_data[ sanitize_text_field( $k ) ] = is_string( $v ) ? sanitize_text_field( $v ) : $v;
        }

        // 2. Chuyển Lệnh Sang Băng Chuyền (The Runner)
        // Áp dụng Nguyên Tắc Vàng = Cấm gọi Class Runner trực tiếp!
        // Truyền Hook vào ko trung, ai hứng thì tính (Ở đây Logic Core đã đăng ký add_filter đón lõng r)
        $completed_payload = apply_filters( 'ska_logic_run_pipeline', $clean_data, $form_id );

        // 3. Phun Phản Hồi Về Cho Thẻ <Form> Ở Lớp Vỏ Giao Diện
        return rest_ensure_response([
            'success' => true,
            'message' => 'Nhiệm màu! Data đã lướt an toàn qua các bộ Node vào trúng Lớp Kho!',
            'data'    => $completed_payload
        ]);
    }
}
