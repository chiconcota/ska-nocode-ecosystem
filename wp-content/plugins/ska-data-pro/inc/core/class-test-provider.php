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
			if ( $key === 'name' ) return __( 'Nguyen Van A (Fake)', 'ska-data-pro' );
			if ( $key === 'role' ) return __( 'Administrator', 'ska-data-pro' );
		}
		if ( $id === 102 ) {
			if ( $key === 'name' ) return __( 'Le Thi B (Fake)', 'ska-data-pro' );
			if ( $key === 'role' ) return __( 'Customers (VIP)', 'ska-data-pro' );
		}
		if ( $id === 103 ) {
			if ( $key === 'name' ) return __( 'Tran C (Fake)', 'ska-data-pro' );
			if ( $key === 'role' ) return __( 'Staff', 'ska-data-pro' );
		}

		// 3. NGHIỆP VỤ TEST DỮ LIỆU ĐƠN LẺ NGÀY BÊN NGOÀI
		// Cú pháp: {{test:status}}
		if ( $key === 'status' ) return __( '✅ CONNECTING EXTERNAL ADAPTER (EXTENSIBILITY) SUCCESSFULLY!', 'ska-data-pro' );
		if ( $key === 'version' ) return 'v2.0 - Core Provider Engine';
		if ( $key === 'time' ) return current_time('mysql');

		return '';
	}
}
