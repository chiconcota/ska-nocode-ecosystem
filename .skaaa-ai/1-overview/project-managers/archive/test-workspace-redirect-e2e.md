# E2E Test Workflow: Workspace Flat Table Storage & 403 Redirect Fallback (v1.1.0)

> [!NOTE]
> Tài liệu này hướng dẫn chi tiết các bước kiểm thử thủ công E2E cho tính năng chuẩn hóa lưu trữ Workspace từ `wp_options` sang bảng phẳng MySQL `wp_skaaa_data_sys_apps` và cơ chế xử lý chuyển hướng 403 (Access Denied) tùy biến thông qua Theme Builder/Fallback mặc định.

## Objectives
1. **Flat Database Schema Verification:** Xác minh bảng phẳng hệ thống `wp_skaaa_data_sys_apps` được tạo và bảo vệ cấu trúc (cấm xóa/sửa cột) thông qua Data Dictionary.
2. **Workspace Settings UI & CRUD:** Xác minh giao diện Modal "Workspace Settings" (được cải tiến từ Rename Space) tiếp nhận, lưu trữ và đồng bộ hóa thành công trường `Unauthorized Redirect URL` xuống bảng phẳng thông qua AJAX.
3. **403 Access Denied Default Fallback:** Xác minh khi không có bất kỳ URL chuyển hướng nào được cấu hình, hệ thống sẽ render trang 403 mặc định tuyệt đẹp (giao diện Glassmorphism, font chữ Outfit, sử dụng Tailwind CSS và hiệu ứng animate-ping).
4. **Custom 403 Template Integration:** Xác minh người dùng có thể tạo, chỉnh sửa và kích hoạt (Active) một Template 403 thông qua Skaaa Builder để đè (override) trang 403 mặc định.
5. **Workspace-Level Redirect Fallback:** Xác minh cơ chế chuyển hướng kế thừa: Nếu portal không có URL chuyển hướng, hệ thống tự động lấy URL chuyển hướng cấp Workspace và đính kèm thêm tham số `?redirect_to=` trỏ về link portal gốc.

---

## 🧪 Test Cases

### Test Case 1: Schema Integrity & Workspace Settings Saving
**Status:** `[x] Done`

**Các bước thực hiện:**
1. Truy cập trang quản trị WordPress, click vào menu **Skaaa Data** ở thanh sidebar.x
2. Quan sát danh sách table xem bảng phẳng hệ thống `wp_skaaa_data_sys_apps` có xuất hiện trong danh mục ứng dụng "Site Management" (`skaaa_system`) hay không.
3. Click vào bảng `sys_apps`, thử click vào icon chỉnh sửa cấu trúc bảng (hoặc nút xóa bảng) để xác minh xem có bị chặn (Read-Only) bởi cơ chế bảo vệ bảng hệ thống hay không.
4. Mở sidebar quản lý Workspace, click chuột phải (hoặc click icon bánh răng/chỉnh sửa) tại **Default Workspace** hoặc một Workspace custom để mở Modal **Workspace Settings**.
5. Kiểm tra xem tiêu đề Modal có đổi thành "Workspace Settings" và xuất hiện ô nhập liệu **Unauthorized Redirect URL** hay không.
6. Điền URL test: `http://skaaa-core-builder.local/unauthorized-test-redirect` và bấm **Save Changes**.
7. Tải lại trang (F5), mở lại modal settings của Workspace đó để xác minh xem giá trị URL cũ có được lưu trữ và nạp lại chuẩn xác hay không.

**Kết quả mong đợi:**
- Bảng `wp_skaaa_data_sys_apps` được tạo tự động với các cột: `app_id`, `name`, `icon`, `unauthorized_redirect_url`.
- Cấu trúc bảng `sys_apps` bị khóa, không cho phép xóa bảng hoặc sửa cột qua UI.
- Modal Workspace Settings hiển thị đúng trường URL và lưu dữ liệu thành công xuống bảng phẳng `wp_skaaa_data_sys_apps` qua Ajax (không lưu trong `wp_options`).

---

### Test Case 2: 403 Access Denied Default Fallback
**Status:** `[x] Done`

**Các bước thực hiện:**
1. Vào **Skaaa Data** -> **Smart Objects**, chọn một table (ví dụ: `app_courses`).
2. Bật cấu hình **App Portal** (Activate App Portal), thiết lập quyền truy cập (Role) chỉ dành cho `Administrator` (không chọn `public`/`guest`).
3. Đảm bảo để trống ô nhập **Unauthorized Redirect URL** ở cả cấu hình Portal này lẫn Workspace chứa table này. Bấm lưu cấu hình.
4. Sao chép URL của Portal (ví dụ: `http://skaaa-core-builder.local/app_courses`).
5. Mở một trình duyệt ẩn danh (Incognito Window) nơi bạn chưa đăng nhập WordPress và truy cập vào URL Portal trên.

**Kết quả mong đợi:**
- Trình duyệt hiển thị trang **403 Forbidden / Access Denied** mặc định tuyệt đẹp.
- Giao diện có hiệu ứng phát sáng tròn màu xanh/tím ở background, thẻ container bo góc tối mờ (`backdrop-filter: blur`), icon khiên đỏ khóa nhấp nháy, font chữ Outfit sang trọng.
- Hiển thị đúng nút **Login to Access** (chuyển hướng sang trang đăng nhập WP kèm redirect_to) và nút **Back to [Site Name]**.
- Trình duyệt nhận HTTP status header `403 Forbidden` và cấm cache (`nocache_headers`).

---

### Test Case 3: Custom 403 Template Integration
**Status:** `[x] Done`

**Các bước thực hiện:**
1. Trở lại trang quản trị WP, truy cập menu **Skaaa Builder** (hoặc Theme Builder).
2. Xác nhận có sự xuất hiện của tab **403 Forbidden** (màu đỏ, icon lock) bên cạnh các tab Single, Archive, 404.
3. Click vào tab **403 Forbidden** và bấm **Create Template**. Chọn thiết kế Organism đơn giản (ví dụ: chỉ có dòng chữ to màu đỏ `"TRANG 403 TUY BIEN CUA TOI"`).
4. Thiết lập thuộc tính template ở trạng thái **Active** và lưu lại.
5. Sử dụng lại trình duyệt ẩn danh (chưa đăng nhập), truy cập vào URL Portal ở Test Case 2.

**Kết quả mong đợi:**
- Thay vì hiển thị trang 403 mặc định, hệ thống render đúng template 403 tùy biến bạn vừa tạo với đầy đủ các khối header, footer và nội dung organism đã thiết kế.
- Trang nhận đúng status code 403 và nocache headers.

---

### Test Case 4: Workspace-Level Redirect Fallback
**Status:** `[x] Done`

**Các bước thực hiện:**
1. Vào **Skaaa Data** -> Mở Modal **Workspace Settings** của Workspace chứa table Portal trên.
2. Điền URL chuyển hướng: `http://skaaa-core-builder.local/login-redirect-page` (đảm bảo để trống URL chuyển hướng ở cấu hình chi tiết của Portal Table). Bấm Save.
3. Sử dụng trình duyệt ẩn danh, truy cập URL Portal (`http://skaaa-core-builder.local/app_courses`).

**Kết quả mong đợi:**
- Hệ thống phát hiện portal không có URL chuyển hướng, tự động fallback lên lấy URL ở cấp Workspace.
- Trình duyệt tự động chuyển hướng (Redirect 302) về địa chỉ:
  `http://skaaa-core-builder.local/login-redirect-page?redirect_to=http%3A%2F%2Fskaaa-core-builder.local%2Fapp_courses`
- Cho phép người dùng đăng nhập xong có thể điều hướng ngược lại Portal đang xem dở.

---

## 🛠 Troubleshooting Guide

| Triệu chứng (Symptom) | Nguyên nhân khả dĩ (Cause) | Hướng khắc phục (Remedy) |
| :--- | :--- | :--- |
| **Không thấy bảng `wp_skaaa_data_sys_apps`** | Plugin Skaaa Data Pro chưa được reload để đúc CSDL. | Vô hiệu hóa (Deactivate) sau đó Kích hoạt lại (Activate) plugin **Skaaa Data Pro** để kích hoạt lại hook `register_activation_hook` và chạy hàm khởi tạo DB. |
| **Không lưu được URL Redirect trên Modal** | Lỗi bundle JS hoặc cache trình duyệt cũ. | Nhấn `Ctrl + F5` để xóa cache trình duyệt, hoặc kiểm tra xem file `assets/js/admin-datagrid.bundle.js` đã được build thành công từ Vite chưa. |
| **Truy cập portal bị chặn vẫn ra trang 404** | Rewrite rules chưa được làm sạch. | Vào **Settings** -> **Permalinks** trong WordPress, bấm **Save Changes** để buộc WP flush rewrite rules thủ công. |
