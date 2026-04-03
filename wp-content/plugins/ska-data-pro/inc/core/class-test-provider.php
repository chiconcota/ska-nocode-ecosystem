<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

// Chống lỗi Fatal Error nếu Plugin Ska Builder Core chưa được bật
if ( ! interface_exists( '\Ska\Builder\Data\Provider' ) ) {
	return;
}

/**
 * Class Test_Provider
 * Adapter giả lập (Mock) để kiểm thử khả năng kết nối Nguồn Ngoại Lai
 * (Chứng minh kiến trúc Extensibility hoạt động trước khi làm WooCommerce thật)
 */
class Test_Provider implements \Ska\Builder\Data\Provider {

	/**
	 * Định danh Provider (Slug).
	 * VD: prefix `test:` trong tag `{{test:status}}`
	 */
	public function get_slug(): string {
		return 'test';
	}

	/**
	 * Trả về giá trị cụ thể từ DB dựa trên Key và Contex ID.
	 */
	public function get_field( string $key, int $id, array $context ) {
		
		// 1. NGHIỆP VỤ TEST VÒNG LẶP (Trả về Mảng ID để Data Engine lặp qua)
		// Cú pháp: {{#foreach test:mock_users}} ... {{/foreach}}
		if ( $key === 'mock_users' ) {
			return array( 101, 102, 103 ); 
		}

		// 2. NGHIỆP VỤ TRẢ DỮ LIỆU TÙY THEO ID BÊN TRONG VÒNG LẶP
		if ( $id === 101 ) {
			if ( $key === 'name' ) return 'Nguyễn Văn A (Fake)';
			if ( $key === 'role' ) return 'Quản Trị Viên';
		}
		if ( $id === 102 ) {
			if ( $key === 'name' ) return 'Lê Thị B (Fake)';
			if ( $key === 'role' ) return 'Khách Hàng (VIP)';
		}
		if ( $id === 103 ) {
			if ( $key === 'name' ) return 'Trần C (Fake)';
			if ( $key === 'role' ) return 'Nhân Viên';
		}

		// 3. NGHIỆP VỤ TEST DỮ LIỆU ĐƠN LẺ NGÀY BÊN NGOÀI
		// Cú pháp: {{test:status}}
		if ( $key === 'status' ) return '✅ KẾT NỐI ADAPTER NGOẠI LAI (EXTENSIBILITY) THÀNH CÔNG!';
		if ( $key === 'version' ) return 'v2.0 - Core Provider Engine';
		if ( $key === 'time' ) return current_time('mysql');

		return '';
	}
}
