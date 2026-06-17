# CHECKPOINT - PHẦN BÀN GIAO TIẾN ĐỘ
*Ngày cập nhật: 2026-06-15*

## 1. Trạng thái hiện tại (Status)
- **Git Branch**: `feature/workspace-redirect-fallback`
- **Công việc**: 
  1. Chuẩn hóa cấu trúc lưu trữ App Workspace: chuyển đổi hoàn toàn từ lưu trữ option `wp_options` (`ska_data_apps`) sang bảng phẳng hệ thống MySQL `wp_ska_data_sys_apps` để tối ưu hóa hiệu năng và bảo mật cấu trúc bảng.
  2. Triển khai cơ chế Redirect Fallback 2 cấp (Table Portal -> Workspace Settings) và tùy biến trang lỗi 403 (location/condition `403`) qua Ska Builder với trang 403 mặc định làm fallback tuyệt đẹp (sử dụng Tailwind, Outfit font, Glassmorphism).
  3. Sửa lỗi `ReferenceError: wp is not defined` và sự cố không đóng được sidebar popup menu khi click ra ngoài.
  4. Bump version của plugin `Ska Data Pro` lên `1.1.1` và `Ska No-Code Design` lên `1.1.0`.
- **Trạng thái**: 🟢 Done (Các lỗi phát sinh trong quá trình kiểm thử đã được sửa chữa triệt để, sẵn sàng cho User tự kiểm thử trực tiếp trên trình duyệt).

## 2. Các quyết định thiết kế đã thống nhất:
- **Flat Table App Workspace Storage**: Di trú dữ liệu lưu trữ Workspace của ứng dụng sang bảng phẳng MySQL `wp_ska_data_sys_apps` để đồng nhất với triết lý flat-table của Ska và cho phép cấu hình URL chuyển hướng an toàn.
- **Two-Tier Redirect Resolution**: Khi bị từ chối truy cập, hệ thống ưu tiên URL ở Portal Table. Nếu trống, fallback về URL ở Workspace. Nếu cả hai cùng trống, hiển thị trang 403.
- **Customizable 403 via Theme Builder**: Cho phép thiết kế trang 403 riêng biệt dưới dạng Theme Template có Location là `403` hoặc điều kiện `is_403`. Nếu không có, hiển thị trang fallback mặc định đẹp mắt.
- **Assets Enqueue Standard**: Loại bỏ việc nhúng script thủ công trong file view PHP, thay thế bằng cơ chế `wp_enqueue_script` chuẩn của WordPress cùng các dependencies bắt buộc (`wp-i18n`, `wp-util`) để tránh xung đột tải và đảm bảo các đối tượng toàn cục `wp` được khai báo đầy đủ.

## 3. Danh sách file thay đổi & tạo mới trong phiên:
- **Cập nhật mã nguồn (Ska Data Pro v1.1.1)**:
  - `[MODIFY]` `ska-data-pro/inc/core/class-app-manager.php` (Đúc bảng apps, viết lại CRUD apps).
  - `[MODIFY]` `ska-data-pro/inc/core/class-database-engine.php` (Bảo vệ bảng phẳng apps).
  - `[MODIFY]` `ska-data-pro/inc/admin/class-admin-ajax.php` (Xử lý AJAX URL redirect).
  - `[MODIFY]` `ska-data-pro/inc/admin/class-admin-menu.php` (Enqueue script `admin-datagrid.bundle.js` chuẩn WordPress).
  - `[MODIFY]` `ska-data-pro/inc/admin/views/manage.php` (Gỡ bỏ script nhúng thủ công).
  - `[MODIFY]` `ska-data-pro/inc/admin/views/parts/manage-modals.php` (Bổ sung input URL redirect).
  - `[MODIFY]` `ska-data-pro/inc/admin/views/parts/manage-sidebar.php` (Gắn class `ska-dropdown-menu` và đổi tên nút).
  - `[MODIFY]` `ska-data-pro/assets/js/src/index.js` (Thêm trình lắng nghe click đóng dropdown khi click ra ngoài).
  - `[MODIFY]` `ska-data-pro/assets/js/src/modules/modals.js` (Popup input value loading).
  - `[MODIFY]` `ska-data-pro/assets/js/src/modules/apps.js` (Gửi AJAX lưu URL redirect).
  - `[MODIFY]` `ska-data-pro/package.json` (Bump version to 1.1.1).
  - `[MODIFY]` `ska-data-pro/ska-data-pro.php` (Bump version to 1.1.1).
- **Cập nhật mã nguồn (Ska No-Code Design v1.1.0)**:
  - `[MODIFY]` `ska-no-code-design/inc/theme-builder/views/admin-panel.php` (Tab 403 và điều kiện `is_403`).
  - `[MODIFY]` `ska-no-code-design/inc/theme-builder/class-ska-app-router.php` (Kế thừa redirect cấp Workspace và default 403 fallback).
  - `[MODIFY]` `ska-no-code-design/inc/theme-builder/class-ska-virtual-wrapper.php` (Override 403 template & condition evaluation).
  - `[MODIFY]` `ska-no-code-design/ska-no-code-design.php` (Bump version to 1.1.0).
- **Cập nhật tài liệu hệ thống**:
  - `[NEW]` `.ska-ai/1-overview/project-managers/test-workspace-redirect-e2e.md` (Tài liệu hướng dẫn test E2E thủ công).
  - `[MODIFY]` `.ska-ai/1-overview/system_map.md` (Update logs & module version).
  - `[MODIFY]` `.ska-ai/2-memory/self-improve.md` (Cập nhật quy tắc MISTAKE-003 tránh lạm dụng browser).
  - `[MODIFY]` `.ska-ai/2-memory/decision-log.md` (Update technical decision).
  - `[MODIFY]` `.ska-ai/3-ecosystem/ska-data-pro/architecture.md` (Document flat table workspace & 403 routing).
  - `[MODIFY]` `.ska-ai/1-overview/project-managers/pm_workspace_storage.md` (Update project task status).
  - `[MODIFY]` `task.md` (Mark tasks as complete).

## 4. Gợi ý công việc cho phiên tiếp theo (QUAN TRỌNG)
- **Tiến hành kiểm thử thực tế từ trình duyệt**:
  1. Kích hoạt hai plugin để tự động khởi tạo bảng phẳng hệ thống `wp_ska_data_sys_apps`.
  2. Tạo/cấu hình thử một Workspace, thay đổi URL redirect trong Workspace settings (popup rename).
  3. Truy cập một Portal bị chặn quyền để:
     - Xem trang 403 mặc định (nếu không có URL redirect).
     - Kiểm tra redirect (nếu có URL redirect).
     - Tạo template 403 trong Builder, active nó và truy cập portal bị chặn -> xác nhận render giao diện tự thiết kế.
