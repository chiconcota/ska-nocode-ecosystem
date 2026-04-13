# SKA SYSTEM FRAMEWORK (Quản Trị Trung Tâm)
@module: ska-system-framework | @version: 2.0.0

## 1. MỤC ĐÍCH (PURPOSE)
- Đóng vai trò là **Shared Drop-in Framework**, không phải là một plugin độc lập. File sẽ tự kích hoạt dựa trên phiên bản mới nhất nếu nhiều plugin nội bộ (Ska Builder Core, Data Pro, Logic Engine) cùng lồng ghép nó.
- Chế độ **Cửa sổ tập trung**: Đưa tất cả Setting Forms và Công cụ AI vào một Trang duy nhất tại WP-Admin (`?page=ska-system-dashboard`).
- Loại bỏ tính phụ thuộc rườm rà. Nếu khách chỉ cài `Ska Data Pro`, hệ thống tự sinh trang Dashboard. Nếu cài thêm `Ska Logic Engine`, các Tab tự động bổ sung.

## 2. GIAO TIẾP (INTERFACE/HOOKS)
### Các Điểm Neo Mở Rộng Dành Cho Plugins Khác (Exposed Hooks):
Sự mở rộng cấu trúc hiển thị trang Dashboard thông qua 3 Action Hooks cốt lõi:
- **`ska_system_dashboard_modules`**: Vị trí Hook dành cho việc thả các Card thông tin module (Vd: Khối cài đặt Data Pro, Logic Engine).
- **`ska_system_dashboard_settings`**: Vị trí Hook để chèn thêm các Cột Form Cài Đặt mở rộng vào Tab "Cài Đặt Hệ Thống".
- **`ska_system_dashboard_extensions`**: Vị trí Hook tại khu đáy trang dành cho các Tiện ích mở rộng. (Ví dụ: Ứng dụng Ska AI Architect cấu hình qua đường dẫn thẻ cài đặt ở đây).

- **Events:**
  - `admin_init` -> Nhúng lệnh bắt tín hiệu POST Save Setting (Kiểm tra `wp_verify_nonce( $_POST['ska_system_nonce'] )` và check user quyền `manage_options`).

## 3. CƠ CHẾ DỮ LIỆU ĐỘT PHÁ (Dev Mode)
- Module cập nhật và quản lý khoá option **`ska_system_dev_mode`**. Khi `Dev Mode` có giá trị là `1` (ON), lõi Engine (`Ska_Dynamic_Content` bên Logic Engine) sẽ tự động bật luồng bắt lỗi Exception và quăng thẳng Badges Đỏ lên Frontend nếu Code có vấn đề (Vd: Trùng Tên Data Table, Trích xuất lỗi).
- Khi OFF, hệ thống Fail Silent ẩn lỗi, an toàn tuyệt đối cho người lướt Web.

## 4. TỪ BỎ (DEPRECATIONS)
- Các thiết lập `Tạo AI Blueprint` trực tiếp từ Nút Header và chức năng `Import JSON Blueprint` đã chính thức bị Trì Hoãn khỏi giao diện chính theo định dạng Phase MVP. (Blueprint JSON sẽ được đưa vào Milestone 4 do rủi ro kiến trúc re-mapping dữ liệu khá phức tạp).

## 5. RÀNG BUỘC BẢO MẬT (CONSTRAINTS)
- Mọi Form lưu cấu hình bắt buộc chèn qua `$nonce = wp_create_nonce( 'ska_system_nonce_action' );`.
- Chỉ Administrator (quyền `manage_options`) mới được phép truy cập Dashboard Framework.
