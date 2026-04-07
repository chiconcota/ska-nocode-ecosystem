# PROJECT MANAGER: PHASE 3 (SKA LOGIC ENGINE) & TẦM NHÌN HỆ SINH THÁI
@status: 🔴 Plan Phase | @last_update: 2026-04-04 | @context: Logic Engine Architecture & Roadmap

---

## 1. TÓM TẮT KIẾN TRÚC LÕI (THE CORE RULES)
- **Tôn chỉ "Airtable-like Ux":** Giấu hoàn toàn các khái niệm Database (SQL, Hook, dbDelta) xuống tầng ngầm. Giao diện quản lý phải 100% thân thiện kiểu Spreadsheet / No-code.
- **Micro-Ecosystem (Quy tắc Cách ly):** Giao tiếp giữa các module phải đi qua `WP Hooks`. Data Pro vẽ giao diện Admin bằng tự nhúng CDN, tránh "ký sinh" vào thư viện của Design Engine.
- **Flat Table First (Nhưng không cực đoan):** Dữ liệu ứng dụng lưu toàn bộ vào bảng phẳng `ska_data_*`. Nhưng tài khoản hội viên/mật khẩu thì nối (Foreign Key) với bảng `wp_users` chuẩn của WordPress.

---

## 2. ROADMAP PHASE 2: SKA DATA PRO (DATABASE ENGINE) - 🟢 [ĐÃ HOÀN TẤT]
Phase 2 chịu trách nhiệm đúc "Thùng chứa" (Database) và thiết lập hệ thống truy xuất dữ liệu (Query) hiện tại đã vể đích.

### 2.1. Template Gallery & Schema Manager - 🟢 [HOÀN TẤT]
- Màn hình chọn Mẫu dữ liệu (Data Templates: E-Commerce, LMS, Booking...)
- Kịch bản: Bấm chọn Mẫu -> Chạy SQL ngầm -> Tạo bảng `ska_data_*` -> Bơm dữ liệu giả (Dummy Data) -> Hiện giao diện quản lý dạng Lưới (Grid).
- **Trạng thái:** Toàn bộ DB Engine (dbDelta), Form CRUD (Thêm/Sửa/Xóa Schema và Bản ghi) qua Ajax đã tích hợp thành công với Dashboard tĩnh.

### 2.2. Query Builder & Cỗ Máy Nội Suy (Backend Dynamic Content) - 🟢 [HOÀN TẤT]
- Xây dựng Cỗ máy khởi chạy câu SQL thô từ Hook `apply_filters('ska_data_query', ...)` thông qua `Query_Builder`.
- Mở khóa tính năng gõ tay `{{ska:product_price}}` vào khối Ska Text thông qua `Ska_Provider` cắm trực tiếp vào Data Engine của lõi Builder.

### 2.3. Data Providers / Thích Ứng Mở Rộng - 🟢 [HOÀN TẤT]
- Xây dựng cơ chế Adapter để kéo dữ liệu từ nhiều Nguồn: 
  - Nguồn nội bộ: `Ska Native Flat Tables` (Đã có sẵn).
  - Nguồn Core WP: Tích hợp thành công `WP Posts`, `WP Users` thẳng vào Cột Relation thông qua Bypass Layer tại Data Fetcher để đạt tốc độ O(1) query.
  - Nguồn Ngoại lai: Xây dựng thành công Provider POC khởi chạy tại `plugins_loaded` để sẵn sàng cho `WooCommerce` hoặc bất kì Custom API nào.

### 2.4. DataGrid: Nâng Cấp Hệ Sinh Thái RDBMS - 🟡 [IN PROGRESS]
- 🟢 **Tham Chiếu Nối Bảng (Relation)**: Cho phép nối bảng Sản Phẩm với Danh Mục hoặc Users thông qua giao diện Popover chọn lựa (Hỗ trợ Data Flat Tables + WP Core).
- 🟢 **Bộ Công Cụ Lưới (Grid Controls)**: Hoàn tất xây dựng thanh URL-driven cho Lọc (Filter), Sắp Xếp (Sort), và Gộp Nhóm (Group Zero-overhead).
- 🟢 **Cột Tra Cứu (Rollup / Lookup) [HOÀN TẤT]**: Nâng cấp Cascading Dropdown dựa trên AJAX để cấy trực tiếp Dữ liệu ảo (Virtual Data) tại thời điểm Render (N-1 Query gom nhóm).
- 🟢 **[HOÀN TẤT] Trắc nghiệm Cột Rollup với System Core WP:** Đã tích hợp thành công cầu nối "Hút máu" dữ liệu Metadata của WordPress EAV (`wp_postmeta`, `wp_usermeta`). Xây dựng Màng Lọc Heuristics Scanner thông minh hất văng hàng trăm file meta rác hệ thống, trải thảm xanh cho DataGrid truy cập siêu tốc vào ACF, WooCommerce `_price`, `_sku`. Xử lý rốt ráo Lỗi Stale Computed UX bằng cơ chế Reload chớp nhoáng (Micro-reload).
- 🔴 **Tính Toán Cục Bộ (Formula / Compute) [ĐANG CHỜ]**: Nghiên cứu khả năng tính toán Virtual Columns hoặc Render bằng PHP (Data Engine) dựa trên các giá trị có trong Grid (Ví dụ: Số lượng * Đơn giá).


### 2.5. Đại Tu Codebase (The Great Refactor) - 🟢 [HOÀN TẤT]
- Ska Data Pro đã hoàn tất các tính năng cốt lõi (Field Types, Lọc, Tìm Kiếm), và phân tách cấu trúc lại toàn bộ Front-end JS của DataGrid (`admin-datagrid.js`).
- **Mục tiêu:** Phá vỡ quái vật JavaScript Monolithic (>700 dòng).
- **Chiến thuật:**
  - Áp dụng **ES6 Modules** (chia nhỏ thành `core.js`, `modals.js`, `cells/`...).
  - Dùng **Vite/Webpack** để biên dịch và nén lại thành 1 file duy nhất siêu nhẹ.
  - Triển khai **Strategy Pattern** cho Cell Engine để Scale gọn gàng, đón đầu các kiểu dữ liệu mới trong tương lai.

---

## 3. ROADMAP TƯƠNG LAI BẮT CẦU (BEYOND PHASE 2)

### 3.1. Nâng cấp Phase 2.5: Dynamic Tag UI (Frontend Picker)
- Việc: Tạo cái nút Lấp lánh (Sparkle Icon) trên trình soạn thảo Gutenberg của Ska Builder.
- Tác dụng: Cho phép người dùng chọn thẳng Fields (Cột: Giá, Tên) từ một cái Dropdown thay vì phải học thuộc lòng cú pháp `{{tag}}`.

### 3.2. Phase 3: Ska Logic Engine & Khối Form (The Flow) - 🟡 [IN PROGRESS]
- 🟢 **Khối Nhập Liệu (Ska Form)**: Đúc xong các khối `<Input>`, `<Select>`, `<Form Wrapper>` vào lõi `ska-no-code-design`. Đã giải quyết triệt để vấn đề "Clean Slate Form Preflight" và "Tailwind V4 Editor Parity" (Polyfill & JIT Proxy Mutation). PHP JIT Engine rà soát thành công các UI phức tạp nhất (Logical Spacing RTL/LTR, Pseudo Objects Arbitrary cho Toggle/Checkbox Component). Mảng UI Form coi như đã **Hoàn tất MVP**.
- 🟢 **Ska-xi măng (Logic Engine / The Trinity)**: Hoàn tất Lớp Nhân Đứng Giữa (Controller/Router/Băng Chuyền) với các tính năng:
  - **Mapping:** Giao diện Linear Builder (Băng chuyền nối dọc bằng Vanilla JS) mô phỏng UI kiểu n8n/Zapier. Tự động xuất chuỗi sự kiện thành mảng `JSON Graph`. (Future-proof với React Flow ở Phase 4).
  - **Processing:** Module siêu nhỏ (Strategy Pattern) rèn Data trước khi cất kho: Tạo Slug (`Ska_Slug_Processor`), Format Date (`Ska_Date_Processor`).
  - **Action:** Gửi thông báo Email (`Ska_Email_Action`), Đẩy lệnh `INSERT` dữ liệu đa bảng qua Hook cho `Ska Data Pro` (`Ska_Insert_Data_Action`).
- 🟡 Xử lý hệ thống Role-Based Access Control (RBAC) và vòng lặp danh sách (Foreach/Map). (Dời sang Milestone Update).
- 🟡 **Quản Lý Băng Chuyền (Logic Manager UI):** Xây dựng trang quản trị hiển thị Danh sách các Luồng Logic (List View). Cho phép người dùng trực quan Thêm Mới, Xóa, Đổi Tên các Luồng thay vì bị phụ thuộc vào 1 luồng ẩn như hiện tại.
- 🟡 **Hoàn thiện UX Linear Builder:** Loại bỏ hành vi Enter tự reload form gây ức chế. Bổ sung nút [OK] thu gọn ở mỗi Node (Bước) giúp Nocode User an tâm đã lưu file cấu hình thành công mà không nhầm lẫn.

### 3.3. Tính Năng Độc Lập Mọi Thời Điểm (UI Features & Canvas App)
- **Theme Builder** (Lắp Header, Footer từ CPT `ska_template`).
- **Milestone 2 (Design Engine): Dark Mode Thượng tầng:** (🔴 Pending) Lập kế hoạch mang lại chức năng Dark Mode hoàn chỉnh (`darkMode: 'class'`). Cần nghiên cứu cơ chế bọc selector `:is(html.dark, .dark)` an toàn mà không phá vỡ logic specificity hiện hành, đồng thời xây dựng UI Toggle trên Frontend/Editor.
- **App Dashboards / Sub-Admin Portals (🔴 Pending):** Tạo Custom Post Type (`ska_portal`) trong `ska-builder-core`. Giúp User dùng luôn quyền trượng Editor để tự do kéo thả bộ khung giao diện trang quản lý Khóa Học, Thành Viên ra Frontend Portal thay vì ép phải xài trang cấu hình xám xịt của WordPress Admin.
- **Custom Block / UI Symbols** (Khối tái sử dụng thiết kế).
- *Lưu ý:* Mấy tính năng này bản chất là Hệ Giao diện Canvas UI/Design (Lưu cục HTML), không cần đợi Phase 3, có thể tranh thủ làm luôn ngay trong Phase 2 bất cứ khi nào rảnh rỗi.

### 3.4. Milestone 4++: Tối ưu Big Data (SQL Indexing & Caching cho Cột Ảo)
- Kế hoạch dự phòng cho ứng dụng quy mô lớn (>1,000,000 mẩu dữ liệu). Khi DataGrid/App Frontend cần thực thi các lệnh `ORDER BY` hoặc Tương tác Lọc (Filter) lên các Cột Ảo (`Rollup` / `Formula`).
- Xây dựng Cỗ máy Caching Webhook/Trigger: Lưu vết giá trị tính toán cục bộ giấu xuống thẳng bảng MySQL (`SQL Persistence Layer`).
- Không cần ưu tiên trong MVP hiện tại vì nó nằm ở độ khó Enterprise, Trade-off kiến trúc nặng.
