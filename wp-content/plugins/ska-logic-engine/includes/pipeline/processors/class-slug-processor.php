<?php
defined( 'ABSPATH' ) || exit;

class Ska_Slug_Processor implements Ska_Logic_Node {
    
    /**
     * Biến chuỗi Tên (Title) thành Slug (vd: "Hello World" -> "hello-world")
     */
    public function execute( $payload, $config ) {
        $source_key = $config['source_field'] ?? '';
        $target_key = $config['target_field'] ?? 'slug';

        if ( ! empty( $source_key ) && ! empty( $payload[ $source_key ] ) ) {
            $payload[ $target_key ] = sanitize_title( $payload[ $source_key ] );
        }

        // Đẩy dòng Data ra ống để đi tới Node kế tiếp
        return $payload; 
    }
}
