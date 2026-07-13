# Hướng dẫn Kiểm thử (Test Workflow) - Phase 5: App Portal Custom Redirect

Tài liệu này hướng dẫn người dùng cuối (End-user/Admin) cách thức kiểm thử tính năng **Tùy chỉnh trang chuyển hướng khi chưa đăng nhập (Unauthorized Custom Redirect)** của Skaaa App Builder.

## 1. Mục đích
Thay vì hệ thống mặc định ép người dùng (chưa có quyền truy cập hoặc chưa đăng nhập) văng về trang đăng nhập của WordPress (`wp-login.php`), bạn có thể cấu hình để hệ thống đá họ sang một trang Landing Page, Lead Page hoặc trang Đăng ký/Giới thiệu sản phẩm.

## 2. Kịch bản Kiểm thử (Test Workflow)

### Bước 1: Chuẩn bị trang Lead Page (Trang đích)
1. Đăng nhập vào WordPress Admin.
2. Tạo một trang Page mới hoặc lấy URL của một trang đã có sẵn (Ví dụ: `https://ten-mien-cua-ban.com/dang-ky-khoa-hoc` hoặc `/dang-ky-khoa-hoc`).
3. Đảm bảo trang này truy cập được ở chế độ Khách (Guest - chưa đăng nhập).

### Bước 2: Cấu hình App Portal Redirect
1. Truy cập vào menu **Skaaa Data Pro** trên thanh sidebar bên trái.
2. Chọn Data Model tương ứng (Ví dụ: `Courses`).
3. Click vào nút **App Portal Settings** (Biểu tượng cửa sổ / Settings) ở phía trên.
4. Bật công tắc **Enable App Portal**.
5. Nhập URL hoặc Slug của trang Lead Page vào ô **Custom Redirect URL** (Ví dụ: nhập `/dang-ky-khoa-hoc`).
6. Click **Update** để lưu cấu hình.

### Bước 3: Thiết lập quyền truy cập cho Portal
1. Truy cập vào **Skaaa Theme Builder**.
2. Mở Template List hoặc Detail của Portal vừa cấu hình (Ví dụ: `Portal: Courses - List`).
3. Ở khung bên phải (Inspector), mục **App Portal Visibility**, cấu hình như sau:
   - Check vào `[x] Cần Đăng nhập (Require Login)`.
   - Tick chọn Role được phép truy cập (Ví dụ: chỉ cho `Customer` hoặc `Student` xem).
4. Lưu Template.

### Bước 4: Kiểm tra thực tế (Đóng vai Khách)
1. Mở một trình duyệt ẩn danh (Incognito Mode) hoặc Đăng xuất khỏi tài khoản Admin hiện tại.
2. Truy cập vào đường link gốc của Portal (Ví dụ: `https://ten-mien-cua-ban.com/khoa-hoc`).
3. **Kết quả mong đợi:** 
   - Hệ thống sẽ chặn bạn lại (vì chưa đăng nhập/không có quyền).
   - Hệ thống tự động đá bạn về trang Lead Page bạn đã cấu hình ở Bước 2 (`/dang-ky-khoa-hoc`) thay vì bay về màn hình đăng nhập nhàm chán của WordPress.
   - Trên thanh địa chỉ URL sẽ xuất hiện biến `?redirect_to=` chứa link gốc để bạn có thể sử dụng cho logic sau khi họ đăng ký xong.

## 3. Khắc phục sự cố (Troubleshooting)
- **Vẫn bị đá về wp-login.php?** Vui lòng quay lại Bước 2 và bấm Update một lần nữa để hệ thống nhận diện cấu hình mới (do trước đó bộ Javascript chưa được biên dịch).
- **Lỗi 404 Not Found ở Portal gốc?** Vào WordPress Admin -> Settings -> Permalinks và bấm "Save Changes" 1 lần để hệ thống làm mới lại sơ đồ URL.

---
*Ghi chú kỹ thuật: Sự cố "trả về trang login" trước đó là do bộ lưu trữ JS chưa được build khiến database không nhận được url khi bạn bấm lưu. Vấn đề này đã được fix hoàn toàn. Bạn chỉ cần thao tác lại Bước 2 để ghi đè.*
