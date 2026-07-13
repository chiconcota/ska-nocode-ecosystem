# PROJECT MANAGER: PHASE 4 - FRONTEND ECOSYSTEM (APP BUILDER & CANVAS UI)
@status: 🟢 MVP Completed (Ready for Production Packaging) | @last_update: 2026-05-23 | @context: Master Roadmap cho toàn bộ Tầng Phân hệ Hiển thị (Frontend/React UI).

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Hoàn thiện hệ sinh thái UI:** Chuyển giao sức mạnh cốt lõi từ `Skaaa Data Pro` và `Skaaa Logic Engine` lên giao diện người dùng (No-code / Canvas).
- **The Dogfooding Model (Skaaa System):** Quản lý hệ thống Theme Builder, Organisms và Global Presets bằng giao diện Flat Tables thông qua Smart Object chống xóa `skaaa_system`.
- **Framework-Agnostic Site Design:** Cho phép dựng toàn bộ Website (Header, Footer, Archive, Logic) mà KHÔNG cần dựa dẫm vào cấu trúc PHP của Theme WordPress.

---

## 2. ROADMAP THEO HẠNG MỤC (PHASE 4 TRACKER)

### 2.1. Skaaa System Dashboard & App-site Routing (Ưu tiên Cao)
*Nhiệm vụ chuyển hướng phát triển từ React Editor sang hoàn thiện vòng lặp UI.*
- [ ] Gắn Action Links (Mở Trình Thiết Kế, Option Design) vào card hiển thị của Plugin Skaaa No-code Design trên System Dashboard. Khóa ổ khóa 🔒 nếu thiếu Dependencies.
- [ ] Xử lý Routing cho nút "Mở Trình Thiết Kế" trỏ thẳng vào giao diện DataGrid của Smart Object `app-site`.
- [x] Thiết kế trang / modal React "Brand, Font & Theme Options". Móc nối REST API để POST JSON vào bảng `skaaa_data_sys_presets`. (Sử dụng Alpine.js thay React)
- [x] Xuất file Physical Cache `.json/.js` để JIT Tailwind render tốc độ cao mỗi khi Save.

### 2.2. Skaaa Symbols & Smart Object 'app-site' (Tầng Lưu Trữ & Custom Blocks)
*Được khởi tạo từ `project_manager_custom_blocks.md` cũ.*
- [x] Khởi tạo phân vùng App `skaaa_system` (nhãn "Site Management") trong `App_Manager` và chặn quyền thay đổi/xoá app này.
- [x] Mồi/tạo 3 Bảng Hệ Thống (`skaaa_data_sys_organisms`, `skaaa_data_sys_theme_templates`, `skaaa_data_sys_presets`) tự động qua hook migration/setup của Skaaa Data Pro.
- [x] Tuỳ biến `skaaa_data_dictionary` để có nhãn UI thuần Việt cực xịn ("Organisms Blocks", "Theme Templates", "Design Tokens").
- [x] Kích hoạt hệ thống Cache thông minh: Bất cứ chức năng Update/Delete/Insert nào, ghi đè file `.json/.php` fallback giúp Frontend truy xuất Data nhanh siêu tốc.
- [x] Cảnh báo cài đặt (Red Banner) nếu người dùng chối bỏ việc cài Skaaa Data Pro và Logic Engine.
- [x] Gắn shortcut "Site Blueprint" tại menu Skaaa Builder Core trỏ sang UI DataGrid tương ứng của `skaaa_system`.
- [x] Tính năng (React UI): Tạo nút "Save as Organism Block" gửi đẩy Payload (JSON/HTML) thẳng xuống CSDL flat-table `skaaa_data_sys_organisms`.
- [x] Tính năng (Inserter): Lấy danh sách Block đã lưu đưa vào Inserter (+) của Gutenberg và render thành reference thay vì hardcode.
- [x] Tính năng (Global Edit): Sửa bản gốc bằng Shadow CPT và Iframe Modal.
- [x] Tính năng (Local Edit / Detach): Phân rã (Detach) Symbol thành các block thường (Native Blocks) để người dùng có thể sửa nội dung cục bộ trên trang hiện tại.
### 2.2. Skaaa Molecule & Alpine.js Library (Tầng Tương tác Frontend)
*Được tích hợp từ `project_manager_molecule_engine.md` cũ.*
- [x] Mảng UI Tabs, Accordion (Sử dụng Template Lock). Trang bị chuẩn `x-data`.
- [x] Mảng Dropdown Menu, Offcanvas Slider, Standard Forms.
- [x] Khởi tạo hệ sinh thái `Alpine.store('app')` để kết nối Global State chéo giữa các block riêng rẽ.
- [x] React Inspector cho Skaaa Select: Bật tắt Dynamic Binding, Protective UX lọc bảng/cột và Template Auto-Generation (Zero N+1).
- [x] **Khủng hoảng UX - Pivot Nocode Form:** Nhận thấy việc bắt người dùng cấu hình thủ công các biến trạng thái (`fields.*`, `status.*`, `skaaaForm()`) trong Alpine là bất khả thi đối với non-coder. Yêu cầu thiết kế lại giải pháp "Abstraction Layer" (Ví dụ: Form Builder UI riêng hoặc Block Form tự động map attributes) nhằm tự động hoá quy trình kết nối Logic Engine.

### 2.3. Theme Builder & Khối Vòng Lặp Vạn Năng (Skaaa Query Loop)
*Được tích hợp từ `project_manager_theme_builder.md` cũ.*
- [x] Lập cấu trúc Ghi đè Template (WordPress Template Router) từ Flat table (Header/Footer/Archive...). Đã chốt kiến trúc **Smart Virtual Wrapper** và loại bỏ FSE.
- [x] Thiết kế **Skaaa Theme Panel** (Dashboard quản lý Template) sử dụng Alpine.js + Tailwind CSS, lưu trữ qua API vào `skaaa_data_sys_organisms` (chuyển qua `skaaa_data_sys_theme_templates`).
- [x] Khởi tạo Isolated Editor (Iframe Gutenberg toàn màn hình) dành riêng cho việc biên tập Theme Template.
- [x] **Khối Query Loop (Foreach / Map):** Đã hoàn tất chiến dịch độc lập. 
  -> 👉 **Xem chi tiết tại:** `project_manager_skaaa_loop_block.md`
- [x] Tích hợp Skaaa Query Loop kết nối với Skaaa Dynamic Content (Biến nhúng `{{...}}`).

### 2.4. Tính Năng UI Độc Lập / Mới Cập Nhật
*Được trích xuất từ phần 'Tương lai' (3.3) của Phase 3.*
- [x] **Milestone 2 (Design Engine) - Dark Mode Thượng tầng:** Lập kế hoạch mang lại chức năng Dark Mode hoàn chỉnh (`darkMode: 'class'`), kết hợp Tailwind. Cần xây dựng Switcher / Toggle Block.
- [x] 🌟 **App Dashboards / Sub-Admin Portals (Phase 4.5):** Đã phân tách Kế hoạch Kiến trúc ra file riêng. -> 👉 **Xem chi tiết tại:** `project_manager_app_portal.md`
- [x] 🌟 **Auto-Generated CRUD Portal (Phase 4.6):** Đã phân tách Kiến trúc Macro Injector ra file riêng. -> 👉 **Xem chi tiết tại:** `project_manager_auto_crud.md`
- [ ] **Role-Based Access Control (RBAC) cho Tầng Hiển thị:** Xử lý hệ thống phân quyền nâng cao (RBAC) để quyết định hiển thị Khối/Template cho từng nhóm đối tượng.
- [x] **Skaaa System Framework (Dashboard):** 
  - Hoàn thiện luồng API Request thật cho thẻ cấu hình Skaaa AI Architect.
  - Viết Hook cho 2 nút Danger Zone (Clear Context, Flush JIT Cache) ở góc của System Dashboard.
  - 🛑 TẠM DỪNG: Tích hợp Tab "Theme Options/Design Tokens" map thẳng vào Smart Object `skaaa_system` (Đã gỡ bỏ cách tiếp cận cũ).
- [x] 🌟 **Kiến trúc Design Engine Mới (3 Lớp):** (Đã hoàn thành ở Phase 3)
  - Lớp 1: Tokens Registry (Nhận file design.md, gen CSS Variables).
  - Lớp 2: Semantic Base Styling (Gen css global cho thẻ HTML H1, H2, p).
  - Lớp 3: Visual Tailwind Browser (Tạo UI trực quan chọn biến thay vì input text).

---

## 3. CÁC QUY TẮC BẢO VỆ (CONSTRAINTS)
- ⚠️ **Zero Dependency:** Khối Query Loop và Giao diện Theme KHÔNG lấy dữ liệu cứng, tất cả phải dùng chung cỗ máy Data Fetcher của `Skaaa Data Pro`. Giao tiếp qua `apply_filters`.
- ⚠️ **Decoupled:** Danh sách các Template được xây dựng trên Flat Tables. Việc phân phối hiển thị sẽ do phân hệ `Skaaa Logic Engine` giám định để đảm bảo tách bạch.
