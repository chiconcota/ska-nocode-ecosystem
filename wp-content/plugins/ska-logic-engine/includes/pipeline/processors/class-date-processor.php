<?php
defined( 'ABSPATH' ) || exit;

class Ska_Date_Processor implements Ska_Logic_Node {
    
    /**
     * Dịch ngày tháng thành định dạng Database MySQL chuẩn 'Y-m-d H:i:s'
     */
    public function execute( $payload, $config ) {
        $source_key = $config['source_field'] ?? '';
        $target_key = $config['target_field'] ?? $source_key;
        $format     = $config['format'] ?? 'Y-m-d H:i:s'; // MySQL mặc định

        if ( ! empty( $source_key ) && ! empty( $payload[ $source_key ] ) ) {
            // Cố gắng rà soát tính hợp lệ của chuỗi Time người dùng gõ
            $timestamp = strtotime( $payload[ $source_key ] );
            if ( $timestamp !== false ) {
                $payload[ $target_key ] = wp_date( $format, $timestamp );
            }
        }

        return $payload; 
    }
}
