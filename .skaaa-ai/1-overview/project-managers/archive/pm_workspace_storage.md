# TIẾN TRÌNH CHUẨN HÓA LƯU TRỮ WORKSPACE (FLAT TABLE MIGRATION)
@status: DONE | @phase: Milestone 1 | @last_update: 2026-06-15

Tài liệu này theo dõi tiến độ phát triển và di chuyển lưu trữ Không gian làm việc (App Workspace) từ option `wp_options` (`skaaa_data_apps`) sang bảng phẳng hệ thống MySQL `wp_skaaa_data_sys_apps` trực thuộc Skaaa Data Pro.

---

## 1. DANH SÁCH NHIỆM VỤ (TODOS)

### 🟢 A. Tầng Cơ Sở Dữ Liệu & Core (Skaaa Data Pro)
- [x] **Đúc bảng phẳng hệ thống mới:**
  - File: `wp-content/plugins/skaaa-data-pro/inc/core/class-app-manager.php`
  - Nhiệm vụ:
    - Bổ sung định nghĩa bảng `wp_skaaa_data_sys_apps` vào `maybe_create_system_tables()`.
    - Tự động chèn 2 Workspace mặc định (`uncategorized` và `skaaa_system`) nếu bảng trống.
- [x] **Tái cấu trúc các hàm CRUD:**
  - File: `wp-content/plugins/skaaa-data-pro/inc/core/class-app-manager.php`
  - Nhiệm vụ:
    - Viết lại `get_apps()` truy vấn bằng SQL `$wpdb->get_results` thay thế `get_option`.
    - Viết lại `create_app()`, `update_app()`, `drop_app()` thực thi `INSERT`, `UPDATE`, `DELETE` trực tiếp xuống bảng phẳng MySQL.

### 🟢 B. Giao diện Cấu hình & AJAX (Skaaa Data Pro)
- [x] **Bổ sung API cập nhật:**
  - File: `wp-content/plugins/skaaa-data-pro/inc/admin/class-admin-ajax.php`
  - Nhiệm vụ:
    - Cập nhật `data_update_app()` tiếp nhận `unauthorized_redirect_url` từ frontend.
- [x] **Nâng cấp giao diện Settings:**
  - File: `wp-content/plugins/skaaa-data-pro/inc/admin/views/parts/manage-modals.php`
  - Nhiệm vụ:
    - Thêm ô nhập `Unauthorized Redirect URL` (`skaaa-rename-app-redirect`) vào modal Workspace Settings.
- [x] **Đồng bộ hóa Javascript:**
  - File: `wp-content/plugins/skaaa-data-pro/assets/js/src/modules/modals.js` & `apps.js`
  - Nhiệm vụ:
    - Bơm dữ liệu URL chuyển hướng vào modal khi mở và đóng gói gửi đi trong Ajax.
- [x] **Biên dịch Frontend Bundle:**
  - Nhiệm vụ: Chạy lệnh `npm run build` để compile file `admin-datagrid.bundle.js`.

### 🟢 C. Kế thừa Chuyển hướng & Bảo mật (Skaaa No-Code Design)
- [x] **Tích hợp định tuyến:**
  - File: `wp-content/plugins/skaaa-no-code-design/inc/theme-builder/class-skaaa-app-router.php`
  - Nhiệm vụ:
    - Cập nhật `get_portal_config()` đính kèm trường `app_id`.
    - Cập nhật `enforce_security()` để kế thừa cấu hình chuyển hướng cấp Workspace từ bảng phẳng MySQL nếu portal table trống.

---

## 2. NHẬT KÝ TIẾN ĐỘ (PROGRESS LOG)
- **2026-06-15 - 🟢 Done:** Phân chia nhiệm vụ tách biệt, hoàn thành thiết kế, code, biên dịch bundle frontend và kiểm thử tính năng hoàn thành chuẩn hóa lưu trữ Workspace và App-level Redirect Fallback/Custom 403 page. Bàn giao để user tiến hành kiểm thử thực tế.
