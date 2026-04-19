# PROJECT MANAGER: PHASE 3 (SKA LOGIC ENGINE) & TẦM NHÌN HỆ SINH THÁI
@status: 🟢 In Progress / MVP Done | @last_update: 2026-04-13 | @context: Logic Engine Architecture & Roadmap

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

### 3.1. Nâng cấp Phase 2.5: Dynamic Tag UI / Frontend Picker - 🟡 [IN PROGRESS]
- Đã quy hoạch lại ranh giới, gộp vào dự án khổng lồ **"Universal Dynamic Binding"**. (Truy cập file `project_manager_universal_dynamic_binding.md` để xem tiến độ chi tiết).
- **Trạng thái:** Đã xong Phase 1 (Động cơ móc Frontend), đang chuẩn bị thiết kế UI React.

### 3.2. Phase 3: Ska Logic Engine & Khối Form (The Flow) - 🟢 [MVP HOÀN TẤT]
- 🟢 **Khối Nhập Liệu (Ska Form & Skapine Engine)**: Đúc xong các khối `<Input>`, `<Select>`, `<Form Wrapper>` vào lõi `ska-no-code-design`. Đã giải quyết triệt để vấn đề "Clean Slate Form Preflight" và "Tailwind V4 Editor Parity" (Polyfill & JIT Proxy Mutation). PHP JIT Engine rà soát thành công các UI phức tạp nhất (Logical Spacing RTL/LTR, Pseudo Objects Arbitrary cho Toggle/Checkbox Component). Tích hợp thành công cấu trúc **Alpine.js** (`x-data`, `x-show`) thiết lập Multi-step Form & Tabs thông qua HTML Attributes panel mà không cần code injection. Tái cấu trúc Panel HTML Attributes thành dạng Opt-group trực quan. Màn nâng cấp **Skapine Engine Preview Mode** giúp mô phỏng hiệu ứng rê chuột (`@mouseenter`, `@mouseleave`) trực tiếp trên màn hình thiết kế Editor, kết hợp công cụ Smart Auto-fill dự đoán lệnh cấu hình siêu tốc độ. Mảng UI Form và Mảng UI Hiệu Ứng coi như đã **Hoàn tất MVP**.
- 🟢 **SkaFX (Ska Expression Language) [HOÀN TẤT]:** Chốt hạ thiết kế và viết xong cỗ máy biên dịch AST (Abstract Syntax Tree) riêng biệt bằng PHP cho hệ sinh thái (Thay thế Blockly). Khớp nối hoàn hảo biến ngữ cảnh `[app.table.col]`, hàm xử lý `IF / CONCAT`, cơ chế "nuốt lỗi" (Syntax Escape) chống crash web, đục xuyên phá thành công bài toán Data Hydration (Điền data) & Conditional Render (Máy chém giao diện) ở tốc độ 0ms nhờ Universal Dynamic Binding.
- 🟢 **Ska-xi măng (Logic Engine Processor)**: Hoàn tất Lớp Nhân Đứng Giữa (Controller/Router/Băng Chuyền) với các tính năng:
  - **Mapping:** Giao diện Linear Builder (Băng chuyền nối dọc bằng Vanilla JS) mô phỏng UI kiểu n8n/Zapier. Tự động xuất chuỗi sự kiện thành mảng `JSON Graph`. (Future-proof với React Flow ở Phase 4).
  - **Processing:** Module siêu nhỏ (Strategy Pattern) rèn Data trước khi cất kho: Tạo Slug (`Ska_Slug_Processor`), Format Date (`Ska_Date_Processor`).
  - **Action:** Gửi Email (`Ska_Email_Action`), Đẩy lệnh `INSERT` và `UPDATE` dữ liệu xuống `Ska Data Pro` hoàn hảo qua Hook tách biệt ranh giới.
- 🟢 **Logic UI DB Picker: [HOÀN TẤT]** Chuyển đổi thành công giao diện TextBox/Datalist cổ điển thành một hệ thống Glassmorphism Modal UI cao cấp với khả năng Real-time Search. Tích hợp thuật toán tự động lấy mảng dữ liệu `ska_data_apps` để gom nhóm chính xác (Grouping) các bảng theo đúng App ID (Tránh thảm họa chia nhóm lệch bằng Regex). Mở đường kết nối Mapping tức thì.

- 🟢 **Quản Lý Băng Chuyền (Logic Manager UI): [HOÀN TẤT]** Xây dựng trang quản trị hiển thị Danh sách các Luồng Logic (List View). Cho phép người dùng trực quan Thêm Mới, Xóa, Đổi Tên các Luồng an toàn theo phong cách Dual-View.
- 🟢 **Hoàn thiện UX Linear Builder: [HOÀN TẤT]** Loại bỏ hành vi Enter tự reload form gây gãy Chrome Extensions. Dịch chuyển luồng bằng DOM Swap 100%.
- 🟢 **Băng Chuyền Dữ Liệu Không Rác (Native JSON Format): [HOÀN TẤT]** Lập cấu trúc phòng thủ (Defensive Array Casting) kết hợp với màn "Pivot Architecture" chuyển đổi toàn diện cột dữ liệu Multiselect/Relation sang cấu trúc Native JSON thay vì CSV. Giải quyết rốt ráo bài toán Frontend đệ trình Payload Array xuống thẳng trái tim Flat Table mà không cần xử lý chuỗi trung gian.

### 3.3. Phase 4: Frontend Ecosystem (UI/React/Canvas)
- **THÔNG BÁO NIÊM PHONG:** Toàn bộ các hạng mục về Tầng hiển thị (Query Loop, Theme Builder, Custom Blocks, App Portals, Dark Mode) đã chính thức được bóc tách và chuyển giao sang một File Quản trị độc lập: `project_manager_phase4.md`.
- File **Phase 3** này chính thức bị niêm phong tại đây. Mọi sự ủy quyền thuộc về Phase 4.
