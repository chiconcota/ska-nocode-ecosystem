# Workflow Kiểm thử E2E: Ska Dedicated Pages & App Portals (Milestone 6)

> [!NOTE]
> Tài liệu này cung cấp quy trình và kịch bản chuẩn để kiểm thử chất lượng (QA) hệ thống Dedicated Pages (thay thế cho SPA cũ), tập trung vào định tuyến động (Dynamic Routing), phân quyền (Auth Middleware), và liên kết dữ liệu quan hệ (Relations & Reverse Lookup).

## Mục tiêu Kiểm thử (Objectives)
1. **Dynamic Routing & Virtual Folders:** Đảm bảo hệ thống bắt đúng URL theo `frontend_slug` mà không làm loạn Rewrite Rules của WordPress. Giao diện Theme Builder quản lý được theo Folder ảo.
2. **Dynamic Linking & Rollup:** Đảm bảo `{{relation.url}}` nội suy chính xác link của Record cha, và `{url:id}` lấy được đúng ID để query List danh sách con.
3. **Auth & Security Middleware:** Đảm bảo chặn đứng các truy cập không hợp lệ ngay tại `parse_request`, chuyển hướng đúng luồng đăng nhập hoặc từ chối truy cập, và hỗ trợ hoàn hảo cấu hình Public Portal.

---

## 🧪 Các Kịch Bản Kiểm Thử (Test Cases)

### Test Case 1: Quản lý Template theo Virtual Folder (App Categorization)
**Trạng thái:** `[x] Đã hoàn thành`

**Bước thực hiện:**
1. Truy cập WP Admin -> Ska Builder -> Theme Builder.
2. Nhấn "Tạo Template Mới", đặt tên "LMS Dashboard". Ở trường **Thuộc App nào?**, nhập "Hệ thống LMS" (hoặc chọn nếu đã có).
3. Lưu lại và quay về danh sách Template.
4. Chọn bộ lọc App Category ở UI, lọc theo "Hệ thống LMS".

**Kỳ vọng:**
- Bảng Theme Builder chỉ hiển thị các Template thuộc thư mục "Hệ thống LMS".
- Cấu trúc thư mục được lưu gọn trong Option `ska_theme_builder_folders` (dưới dạng JSON), không sinh ra terms rác trong `wp_terms`.

---

### Test Case 2: Cấu hình Dynamic Routing và Truy cập Frontend
**Trạng thái:** `[x] Đã hoàn thành`

**Bước thực hiện:**
1. Mở Ska Data Pro -> Tạo một Smart Object tên là `Courses` (Khóa học).
2. Tại phần Cài đặt Portal (Portal Settings), nhập `frontend_slug` là `khoa-hoc`.
3. Bật **Kích hoạt Portal Template** và chọn một giao diện Template (ví dụ "LMS Dashboard").
4. Nhấn **Lưu Smart Object**. (Lúc này hệ thống sẽ tự động flush_rewrite_rules).
5. Mở trình duyệt ẩn danh (hoặc tab mới), truy cập URL: `http://your-domain/khoa-hoc/` và `http://your-domain/khoa-hoc/123/`.

**Kỳ vọng:**
- URL tải thành công và hiển thị giao diện của Template "LMS Dashboard" (chưa có data cũng được, nhưng không được trả về 404).
- Nếu đổi `frontend_slug` sang tên khác và lưu lại, đường dẫn cũ sẽ trả về 404, đường dẫn mới phải hoạt động lập tức.
- Hook `parse_request` (Parasite Dispatcher) đã đánh chặn và xử lý luồng, không chạy qua logic Query bài viết thừa thãi của WordPress.

---

### Test Case 3: Chức năng Phân quyền (Auth & Access Control)
**Trạng thái:** `[x] Đã hoàn thành`

**Bước thực hiện:**
1. Mở Cài đặt Portal của Smart Object `Courses`.
2. **Sub-case 3A (Private):** Chọn Role truy cập là `Administrator`. Bấm Lưu. Mở tab Ẩn danh (chưa đăng nhập) truy cập `/khoa-hoc/`.
3. **Sub-case 3B (Access Denied):** Mở tab khác, đăng nhập bằng account có role `Subscriber`. Truy cập `/khoa-hoc/`.
4. **Sub-case 3C (Public):** Quay lại Cài đặt Portal, chọn Role truy cập là `Public` (hoặc Guest/All). Lưu lại. Mở tab Ẩn danh truy cập `/khoa-hoc/`.

**Kỳ vọng:**
- **3A:** Chuyển hướng (Redirect) ngay lập tức về trang Đăng nhập (`wp-login.php` hoặc custom login URL).
- **3B:** Bị từ chối truy cập (Hiển thị trang lỗi 403 wp_die, hoặc redirect về trang Access Denied nếu đã filter hook `ska_access_denied_redirect_url`).
- **3C:** Truy cập thành công, hiển thị giao diện bình thường mặc dù không đăng nhập.

---

### Test Case 4: Liên kết Động (Relation Dynamic Link)
**Trạng thái:** `[x] Đã hoàn thành`

**Bước thực hiện:**
1. Có 2 Smart Objects: `Teachers` (giáo viên) và `Courses` (khóa học). `Courses` có một field relation `teacher_id` trỏ về `Teachers`.
2. Bảng `Teachers` có `frontend_slug` là `giao-vien`.
3. Thiết kế một Template cho `Courses` List, bên trong có khối Ska Loop lặp qua các Khóa học.
4. Trong Loop, kéo một Button "Xem hồ sơ Giáo viên". Cấu hình Link = Dynamic Link -> Loop Data -> Key là `teacher_id.url`.
5. Truy cập frontend `/khoa-hoc/` và soi HTML.

**Kỳ vọng:**
- Hệ thống tự phân tích dictionary và sinh ra đúng thẻ `<a href="/giao-vien/{ID_giao_vien_cua_khoa_hoc_do}/">`.
- Nhấp vào nút Button phải chuyển hướng thành công đến trang chi tiết giáo viên.

---

### Test Case 5: Tra cứu ngược (Reverse Lookup - Rollup List)
**Trạng thái:** `[x] Đã hoàn thành`

**Bước thực hiện:**
1. Tại trang Chi tiết Giáo viên (`/giao-vien/123/`), bạn muốn hiển thị "Các khóa học do giáo viên này dạy".
2. Kéo khối Ska Loop vào Template chi tiết Giáo viên. Chọn Nguồn dữ liệu (Source) là bảng `Courses`.
3. Cấu hình Filter cho vòng lặp:
   - Field: `teacher_id`
   - Operator: `JSON_CONTAINS` (hoặc `IN` tuỳ UI hỗ trợ mảng flat array).
   - Value: `{url:id}` (Lấy ID từ thanh URL).
4. Truy cập URL frontend `/giao-vien/123/`.

**Kỳ vọng:**
- Loop hiển thị đúng danh sách khóa học mà `teacher_id` chứa giá trị `123`.
- Nếu thay bằng URL `/giao-vien/456/`, nó sẽ load dữ liệu của giáo viên khác dựa theo App Record ID trong Router Context.
- Check Query Monitor để đảm bảo index Database (MySQL 8.0+ Multi-Valued Index) hoạt động mượt mà, không lag.

---

## 🛠 Hướng dẫn Gỡ lỗi (Troubleshooting)

| Dấu hiệu | Nguyên nhân có thể | Cách khắc phục |
| :--- | :--- | :--- |
| **Truy cập bị lỗi 404** | Rewrite Rules chưa được cập nhật (flush). | Ấn Lưu lại (Save) cấu hình Smart Object bên Data Pro để ép chạy `flush_rewrite_rules()`. Hoặc vào Settings -> Permalinks ấn Save. |
| **Role Public vẫn bị chuyển ra đăng nhập** | Logic kiểm tra role public bị sai chính tả ở mảng intercept array. | Kiểm tra `array_intersect( ['public', 'guest', 'all'], $roles )` trong hàm `enforce_security` của `Ska_App_Router`. |
| **Filter {url:id} không load ra data** | Router Context chưa nhận được `app_record_id`. | Dùng `var_dump(get_query_var('app_record_id'))` trong file render của loop để kiểm tra xem URL matcher có bóc tách đúng Regex hay không. |
| **{{relation.url}} xuất ra rỗng** | Dictionary chưa lưu thông tin `portal_settings` của bảng đích. | Kiểm tra biến tổng `$ska_data_dictionary` có thiếu mục `portal_settings` hay `frontend_slug` của bảng Relation đó không. |

---
*Ghi chú: Workflow này được dùng làm khung chuẩn để nghiệm thu Phase 5 & Dedicated Pages trước khi đóng gói sản phẩm hoàn thiện.*
