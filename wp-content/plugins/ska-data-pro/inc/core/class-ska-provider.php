<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

// Chống lỗi Fatal Error nếu Plugin Ska Builder Core chưa được bật
if ( ! interface_exists( '\Ska\Builder\Data\Provider' ) ) {
	return;
}

/**
 * Class Ska_Provider
 * Đóng vai trò Data Provider (Cầu nối) cho Cỗ Máy Nội Suy (Data Engine) của Ska Builder Core.
 * Xử lý các tag dạng {{ska:tên_cột}}.
 */
class Ska_Provider implements \Ska\Builder\Data\Provider {

	/**
	 * Định danh Provider (Slug).
	 * VD: prefix `ska:` trong tag `{{ska:product_price}}`
	 */
	public function get_slug(): string {
		return 'ska';
	}

	/**
	 * Trả về giá trị cụ thể từ DB dựa trên Key và Contex ID.
	 *
	 * @param string $key Tên cột (Ví dụ: `price`)
	 * @param int $id ID khóa chính của dòng dữ liệu hiện tại
	 * @param array $context Mảng bối cảnh chứa thông tin phụ (VD: $context['table'])
	 * @return mixed Giá trị hiển thị tại Frontend
	 */
	public function get_field( string $key, int $id, array $context ) {
		// Context bắt buộc phải truyền tên bảng (do Vòng Lặp Foreach thiết lập)
		if ( empty( $context['table'] ) || empty( $id ) ) {
			return '';
		}

		// Nhờ Cỗ máy Query_Builder lấy dữ liệu Dòng này
		$row = apply_filters( 'ska_data_get_row', null, $context['table'], $id );

		// Kiểm tra nếu lấy được dòng và cột này có tồn tại trong dữ liệu Data flat
		if ( is_array( $row ) && isset( $row[ $key ] ) ) {
			return wp_kses_post( $row[ $key ] ); // Tăng cường tính bảo mật khi In ra Frontend
		}

		return '';
	}
}
