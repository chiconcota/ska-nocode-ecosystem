# CHECKPOINT: BÀN GIAO PHIÊN LÀM VIỆC (Phase 4.4)
@date: 2026-05-10
@status: Bắt đầu Milestone 1 của Theme Options & Design Tokens

## 1. Trạng thái hiện tại
- Đã khởi tạo File Quản lý Dự án cho Milestone mới tại: `.ska-ai/1-overview/project-managers/project_manager_theme_options.md`
- Đã lập `Implementation Plan` chi tiết cho việc xây dựng tính năng Theme Options.
- **Quyết định UX cốt lõi đã được trình lên User:** Thay vì làm trang cấu hình riêng, Hệ thống sẽ dùng giao diện Modal/Offcanvas (Alpine.js) móc trực tiếp vào Thẻ "Theme Options" của `Ska System Dashboard` (tại `ska_system_dashboard_extensions`).
- **Quyết định Dữ liệu cốt lõi:** Thống nhất dùng chuẩn Token API (`primary`, `secondary`, `surface`, `text`, `border`) ghi vào `tokens.json` làm Nguồn Sự Thật Duy Nhất (Single Source of Truth) thay thế cho Options API lề mề cũ.

## 2. Nhiệm vụ cho Agent phiên tiếp theo (Next Session)
1. Bắt đầu viết Code cho `class-theme-options-ui.php` để tạo Giao diện thẻ Extension trên Dashboard.
2. Xây dựng Form Modal bằng `Alpine.js` và `Tailwind` (trong thư mục `views/`) để người dùng có thể kéo màu, đổi Font.
3. Call API REST (`/ska-design/v1/tokens`) bằng JS fetch thuần hoặc Alpine để Submit dữ liệu từ Modal.
4. Điều chỉnh `class-tailwind-color-registry.php` để đọc data từ `tokens.json` thay vì gọi option cũ.

## 3. Các files cần chú ý (Context Load)
- `c:\Users\ADMIN\Local Sites\ska-core-builder\app\public\wp-content\plugins\ska-no-code-design\inc\ska-system-framework\includes\class-framework-ui.php` (Để đăng ký thẻ Extension)
- `c:\Users\ADMIN\Local Sites\ska-core-builder\app\public\wp-content\plugins\ska-no-code-design\inc\design-engine\class-design-tokens-api.php` (API lưu JSON)
- `c:\Users\ADMIN\Local Sites\ska-core-builder\app\public\wp-content\plugins\ska-no-code-design\inc\design-engine\class-tailwind-color-registry.php` (Trình Compiler đọc JSON)

---
*Ghi chú: Lịch sử quyết định đã được cập nhật vào `decision-log.md` và Trạng thái đã được ghi vào `system_map.md`.*
