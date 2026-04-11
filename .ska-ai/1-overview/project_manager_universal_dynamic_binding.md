# PROJECT MANAGER: UNIVERSAL DYNAMIC BINDING
@target: Ska App Builder Ecosystem
@timeline: 1-2 Tháng
@status: 🚀 Khởi chạy (2026-04-08)

Tính năng **"Universal Dynamic Binding" (Siêu Liên kết Động)** là trái tim của hệ sinh thái Ska App Builder, kết nối giữa Giao diện (Ska Design), Cơ sở dữ liệu (Ska Data Pro), và Bộ xử lý Logic (Ska Logic Engine).

Dự án này phức tạp bởi nó bao hàm cả giao diện tương tác React (Frontend Editor) lẫn bộ máy đánh chặn PHP (Backend Pipeline). Lộ trình 1-2 tháng được thiết kế thành 4 chặng (Phases) vững chắc để không gây gãy đổ (regression) cho hệ thống sẵn có.

## 🏁 PHASE 1: Động cơ Hydration Cốt lõi (Backend Engine)
*Xây dựng hệ thống bơm dữ liệu từ Database lên Frontend ở mức độ tĩnh (chữ cứng cài trong thẻ HTML).*

- [x] (Logic Engine) Tạo `class-dynamic-content.php` làm Backend Pipeline.
- [x] Xây dựng cơ chế Hook vào `the_content` (độ ưu tiên 90) để đánh chặn HTML xuất ra từ WordPress.
- [x] Lập trình thuật toán Regex `/\{\{\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*\}\}/` để bắt cấu trúc `{{tên_bảng.tên_cột}}`.
- [x] Giao tiếp với `Ska\Data\Core\Data_Fetcher` để Query data theo `$_GET['id']` từ URL. Đảm bảo có caching 1-lần-query (Memory Cache) để chống lag.
- [x] Test tay: Viết tay `{{ska_data_doctors.name}}` vào Khối Ska Text xem có nảy data từ MySQL lên không.

## 🏁 PHASE 2: Giao diện React Siêu Dữ Liệu (Static Data Provider)
*Xây dựng "Khu Vực 1" trên Editor để Lễ Tân không phải gõ tay mã {{...}} nữa.*

- [x] (Design & Logic) Viết React Component (Sidebar Panel) tên là `Universal Dynamic Binding`.
- [x] Dùng `wp.hooks.addFilter('editor.BlockEdit')` chỉ bơm Panel này vào các khối khi Plugin Data & Logic được kích hoạt.
- [x] Thiết kế tính năng **Dropdown Chọn Nguồn Dữ Liệu / Input Biểu Thức (SkaFX)**:
  - Tự động call API lấy danh sách các Bảng (`ska_data_*`) và Cột tương ứng.
  - Hỗ trợ lưu trữ theo phương thức Attribute-Driven (Sử dụng object attribute `skaDynamicBinding` trên block) kết hợp với PHP Hook `render_block` để đảm bảo WYSIWYG và không đụng chạm tới lõi content RichText.

## 🏁 PHASE 3: Ngôn ngữ biểu thức Ska (SkaFX DSL) & Giao diện Input
*Loại bỏ hoàn toàn thư viện Blockly JS nặng nề. Thay bằng ngôn ngữ SkaFX để xử lý cả Logic hiển thị lẫn Dynamic Content.*

- [x] (Logic Engine) Thiết kế kiến trúc `SkaFX_Evaluator` (AST Parser) bằng PHP. Bao gồm Trình phân mảnh (Lexer) và Trình Tính toán (Parser).
- [x] Định nghĩa cú pháp chuẩn: Biến `[table.col]`, Toán tử (`=`, `>`, `<`), Hàm `IF(...)`, `CONCAT(...)`.
- [x] (Design & Logic) Cập nhật Panel React: Thay vì Toggle Modal Blockly, cung cấp một **Ô Input "Điều kiện Hiển Thị" (Visibility Expression)**. CodeMirror Editor.
- [x] Thêm thư viện Autocomplete/IntelliSense siêu nhẹ trên Editor để tự động gợi ý biến (Ví dụ gõ `[doc...` ra `[doctors.name]`).

## 🏁 PHASE 4: Bộ máy Trảm Quyết (Backend Conditional Render)
*Mảnh ghép cuối cùng - Kết nối Chuỗi SkaFX Rule sinh ra từ Editor và bộ máy render PHP.*

- [x] Gỡ bỏ việc dùng Filter lỏng lẻo `the_content` ở Phase 1. Thay thế bằng việc móc vào filter uy lực `render_block` của WordPress. (Đã kích hoạt chạy song song).
- [x] Trước khi in thẻ HTML của block, hệ thống đọc attribute logic hiển thị.
- [x] Bơm chuỗi Rule xuống `SkaFX_Evaluator`. Cỗ máy dựa trên Context (`$_GET['id']`) để giải mã chuỗi AST.
- [x] Nếu hàm AST trả về False -> Kích hoạt "Máy Chém": Hủy xuất chuỗi (Return rỗng) để Cắt đứt thẻ HTML đó khỏi trình duyệt. KHÔNG dùng CSS `display:none`. Vượt thêm tính năng Data Hydration nếu trả về chuỗi/số.

---
*Ghi chú: Bản đồ này là kim chỉ nam cho các Agents tham gia vào chu trình Code, bất kỳ thay đổi nào cần cập nhật lại tiến độ tại đây.*
