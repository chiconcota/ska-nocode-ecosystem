# PROJECT MANAGER: CUSTOM BLOCKS & SYMBOLS (PHASE 4)
@status: 🔴 Pending | @last_update: 2026-04-13 | @context: Quản trị các thành phần giao diện tái sử dụng (Atomic Blocks & Patterns)

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Tái sử dụng (Reusability):** Xây dựng kho quản lý "Symbols" (Tương tự Figma/Webflow) nơi user có thể thiết kế một cụm Block (Ví dụ: Card Khách hàng, Header), lưu lại làm Symbol và ném vào bất cứ đâu. Khi sửa Symbol gốc, toàn bộ các bản sao trên web sẽ tự động thay đổi theo.
- **Tách biệt khỏi wp_block mặc định:** Xây dựng hệ thống quản lý Pattern/Block riêng bằng kiến trúc Flat Tables của `Ska Data Pro` (Ví dụ: tạo bảng `ska_data_symbols`) thay vì phụ thuộc Custom Post Type (`wp_block`) chậm chạp của WordPress.
- **Atomic Block Sync:** Phối hợp cùng Ska No-Code Design (Gutenberg/React) để làm UI kéo thả Symbol vào Editor.
- **Tích hợp tính năng AI (Ska AI Architect):** Bắt đầu triển khai Hook gửi prompt từ AI Extension Card để sinh cấu trúc Symbol tự động.

---

## 2. ROADMAP KIẾN TRÚC

### 2.1. Tầng Dữ liệu (Ska Data Pro)
- [ ] Khởi tạo Bảng vật lý `ska_data_symbols` (hoặc Cấu trúc App `ska-core-symbols`). Gồm các cột: `id`, `name`, `content` (mã JSON/HTML của Gutenberg), `category`, `status`.
- [ ] Xây dựng API (REST/Ajax) cho phép truy xuất/lưu nội dung Symbol từ Editor xuống Table mượt mà (Không dùng REST CPT).

### 2.2. Tầng Hiển Thị (Ska No-code Design)
- [ ] Bổ sung thư mục `Reusable Blocks / Symbols` vào Inserter (nút dấu +) của Gutenberg Editor.
- [ ] Viết React Block đặc biệt `<Ska_Symbol_Block id="my-symbol-id" />` để làm cầu nối Render nội dung Symbol thực tế.

### 2.3. Tầng Quản trị (Ska System Framework)
- [ ] Móc nối vào menu `Ska Ecosystem` một sub-menu mới mang tên `Kho Symbols` (Hoặc đẩy thẳng vào App Portal).
- [ ] Hiển thị List View các Symbols đã tạo (Có preview hình ảnh nếu có thể).

### 2.4. Bơm móc Ngoại vi (Phase sau)
- [ ] Extension Store: Cho phép Import (`.json`) các bộ UI Kit (Symbols/Blocks) mẫu từ Cloud hoặc máy tính.
