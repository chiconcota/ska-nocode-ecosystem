<?php
defined( 'ABSPATH' ) || exit;

class Ska_Insert_Data_Action implements Ska_Logic_Node {
    
    public function execute( $payload, $config ) {
        $table_name = $config['table_name'] ?? '';
        
        // Nếu cục Node cắm thẳng xuống đất mà không cấu hình, bỏ qua!
        if ( empty( $table_name ) ) {
            return [ 'payload' => $payload, 'port' => 'error' ]; 
        }

        // Tích hợp Kế hoạch Explicit Mapping (Ánh xạ rõ ràng từ User)
        $mapped_payload = $payload; // Mặc định là chuyển qua nguyên vẹn để nhờ Auto Reverse Lookup (Fallback)
        
        if ( ! empty( $config['mappings'] ) && is_array( $config['mappings'] ) ) {
            $mapped_payload = []; // Thanh lọc sạch sẽ mảng, CHỈ lấy những gì có Map
            foreach ( $config['mappings'] as $target_key => $source_key ) {
                if ( empty( $target_key ) || empty( $source_key ) ) continue;
                
                $actual_source = $source_key;
                if ( ! isset( $payload[ $actual_source ] ) ) {
                    // Fallback PHP _POST conversion
                    $underscored = str_replace( ' ', '_', $source_key );
                    if ( isset( $payload[ $underscored ] ) ) {
                        $actual_source = $underscored;
                    }
                }
                
                if ( isset( $payload[ $actual_source ] ) ) {
                    $mapped_payload[ $target_key ] = $payload[ $actual_source ];
                }
            }
            
            // Chống lỗi mảng trống (bị map sai)
            if ( empty( $mapped_payload ) ) {
                return [ 'payload' => $payload, 'port' => 'error' ]; 
            }
        }

        // Giao tiếp với Lớp Kho (Ska Data Pro) BẰNG HOOK (Kẻ vạch ranh giới Microservice).
        // Ta không gọi $wpdb ở đây, không gọi Class DB nào từ Data Pro.
        // Data Pro sẽ nghe Hook này và tự tự gọi INSERT bên vương quốc của nó.
        $insert_result = apply_filters( 'ska_data_insert_record', false, $mapped_payload, $table_name );
        
        // Nhỉnh thêm tí mắm muối: Bơm Kết quả Insert ID này vào lại Dòng Điện,
        // lỡ như Node sau (Node Gửi Email, Node Redirect) cần xuất ID cho khách.
        $payload['_latest_insert'] = [
            'table_name' => $table_name,
            'result'     => $insert_result // true, false, hoac array data
        ];

        $port = ( empty($insert_result) || is_wp_error( $insert_result ) ) ? 'error' : 'main';

        return [ 'payload' => $payload, 'port' => $port ];
    }
}
