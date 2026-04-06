<?php
defined( 'ABSPATH' ) || exit;

class Ska_Insert_Data_Action implements Ska_Logic_Node {
    
    public function execute( $payload, $config ) {
        $table_name = $config['table_name'] ?? '';
        
        // Nếu cục Node cắm thẳng xuống đất mà không cấu hình, bỏ qua!
        if ( empty( $table_name ) ) {
            return $payload; 
        }

        // Giao tiếp với Lớp Kho (Ska Data Pro) BẰNG HOOK (Kẻ vạch ranh giới Microservice).
        // Ta không gọi $wpdb ở đây, không gọi Class DB nào từ Data Pro.
        // Data Pro sẽ nghe Hook này và tự tự gọi INSERT bên vương quốc của nó.
        $insert_result = apply_filters( 'ska_data_insert_record', false, $payload, $table_name );
        
        // Nhỉnh thêm tí mắm muối: Bơm Kết quả Insert ID này vào lại Dòng Điện,
        // lỡ như Node sau (Node Gửi Email, Node Redirect) cần xuất ID cho khách.
        $payload['_latest_insert'] = [
            'table_name' => $table_name,
            'result'     => $insert_result // true, false, hoac array data
        ];

        return $payload;
    }
}
