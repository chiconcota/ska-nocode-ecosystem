<?php
defined( 'ABSPATH' ) || exit;

class Ska_Update_Data_Action implements Ska_Logic_Node {
    
    public function execute( $payload, $config ) {
        $table_name = $config['table_name'] ?? '';
        $condition_column = $config['condition_column'] ?? 'id';
        
        if ( empty( $table_name ) ) {
            return $payload; 
        }

        $mapped_payload = $payload;
        
        if ( ! empty( $config['mappings'] ) && is_array( $config['mappings'] ) ) {
            $mapped_payload = [];
            foreach ( $config['mappings'] as $target_key => $source_key ) {
                if ( empty( $target_key ) || empty( $source_key ) ) continue;
                
                $actual_source = $source_key;
                if ( ! isset( $payload[ $actual_source ] ) ) {
                    $underscored = str_replace( ' ', '_', $source_key );
                    if ( isset( $payload[ $underscored ] ) ) {
                        $actual_source = $underscored;
                    }
                }
                
                if ( isset( $payload[ $actual_source ] ) ) {
                    $mapped_payload[ $target_key ] = $payload[ $actual_source ];
                }
            }
            
            if ( empty( $mapped_payload ) ) {
                return $payload; 
            }
        }

        // Trích xuất ID điều kiện từ mapped payload hoặc original payload
        // Thường khi update, ta update dựa vào ID truyền vào form ẩn.
        $record_id = 0;

        // Cách 1: Tìm ID trong Mapped Payload
        if ( isset( $mapped_payload[ $condition_column ] ) ) {
            $record_id = intval( $mapped_payload[ $condition_column ] );
            unset( $mapped_payload[ $condition_column ] ); // Không update cột điều kiện
        } 
        // Cách 2: Tìm ID trong Payload gốc
        else if ( isset( $payload[ $condition_column ] ) ) {
            $record_id = intval( $payload[ $condition_column ] );
        }
        else if ( isset( $payload['id'] ) && $condition_column === 'id' ) {
             $record_id = intval( $payload['id'] );
        }

        if ( empty( $record_id ) ) {
            return $payload; // Không có điều kiện update
        }

        // Giao tiếp với Lớp Kho (Ska Data Pro) BẰNG HOOK (Kẻ vạch ranh giới Microservice).
        $update_result = apply_filters( 'ska_data_update_record', false, $mapped_payload, $table_name, [ $condition_column => $record_id ] );
        
        $payload['_latest_update'] = [
            'table_name' => $table_name,
            'record_id'  => $record_id,
            'result'     => $update_result
        ];

        return $payload;
    }
}
