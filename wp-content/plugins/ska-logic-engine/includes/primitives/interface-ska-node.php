<?php
defined( 'ABSPATH' ) || exit;

/**
 * Giao diện Chuẩn (Strategy) cho tất cả Cục Node (Bao gồm Processor và Action)
 * Giúp cho hệ thống Node Engine UI nén thành dạng chuẩn một cổng In - một cổng Out.
 */
interface Ska_Logic_Node {
    /**
     * Dòng điện (Data) chạy qua Node.
     * @param array $payload Bộ dữ liệu đi vào (In).
     * @param array $config Cấu hình của Cục Node (Ghi từ Giao diện UI).
     * @return array Định dạng chuẩn: ['payload' => $new_data, 'port' => 'main' | 'error']
     */
    public function execute( $payload, $config );
}
