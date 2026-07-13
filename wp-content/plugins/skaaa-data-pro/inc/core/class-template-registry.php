<?php
namespace Skaaa\Data\Core;

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
				'main_table' => 'skaaa_data_products', // URL Redirect sẽ dùng bảng này
				'tables' => array(
					'skaaa_data_products' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						stock int(11) NOT NULL DEFAULT 0,
						status varchar(50) NOT NULL DEFAULT 'publish',
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					",
					'skaaa_data_orders' => "
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
					'skaaa_data_products' => array(
						array( 'name' => __( 'Basic White T-shirt', 'skaaa-data-pro' ), 'price' => 150000, 'stock' => 50, 'status' => 'publish' ),
						array( 'name' => __( 'Dark Blue Jeans', 'skaaa-data-pro' ), 'price' => 350000, 'stock' => 20, 'status' => 'publish' ),
						array( 'name' => __( 'Sports Sneakers', 'skaaa-data-pro' ), 'price' => 650000, 'stock' => 15, 'status' => 'publish' ),
						array( 'name' => __( 'Waterproof Travel Backpack', 'skaaa-data-pro' ), 'price' => 450000, 'stock' => 5, 'status' => 'draft' ),
						array( 'name' => __( 'Black Cap', 'skaaa-data-pro' ), 'price' => 99000, 'stock' => 100, 'status' => 'publish' ),
					),
					'skaaa_data_orders' => array(
						array( 'customer_name' => __( 'Nguyen Van A', 'skaaa-data-pro' ), 'total_amount' => 500000, 'status' => 'completed', 'payment_method' => 'banking' ),
						array( 'customer_name' => __( 'Tran Thi B', 'skaaa-data-pro' ), 'total_amount' => 150000, 'status' => 'processing', 'payment_method' => 'cod' ),
						array( 'customer_name' => __( 'Le Huu C', 'skaaa-data-pro' ), 'total_amount' => 650000, 'status' => 'pending', 'payment_method' => 'cod' ),
					)
				)
			),

			// 2. NGÀNH HÀNG LMS (HỌC TRỰC TUYẾN)
			'lms' => array(
				'main_table' => 'skaaa_data_courses',
				'tables' => array(
					'skaaa_data_courses' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						title varchar(255) NOT NULL,
						instructor varchar(200) NOT NULL,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						level varchar(50) NOT NULL DEFAULT 'beginner',
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					",
					'skaaa_data_lessons' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						course_id bigint(20) NOT NULL,
						title varchar(255) NOT NULL,
						duration_minutes int(11) NOT NULL DEFAULT 0,
						video_url varchar(255) NOT NULL DEFAULT '',
						PRIMARY KEY  (id)
					",
					'skaaa_data_students' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						email varchar(100) NOT NULL,
						enrolled_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array(
					'skaaa_data_courses' => array(
						array( 'title' => __( 'Basic to Advanced HTML/CSS', 'skaaa-data-pro' ), 'instructor' => 'Skaaa Academy', 'price' => 0, 'level' => 'beginner' ),
						array( 'title' => __( 'JavaScript Programming in Action', 'skaaa-data-pro' ), 'instructor' => 'Skaaa Academy', 'price' => 500000, 'level' => 'intermediate' ),
						array( 'title' => 'ReactJS & NextJS Masterclass', 'instructor' => 'Alex Nguyen', 'price' => 1200000, 'level' => 'advanced' ),
					),
					'skaaa_data_lessons' => array(
						array( 'course_id' => 1, 'title' => __( 'Lesson 1: HTML structure', 'skaaa-data-pro' ), 'duration_minutes' => 15, 'video_url' => 'https://youtube.com/...' ),
						array( 'course_id' => 1, 'title' => __( 'Lesson 2: CSS Flexbox', 'skaaa-data-pro' ), 'duration_minutes' => 25, 'video_url' => 'https://youtube.com/...' ),
						array( 'course_id' => 2, 'title' => __( 'Lesson 1: JS Variables and Loops', 'skaaa-data-pro' ), 'duration_minutes' => 30, 'video_url' => 'https://youtube.com/...' ),
					),
					'skaaa_data_students' => array(
						array( 'name' => __( 'Pham Van Hoc', 'skaaa-data-pro' ), 'email' => 'hocpv@example.com' ),
						array( 'name' => __( 'Dinh Thi Gioi', 'skaaa-data-pro' ), 'email' => 'gioidt@example.com' ),
					)
				)
			),

			// 3. NGÀNH HÀNG BOOKING (ĐẶT LỊCH)
			'booking' => array(
				'main_table' => 'skaaa_data_services',
				'tables' => array(
					'skaaa_data_services' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						service_name varchar(255) NOT NULL,
						duration_minutes int(11) NOT NULL DEFAULT 30,
						price decimal(10,2) NOT NULL DEFAULT 0.00,
						status varchar(50) NOT NULL DEFAULT 'active',
						PRIMARY KEY  (id)
					",
					'skaaa_data_appointments' => "
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
					'skaaa_data_services' => array(
						array( 'service_name' => __( 'Handsome Men\'s Haircut', 'skaaa-data-pro' ), 'duration_minutes' => 30, 'price' => 80000, 'status' => 'active' ),
						array( 'service_name' => __( 'Korean Hair Perm', 'skaaa-data-pro' ), 'duration_minutes' => 120, 'price' => 350000, 'status' => 'active' ),
						array( 'service_name' => __( 'Smoky Hair Dye', 'skaaa-data-pro' ), 'duration_minutes' => 90, 'price' => 450000, 'status' => 'active' ),
					),
					'skaaa_data_appointments' => array(
						array( 'service_id' => 1, 'client_name' => __( 'Mr. Tien', 'skaaa-data-pro' ), 'client_phone' => '0912345678', 'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 day 09:00:00')), 'status' => 'booked' ),
						array( 'service_id' => 2, 'client_name' => __( 'Long friend', 'skaaa-data-pro' ), 'client_phone' => '0987654321', 'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 day 14:30:00')), 'status' => 'booked' ),
					)
				)
			),

			// 4. BỆNH VIỆN / PHÒNG KHÁM (ONLINE HOSPITAL)
			'hospital' => array(
				'main_table' => 'skaaa_data_doctors',
				'tables' => array(
					'skaaa_data_doctors' => "
						id bigint(20) NOT NULL AUTO_INCREMENT,
						doctor_name varchar(255) NOT NULL,
						avatar varchar(500) DEFAULT '',
						specialty varchar(255) DEFAULT '',
						experience varchar(255) DEFAULT '',
						qualifications varchar(255) DEFAULT '',
						city varchar(255) DEFAULT '',
						clinic_name varchar(255) DEFAULT '',
						rating varchar(50) DEFAULT '',
						patient_count varchar(100) DEFAULT '',
						consultation_fee decimal(10,2) NOT NULL DEFAULT 0.00,
						availability_text varchar(255) DEFAULT '',
						has_guarantee tinyint(1) DEFAULT 0,
						created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
						PRIMARY KEY  (id)
					"
				),
				'dummy_data' => array(
					'skaaa_data_doctors' => array(
						array(
							'doctor_name' => 'Dr. Shesham Srinidhi',
							'avatar' => 'https://via.placeholder.com/150',
							'specialty' => 'General Practitioner',
							'experience' => '5 YEARS',
							'qualifications' => 'MD(PHYSICIAN)',
							'city' => 'Hyderabad',
							'clinic_name' => 'Apollo 24|7 Clinic, Hyderabad',
							'rating' => '86%',
							'patient_count' => '175+ Patients',
							'consultation_fee' => 660.00,
							'availability_text' => 'Available in 1 minutes',
							'has_guarantee' => 1
						),
						array(
							'doctor_name' => 'Dr. Ly Tat Thanh',
							'avatar' => 'https://via.placeholder.com/150',
							'specialty' => 'Cardiologist',
							'experience' => '12 YEARS',
							'qualifications' => 'MD, Ph.D',
							'city' => 'Hanoi',
							'clinic_name' => 'Skaaa Heart Institute',
							'rating' => '99%',
							'patient_count' => '2000+ Patients',
							'consultation_fee' => 1500.00,
							'availability_text' => 'Available Tomorrow',
							'has_guarantee' => 1
						)
					)
				)
			),

			// 5. CUSTOM (BẢNG TRỐNG)
			'custom' => array(
				'main_table' => 'skaaa_data_custom',
				'tables' => array(
					'skaaa_data_custom' => "
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
