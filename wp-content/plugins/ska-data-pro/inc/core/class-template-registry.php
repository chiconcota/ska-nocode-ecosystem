<?php
namespace Ska\Data\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Class Template_Registry
 * Chứa cấu trúc Schema phẳng (Flat Tables) và Dữ liệu mẫu (Dummy Data) cho từng nhóm dự án.
 */
class Template_Registry {

	/**
	 * Lấy thông tin cấu trúc Schema của một Template cụ thể.
	 *
	 * @param string $template_id Tên template (ecommerce, lms, booking, custom).
	 * @return array|false Mảng cấu trúc chứa tables, data, main_table hoặc false nếu không tồn tại.
	 */
	public static function get_template( $template_id ) {
		$templates = self::get_all_templates();
		
		if ( isset( $templates[ $template_id ] ) ) {
			return $templates[ $template_id ];
		}
		
		return false;
	}

	/**
	 * Trả về toàn bộ danh sách Templates.
	 * Chú ý quan trọng với dbDelta: Phải có đúng 2 dấu cách sau chữ PRIMARY KEY  (id)
	 *
	 * @return array
	 */
	public static function get_all_templates() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		return array(
			// 1. NGÀNH HÀNG E-COMMERCE
			'ecommerce' => array(
				'main_table' => 'ska_data_products', // URL Redirect sẽ dùng bảng này
				'tables' => array(
					'ska_data_products' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						stock int(11) NOT NULL DEFAULT 0,
						status varchar(50) NOT NULL DEFAULT 'publish',
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					",
					'ska_data_orders' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						customer_name varchar(255) NOT NULL,
						total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
						status varchar(50) NOT NULL DEFAULT 'pending',
						payment_method varchar(50) NOT NULL DEFAULT 'cod',
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array(
					'ska_data_products' => array(
						array( 'name' => 'Áo Thun Basic Trắng', 'price' => 150000, 'stock' => 50, 'status' => 'publish' ),
						array( 'name' => 'Quần Jean Xanh Đậm', 'price' => 350000, 'stock' => 20, 'status' => 'publish' ),
						array( 'name' => 'Giày Sneaker Thể Thao', 'price' => 650000, 'stock' => 15, 'status' => 'publish' ),
						array( 'name' => 'Balo Du Lịch Chống Nước', 'price' => 450000, 'stock' => 5, 'status' => 'draft' ),
						array( 'name' => 'Mũ Lưỡi Trai Đen', 'price' => 99000, 'stock' => 100, 'status' => 'publish' ),
					),
					'ska_data_orders' => array(
						array( 'customer_name' => 'Nguyễn Văn A', 'total_amount' => 500000, 'status' => 'completed', 'payment_method' => 'banking' ),
						array( 'customer_name' => 'Trần Thị B', 'total_amount' => 150000, 'status' => 'processing', 'payment_method' => 'cod' ),
						array( 'customer_name' => 'Lê Hữu C', 'total_amount' => 650000, 'status' => 'pending', 'payment_method' => 'cod' ),
					)
				)
			),

			// 2. NGÀNH HÀNG LMS (HỌC TRỰC TUYẾN)
			'lms' => array(
				'main_table' => 'ska_data_courses',
				'tables' => array(
					'ska_data_courses' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
						instructor varchar(200) NOT NULL,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						level varchar(50) NOT NULL DEFAULT 'beginner',
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					",
					'ska_data_lessons' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						course_id bigint(20) NOT NULL,
						title varchar(255) NOT NULL,
						duration_minutes int(11) NOT NULL DEFAULT 0,
						video_url varchar(255) NOT NULL DEFAULT '',
						PRIMARY KEY  (id)
					",
					'ska_data_students' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						email varchar(100) NOT NULL,
						enrolled_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array(
					'ska_data_courses' => array(
						array( 'title' => 'HTML/CSS Cơ Bản Đến Nâng Cao', 'instructor' => 'Ska Academy', 'price' => 0, 'level' => 'beginner' ),
						array( 'title' => 'Lập Trình JavaScript Thực Chiến', 'instructor' => 'Ska Academy', 'price' => 500000, 'level' => 'intermediate' ),
						array( 'title' => 'ReactJS & NextJS Masterclass', 'instructor' => 'Alex Nguyen', 'price' => 1200000, 'level' => 'advanced' ),
					),
					'ska_data_lessons' => array(
						array( 'course_id' => 1, 'title' => 'Bài 1: Cấu trúc HTML', 'duration_minutes' => 15, 'video_url' => 'https://youtube.com/...' ),
						array( 'course_id' => 1, 'title' => 'Bài 2: CSS Flexbox', 'duration_minutes' => 25, 'video_url' => 'https://youtube.com/...' ),
						array( 'course_id' => 2, 'title' => 'Bài 1: Biến và Vòng lặp JS', 'duration_minutes' => 30, 'video_url' => 'https://youtube.com/...' ),
					),
					'ska_data_students' => array(
						array( 'name' => 'Phạm Văn Học', 'email' => 'hocpv@example.com' ),
						array( 'name' => 'Đinh Thị Giỏi', 'email' => 'gioidt@example.com' ),
					)
				)
			),

			// 3. NGÀNH HÀNG BOOKING (ĐẶT LỊCH)
			'booking' => array(
				'main_table' => 'ska_data_services',
				'tables' => array(
					'ska_data_services' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						service_name varchar(255) NOT NULL,
						duration_minutes int(11) NOT NULL DEFAULT 30,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						status varchar(50) NOT NULL DEFAULT 'active',
						PRIMARY KEY  (id)
					",
					'ska_data_appointments' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						service_id bigint(20) NOT NULL,
						client_name varchar(255) NOT NULL,
						client_phone varchar(50) NOT NULL,
						appointment_date datetime NOT NULL,
						status varchar(50) NOT NULL DEFAULT 'booked',
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array(
					'ska_data_services' => array(
						array( 'service_name' => 'Cắt Tóc Nam Chuẩn Điển Trai', 'duration_minutes' => 30, 'price' => 80000, 'status' => 'active' ),
						array( 'service_name' => 'Uốn Tóc Hàn Quốc', 'duration_minutes' => 120, 'price' => 350000, 'status' => 'active' ),
						array( 'service_name' => 'Nhuộm Tóc Màu Khói', 'duration_minutes' => 90, 'price' => 450000, 'status' => 'active' ),
					),
					'ska_data_appointments' => array(
						array( 'service_id' => 1, 'client_name' => 'Anh Tiến', 'client_phone' => '0912345678', 'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 day 09:00:00')), 'status' => 'booked' ),
						array( 'service_id' => 2, 'client_name' => 'Bạn Long', 'client_phone' => '0987654321', 'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 day 14:30:00')), 'status' => 'booked' ),
					)
				)
			),

			// 4. CUSTOM (BẢNG TRỐNG)
			'custom' => array(
				'main_table' => 'ska_data_custom',
				'tables' => array(
					'ska_data_custom' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array() // Bảng trống không có dữ liệu mẫu
			)
		);
	}
}
