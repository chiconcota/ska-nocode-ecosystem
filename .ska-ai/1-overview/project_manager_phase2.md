# PROJECT MANAGER: PHASE 2 (SKA DATA PRO) & TẦM NHÌN HỆ SINH THÁI
@status: 🔴 Kế hoạch Đã duyệt | @last_update: 2026-03-29 | @context: Brainstorming Session Log

---

## 1. TÓM TẮT KIẾN TRÚC LÕI (THE CORE RULES)
- **Tôn chỉ "Airtable-like Ux":** Giấu hoàn toàn các khái niệm Database (SQL, Hook, dbDelta) xuống tầng ngầm. Giao diện quản lý phải 100% thân thiện kiểu Spreadsheet / No-code.
- **Micro-Ecosystem (Quy tắc Cách ly):** Giao tiếp giữa các module phải đi qua `WP Hooks`. Data Pro vẽ giao diện Admin bằng tự nhúng CDN, tránh "ký sinh" vào thư viện của Design Engine.
- **Flat Table First (Nhưng không cực đoan):** Dữ liệu ứng dụng lưu toàn bộ vào bảng phẳng `ska_data_*`. Nhưng tài khoản hội viên/mật khẩu thì nối (Foreign Key) với bảng `wp_users` chuẩn của WordPress.

---

## 2. ROADMAP PHASE 2: SKA DATA PRO (DATABASE ENGINE)
Phase 2 chịu trách nhiệm đúc "Thùng chứa" (Database) và thiết lập hệ thống truy xuất dữ liệu (Query).

### 2.1. Template Gallery & Schema Manager (Đang làm)
- Màn hình chọn Mẫu dữ liệu (Data Templates: E-Commerce, LMS, Booking...)
- Kịch bản: Bấm chọn Mẫu -> Chạy SQL ngầm -> Tạo bảng `ska_data_*` -> Bơm dữ liệu giả (Dummy Data) -> Hiện giao diện quản lý dạng Lưới (Grid).
- **Task Hiện tại:** Xây dựng cục xử lý AJAX và Class Database (`dbDelta`).

### 2.2. Query Builder & Cỗ Máy Nội Suy (Backend Dynamic Content)
- Xây dựng Cỗ máy đọc dữ liệu thông qua Hook `apply_filters('ska_data_query', ...)`.
- Mở khóa tính năng gõ tay `{{product_price}}` vào khối Ska Text để nó tự đổi thành giá tiền trên Frontend.

### 2.3. Data Providers / Thích Ứng Mở Rộng
- Xây dựng cơ chế Adapter để kéo dữ liệu từ nhiều Nguồn: 
  - Nguồn nội bộ: `Ska Native Flat Tables`
  - Nguồn Core WP: `WP Posts`, `WP Users`
  - Nguồn Ngoại lai: `WooCommerce` (Không copy data Woo qua bảng Ska, mà dùng Adapter để đọc trực tiếp bằng `wc_get_products`).

---

## 3. ROADMAP TƯƠNG LAI BẮT CẦU (BEYOND PHASE 2)

### 3.1. Nâng cấp Phase 2.5: Dynamic Tag UI (Frontend Picker)
- Việc: Tạo cái nút Lấp lánh (Sparkle Icon) trên trình soạn thảo Gutenberg của Ska Builder.
- Tác dụng: Cho phép người dùng chọn thẳng Fields (Cột: Giá, Tên) từ một cái Dropdown thay vì phải học thuộc lòng cú pháp `{{tag}}`.

### 3.2. Phase 3: Ska Logic Engine & Khối Form (The Flow)
- Đúc các khối nhập liệu: `<Input>`, `<Select>`, `<Form Wrapper>`. (Các khối này nằm trong `ska-builder-core`).
- Đúc Cỗ máy bắt Sự Kiện (Trigger/Action): Trả lời câu hỏi "Submit xong thì làm gì?". (Ví dụ: `Insert Row` vào Ska Data Pro, Gửi Email báo cáo...).

### 3.3. Tính Năng Độc Lập Mọi Thời Điểm (UI Features)
- **Theme Builder** (Lắp Header, Footer từ CPT `ska_template`).
- **Custom Block / UI Symbols** (Khối tái sử dụng thiết kế).
- *Lưu ý:* Mấy tính năng này bản chất là UI/Design (Lưu cục HTML), không cần đợi Phase 3, có thể tranh thủ làm luôn ngay trong Phase 2 bất cứ khi nào rảnh rỗi.
