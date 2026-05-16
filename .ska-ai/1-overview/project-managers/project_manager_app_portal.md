# PROJECT MANAGER: APP DASHBOARDS & PORTALS (PHASE 4.5)
@status: TODO
@priority: High | @dependency: Ska Theme Builder, Ska Data Pro

## 1. TẦM NHÌN (VISION)
Cung cấp hạ tầng cốt lõi để đưa Ska từ "Website Builder" lên tầm "App Builder". Xóa bỏ hoàn toàn sự phụ thuộc vào `/wp-admin` để quản lý dữ liệu, tạo ra các giao diện quản trị Frontend (Portals) độc lập, bảo mật và chuẩn White-label (có thể đóng gói đem bán).

## 2. QUYẾT ĐỊNH KIẾN TRÚC (ARCHITECTURE DECISIONS)
- **Data Pro Integration (No Shadow CPT):** Mở rộng **Ska Data Pro** để quản lý App Portals trực tiếp từ Smart Object thay vì tạo Shadow CPT thừa thãi.
- **URL & Security Cấp Bảng:** Cấp URL, Prefix điều hướng và Security Role (Quyền truy cập) ngay tại cấp độ cấu hình của từng Smart Object (Bảng dữ liệu).
- **Virtual Routing (Frontend):** Sử dụng cơ chế Wrapper của Theme Builder để quyết định giao diện hiển thị ở Frontend dựa trên URL (Ví dụ: `/portal/*`). Hỗ trợ cả cơ chế Multi-page và SPA (Single Page Application) qua Alpine.js.
- **Data View Rendering:** Hỗ trợ thiết lập cấu trúc View (Sub-table, Relation & Rollup tự động). Cung cấp chế độ Read-only hoặc CRUD trực tiếp tại View.

## 3. LỘ TRÌNH TRIỂN KHAI (IMPLEMENTATION PHASES)

### Phase 1: App Portal Configuration (Ska Data Pro)
**Mục tiêu:** Cho phép Nocode Admin cấu hình URL, Quyền truy cập (Role) và Giao diện Portal ngay trong bảng Smart Object.
- [x] **Task 1.1: DB Schema Upgrade:** Cập nhật cấu trúc Blueprint và logic lưu của Smart Object để chứa thêm trường `portal_settings` (gồm URL Slug, Allowed Roles, Portal Active).
- [x] **Task 1.2: Portal Settings UI:** Nâng cấp UI ở `/wp-admin` của Ska Data Pro, thêm modal/panel "App Portal Settings" trên DataGrid để thiết lập cấu hình.
- [x] **Task 1.3: Data Validation:** Viết API Endpoint và Controller lưu trữ kèm cơ chế kiểm tra trùng lặp URL Slug để tránh đụng độ (Collision) định tuyến.

### Phase 2: Virtual Router & Security Layer (Ska Theme Builder)
**Mục tiêu:** Chặn (Intercept) các URL của hệ thống (VD: `/portal/...`) và quyết định quyền truy cập trước khi nạp giao diện.
- [x] **Task 2.1: URL Interceptor (Rewrite Rules):** Đăng ký Custom Rewrite Rule trong WordPress để bắt pattern URL theo thiết lập của Portal.
- [x] **Task 2.2: Context & Auth Middleware:** Viết Middleware để nhận diện Smart Object qua URL. Kiểm tra `Current User Role` với `Allowed Roles` của bảng. Xử lý trả về trang 403 Forbidden hoặc Redirect Login nếu không đủ quyền.
- [x] **Task 2.3: Virtual Template Injector:** Nếu vượt qua bảo mật, gạt bỏ Template Hierarchy mặc định của Theme và bơm (inject) Master Template (Giao diện App) của Theme Builder vào thay thế.

### Phase 3: Data View Engine (Ska Data Pro & Logic Engine)
**Mục tiêu:** Cung cấp hạ tầng truy xuất dữ liệu mạnh mẽ để đổ (render) ra màn hình Portal với hỗ trợ phân trang và quan hệ bảng.
- [x] **Task 3.1: REST API / GraphQL Endpoint:** Tạo endpoint bảo mật để Frontend có thể query dữ liệu của Smart Object phục vụ Portal Data View.
- [x] **Task 3.2: Pagination & Filter Core:** Xây dựng logic phân trang, lọc (Filter) và sắp xếp (Sort) tối ưu trên flat tables.
- [x] **Task 3.3: Relation & Rollup Engine:** Bổ sung cơ chế tự động JOIN/Fetch các bản ghi con (Sub-table) và tự động tính toán (Rollup) vào chung một JSON payload.

### Phase 4: App Portal Layout & Frontend SPA Integration
**Mục tiêu:** Lắp ráp UI thành dạng SPA (Single Page Application) sử dụng dữ liệu từ Phase 3.
- [x] **Task 4.1: Portal Layout Variables:** Truyền context an toàn (Current Table, Data, Schema Columns) từ Backend xuống Frontend qua `wp_localize_script` (hoặc chèn tĩnh vào Alpine Store).
- [x] **Task 4.2: SPA Navigation (Alpine):** Xử lý điều hướng bên trong Portal (Chuyển đổi view List -> Detail -> Create) qua Alpine.js để tránh reload toàn bộ trang, đảm bảo trải nghiệm UX mượt mà.

### Phase 5: Custom Login Page (Future)
**Mục tiêu:** Xây dựng tính năng cho phép tạo trang đăng nhập tùy biến bằng Ska Theme Builder và tích hợp vào App Portal.
- [ ] **Task 5.1: Login Form Block:** Phát triển khối (block) Login Form trong Ska Logic Engine với khả năng xử lý xác thực qua AJAX.
- [ ] **Task 5.2: Auth Gateway Update:** Nâng cấp Virtual Router để cho phép cấu hình URL điều hướng khi User chưa đăng nhập (thay vì mặc định đẩy về `wp-login.php`). Hỗ trợ redirect_to sau khi đăng nhập thành công.
