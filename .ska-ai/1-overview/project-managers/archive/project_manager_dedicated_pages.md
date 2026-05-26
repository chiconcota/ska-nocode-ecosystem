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
- [x] **Task 1.1: Remove Portal Visibility Extension:** Xóa code liên quan đến extension Gutenberg "Portal Visibility" (`skaPortalView`) và logic tự động bơm `x-show` trong PHP.
- [x] **Task 1.2: Refactor Alpine Store:** Tối giản lại `SkaPortal Store` trong JS. Không cần xử lý logic View (`list`, `detail`, `create`) trên frontend nữa. Store giờ đây chỉ tập trung vào việc hứng dữ liệu API hiện tại (Current Page Data).

### Phase 2: App Categorization (Theme Builder UI)
**Mục tiêu:** Xây dựng hệ thống Folder (Thư mục ảo) để phân loại Template bằng JSON Dictionary (tối ưu truy vấn).
- [x] **Task 2.1: Dictionary Storage:** Khởi tạo Option `ska_theme_builder_folders` trong `wp_options` để lưu cấu trúc cây thư mục dưới dạng JSON (ngăn chặn N+1 query so với dùng meta thuần).
- [x] **Task 2.2: Modal UI Update:** Thêm trường "Thuộc App nào?" vào Modal tạo mới Template, lưu `folder_id` vào JSON array `conditions` của Template để làm mapping reference.
- [x] **Task 2.3: Admin UI Filtering:** Nạp thẳng cục JSON Folder ở trên ra UI để dựng Tree View siêu nhẹ. Các Template không có nhóm sẽ hiển thị ở "Core/Global".

### Phase 3: Dynamic Routing V2 (The Core Engine)
**Mục tiêu:** Khả năng định tuyến động theo cấu trúc Dữ liệu thay vì gõ tay từng trang.
- [x] **Task 3.1: Custom Base Slug:** Cho phép nhập `frontend_slug` trong phần cài đặt của Smart Object (Data Pro). (VD bảng Lessons có slug là `bai-hoc`).
- [x] **Task 3.2: Safe Rewrite Rules:** Tự động tạo Rewrite Rule dựa trên `frontend_slug`. **TUYỆT ĐỐI:** Chỉ trigger `flush_rewrite_rules()` khi Nocode Admin ấn Lưu cài đặt Smart Object, không bao giờ chạy trong hook `init`.
- [x] **Task 3.3: Parasite Architecture Dispatcher:** Hook vào `parse_request` (rất sớm) để đánh chặn URL. Load Context và ép WP render thẳng file HTML/JSON của `ska_theme_builder`, tránh các query rác của WP đi tìm file .php.

### Phase 4: Native Deep Linking & Dynamic Binding
**Mục tiêu:** Kết nối Dữ liệu (Backend) với Khối hiển thị (Gutenberg) ngoài Frontend.
- [x] **Task 4.1: Relation Dynamic Link:** Nâng cấp tính năng `Dynamic Link` trong bộ công cụ Gutenberg để tự động tạo URL trỏ tới trang Detail nếu ô dữ liệu là một trường Relation (Khóa ngoại).
- [x] **Task 4.2: Reverse Lookup (Ska List):** Nâng cấp block `ska-list` để hỗ trợ filter theo Context. (VD: Đang đứng ở trang Detail Giáo viên, `ska-list` tự động gửi Request API chỉ lấy các Bài Học có `teacher_id = current_url_id`).
- [x] **Task 4.3: DB Auto-Indexing (Data Pro):** Bắt buộc tự động chèn lệnh `ADD INDEX` cho tất cả cột được thiết lập là Relation (Khóa ngoại) khi build bảng, chống Table Scan sập DB khi Reverse Lookup.

### Phase 5: Auth & Access Control
**Mục tiêu:** Hợp nhất hệ thống bảo mật cho cả App Portal nội bộ và Public Frontend Page.
- [x] **Task 5.1: Early Intercept Middleware:** Đặt Middleware check quyền ngay tại `parse_request` (cổng vào của Router Phase 3). Ngăn WP load các hàm nội lõi rác rưởi nếu user bị từ chối truy cập, tiết kiệm CPU.
- [x] **Task 5.2: Redirect Auth:** Cấu hình trang Đăng nhập tuỳ biến nếu truy cập thất bại.
