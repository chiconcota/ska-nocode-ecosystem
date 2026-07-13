# PROJECT MANAGER: SKAAA SYMBOLS & ORGANISMS (REACT UI)
@status: 🟢 Done | @target: Phase 4.1 UI Component | @context: Tầng React Gutenberg cho việc lưu trữ và kết xuất Block tái sử dụng.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- Xây dựng tính năng biến bất kỳ Block nào trên giao diện (ví dụ: Header, Hero Section, Card) thành một "Skaaa Organism / Symbol".
- Khắc phục các hạn chế nặng nề của Reusable Block mặc định trong WordPress bằng cơ chế định danh Ref-ID gắn kết chặt chẽ vào hệ thống Data Engine của Skaaa.
- Lưu trữ trực tiếp mã nguồn (HTML/JSON/Attributes) xuống hệ thống bảng phẳng của Skaaa Data Pro (`skaaa_data_sys_organisms`), hoàn toàn loại trừ sự phụ thuộc vào Postmeta.

---

## 2. ROADMAP TRIỂN KHAI (TASKS)

### 2.1. Tầng Hạ Tầng Hệ Thống (Mạch Máu - Đã Hoàn Tất)
- [x] Khởi tạo phân vùng App `skaaa_system` (nhãn "Site Management") trong `App_Manager` và chặn quyền can thiệp thay đổi/xoá Cấu trúc App.
- [x] Mồi và tạo tự động 3 Bảng Hệ Thống (`skaaa_data_sys_organisms`, `skaaa_data_sys_theme_templates`, `skaaa_data_sys_presets`) thông qua hook migration/setup của Skaaa Data Pro.
- [x] Tuỳ biến `skaaa_data_dictionary` để hiển thị nhãn UI thuần Việt ("Organisms Blocks", "Theme Templates", "Design Tokens").
- [x] Kích hoạt hệ thống Cache thông minh (Zero-Query): Bất cứ chức năng Update/Delete/Insert nào diễn ra, hệ thống tự động ghi đè file `.json/.php` fallback giúp Frontend truy xuất dữ liệu cực tốc độ.
- [x] Gắn shortcut "Site Blueprint" tại menu Skaaa Builder Core trỏ sang khu vực Schema Manager UI của `skaaa_system`.

### 2.2. Tầng Tương Tác Gutenberg / React UI (Đang Làm Việc)
- [x] **UI Button 'Save as Organism':** Tạo React Component (Toolbar Button hoặc Inspector Panel) hiển thị trên các Block được chọn trong Skaaa Builder.
- [x] **Luồng POST Data (Save):** Viết logic thu thập Nội dung (HTML/JSON Attributes) của khối hiện hành và gọi REST API (hoặc Ajax) để `INSERT` thẳng xuống bảng phẳng `skaaa_data_sys_organisms` kèm theo `organisms.json` Cache.
- [x] **Block Transformation (Ref-ID Ghosting):** Ngay sau khi Lưu thành công, Block đang chọn sẽ tự động được thay đổi định dạng (Transformed) thành một Khối Ref rỗng (Ghost Block) chỉ giữ đúng một ID duy nhất: `{{organism_id}}`. Phần Renderer của PHP sẽ dựa vào ID này để bốc ngược dữ liệu từ JSON Cache File để đắp vào FrontEnd.
- [x] **ServerSideRender Fix (2026-04-21):** Sửa lỗi `echo output` của hàm PHP render block do tính năng quét của WP REST API và output buffering của WP > 6.1.
- [x] **Tab Thư Viện Inserter (+):** Mở rộng bảng chọn Block Menu (+) mặc định của Gutenberg. Cấp lệnh Fetch danh sách các Organisms đã bọc sẵn Cache để tạo ra Nhóm "Skaaa Thư Viện UI" giúp người dùng lôi cấu trúc đã lưu ném ra giao diện một cách trực quan.
- [x] **Phân Rã Khối nội tuyến (Detach - Local Edit):** Đã hoàn thiện luồng Data Injection vào `window.skaaaOrganismsCache` phía Client-side sau khi Lưu mới, giúp tính năng Detach hoạt động Real-time (0ms latency, không yêu cầu Reload Trang).
- [x] **Chỉnh sửa Mẫu Gốc (Global Edit Pop-up):** Đã lập trình xong nút "Sửa Bản Gốc", mở ra Iframe (Shadow CPT) toàn màn hình cho phép thay đổi cấu trúc cốt lõi của Organism và lưu lại an toàn (chống ghi đè `name`). Đã tích hợp PostMessage để cập nhật giao diện Editor theo thời gian thực.
*(Toàn bộ phân hệ Skaaa Symbols / Organism đã hoàn thành 100%)*
---
*Ghi Chú: File PM Phụ này chỉ dùng như tấm bảng ghim nhắc việc tập trung cho luồng Frontend của tính năng Skaaa Symbols. Khi tính năng vận hành trơn tru trên môi trường Editor, người dùng có thể tùy ý xóa bỏ file.*
