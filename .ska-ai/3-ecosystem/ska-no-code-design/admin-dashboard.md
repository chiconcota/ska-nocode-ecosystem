# ADMIN DASHBOARD MODULE
@module: admin-dashboard | @version: 1.0.0

## 1. Mục đích (Purpose)
- Cung cấp giao diện quản trị (Admin Settings) cho plugin Ska Core Builder.
- Cho phép người dùng cấu hình các tuỳ chọn toàn cục (Global Settings), License, và theo dõi hệ thống.
- Làm môi trường hiển thị tổng quan về các module đang hoạt động.

## 2. Giao tiếp (Interface/Hooks)
- **Input:**
  - Hook: `admin_menu` -> Đăng ký menu page.
  - Hook: `admin_enqueue_scripts` -> Nạp CSS/JS (Tailwind scope) cho trang admin.
- **Output:**
  - Giao diện Admin với chuẩn Tailwind CSS scope (`.ska-builder`).
- **Events (Do Action):**
  - `ska_admin_dashboard_loaded` -> Emit khi trang admin hiển thị.

## 3. Cấu trúc thư mục (Structure)
- `admin-dashboard.php`: Init file.
- `class-admin-menu.php`: Class xử lý khai báo Menu và Enqueue.
- `views/dashboard.php`: File template HTML.

## 4. Ràng buộc bảo mật (Constraints)
- Chỉ User có quyền `manage_options` mới được truy cập.
- Mọi dữ liệu in ra template phải được escaped (esc_html, etc...).
