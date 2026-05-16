# PROJECT MANAGER: DEDICATED PAGES & APP CATEGORIZATION
@status: TODO
@priority: High | @dependency: Ska Theme Builder, Ska Data Pro

## 1. TẦM NHÌN (VISION)
Loại bỏ mô hình SPA App Portal (sử dụng `x-show` gom chung nhiều view trên 1 màn hình) do rủi ro phình to DOM và gây rối rắm cực độ cho trải nghiệm thiết kế (UX) của Nocode Admin.
Chuyển đổi hoàn toàn sang kiến trúc **Dedicated Pages (Trang độc lập)** kết hợp **Dynamic Routing (Định tuyến động)**. Mọi đối tượng (từ Dashboard nội bộ đến Trang Frontend học viên) đều được đối xử như nhau, quản lý qua Smart Object và kết nối bởi Relation (Khóa ngoại).

## 2. QUYẾT ĐỊNH KIẾN TRÚC (ARCHITECTURE PIVOT)
- **Dedicated Templates:** List View, Detail View, Create Form, Edit Form... mỗi thứ sẽ là một Template (Custom Post Type `ska_theme_builder`) riêng biệt, thiết kế độc lập.
- **Virtual Folder (App Categorization):** Để tránh "bãi rác UI" khi số lượng Template quá lớn, bổ sung trường cấu hình `App Category` (không dùng WordPress Taxonomy mặc định) lưu trực tiếp vào CPT, và tạo bộ lọc giao diện Admin Panel để nhóm các Template theo từng App (VD: LMS, E-commerce, HR).
- **Dynamic Router V2:** Nâng cấp `class-ska-app-router.php` thành cỗ máy định tuyến động mạnh mẽ. Thay vì gò bó ở dạng `/portal/{table}`, nó sẽ chấp nhận mọi Custom Base Slug và hỗ trợ bắt Parameter (VD: `/khoa-hoc/` và `/khoa-hoc/{id}`).
- **No Taxonomy, Pure Relations:** Xóa bỏ khái niệm WP Taxonomy. Dùng Flat Tables với trường Relation để biểu diễn quan hệ (Ví dụ Giáo viên 1-n Bài học), cho phép Reverse Lookup (Rollup) trực tiếp với tốc độ ánh sáng.

## 3. LỘ TRÌNH TRIỂN KHAI (IMPLEMENTATION PHASES)

### Phase 1: Dọn dẹp & Phế bỏ SPA Cũ
**Mục tiêu:** Gỡ bỏ các logic rườm rà của phiên bản SPA cũ.
- [ ] **Task 1.1: Remove Portal Visibility Extension:** Xóa code liên quan đến extension Gutenberg "Portal Visibility" (`skaPortalView`) và logic tự động bơm `x-show` trong PHP.
- [ ] **Task 1.2: Refactor Alpine Store:** Tối giản lại `SkaPortal Store` trong JS. Không cần xử lý logic View (`list`, `detail`, `create`) trên frontend nữa. Store giờ đây chỉ tập trung vào việc hứng dữ liệu API hiện tại (Current Page Data).

### Phase 2: App Categorization (Theme Builder UI)
**Mục tiêu:** Xây dựng hệ thống Folder (Thư mục ảo) để phân loại Template.
- [ ] **Task 2.1: Schema Update:** Bổ sung việc lưu metadata `app_group` cho các bài viết thuộc CPT `ska_theme_builder`.
- [ ] **Task 2.2: Modal UI Update:** Thêm một trường nhập liệu (Input/Select) "Thuộc App nào?" vào Modal tạo mới Template trong màn hình `admin-panel.php`.
- [ ] **Task 2.3: Admin UI Filtering:** Nâng cấp Alpine.js ở `admin-panel.php` để tạo một Sidebar hoặc Dropdown cho phép bấm chọn lọc Template theo nhóm App. Các Template không có nhóm sẽ hiển thị ở "Core/Global".

### Phase 3: Dynamic Routing V2 (The Core Engine)
**Mục tiêu:** Khả năng định tuyến động theo cấu trúc Dữ liệu thay vì gõ tay từng trang.
- [ ] **Task 3.1: Custom Base Slug:** Cho phép nhập `frontend_slug` trong phần cài đặt của Smart Object (Data Pro). (VD bảng Lessons có slug là `bai-hoc`).
- [ ] **Task 3.2: Rewrite Rules Generator:** Cập nhật hàm `register_rewrites()` để tự động tạo Rewrite Rule dựa trên các `frontend_slug` đang có trong Dictionary. Hỗ trợ bắt Route chi tiết: `^slug/([a-zA-Z0-9_-]+)/?$`.
- [ ] **Task 3.3: Route Context Dispatcher:** Khi User truy cập vào URL, Router phải tính toán được đây là trang Archive (List) hay trang Single (Detail) dựa trên việc có tồn tại Parameter ID hay không. Đẩy thông tin này vào Global Context để `Virtual_Wrapper` chọn đúng Template.

### Phase 4: Native Deep Linking & Dynamic Binding
**Mục tiêu:** Kết nối Dữ liệu (Backend) với Khối hiển thị (Gutenberg) ngoài Frontend.
- [ ] **Task 4.1: Relation Dynamic Link:** Nâng cấp tính năng `Dynamic Link` trong bộ công cụ Gutenberg để tự động tạo URL trỏ tới trang Detail nếu ô dữ liệu là một trường Relation (Khóa ngoại).
- [ ] **Task 4.2: Reverse Lookup (Ska List):** Nâng cấp block `ska-list` để hỗ trợ filter theo Context. (VD: Đang đứng ở trang Detail Giáo viên, `ska-list` tự động gửi Request API chỉ lấy các Bài Học có `teacher_id = current_url_id`).

### Phase 5: Auth & Access Control
**Mục tiêu:** Hợp nhất hệ thống bảo mật cho cả App Portal nội bộ và Public Frontend Page.
- [ ] **Task 5.1: Role Checking Middleware:** Cập nhật Middleware để quét quyền. Nếu Smart Object không yêu cầu Role (Public), cho phép xem tự do. Nếu cần Role, check `current_user_can()`.
- [ ] **Task 5.2: Redirect Auth:** Cấu hình trang Đăng nhập tuỳ biến nếu truy cập thất bại.
