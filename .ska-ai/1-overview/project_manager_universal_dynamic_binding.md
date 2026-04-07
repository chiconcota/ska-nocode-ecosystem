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

- [ ] (Design & Logic) Viết React Component (Sidebar Panel) tên là `Universal Dynamic Binding`.
- [ ] Dùng `wp.hooks.addFilter('editor.BlockEdit')` chỉ bơm Panel này vào các khối khi Plugin Data & Logic được kích hoạt.
- [ ] Thiết kế tính năng **Dropdown Chọn Nguồn Dữ Liệu**:
  - Tự động call API lấy danh sách các Bảng (`ska_data_*`) và Cột tương ứng.
  - Khi user chọn (VD: Bảng Doctors -> Cột Tên), hệ thống âm thầm ghi đè biến attribute `dynamicContent` của block hoặc chèn mã JSON ẩn. (Cần thống nhất cách lưu trữ ẩn này để Không phá vỡ trải nghiệm văn bản phong phú - RichText của wp.editor).

## 🏁 PHASE 3: Kiến trúc Toggle & Động cơ Blockly (Visibility Logic)
*Thực thi "Khu Vực 2" - Phần xương xẩu nhất của dự án (Lập trình kéo thả Blockly).*

- [ ] Khởi tạo nút Toggle (Tĩnh/Động) trên Panel Sidebar. Mặc định là Tắt (Luôn luôn hiển thị).
- [ ] Khi Bật Toggle, làm ẩn Dropdown Khu vực 1 (?) (Hoặc hiển thị nút Mở Trình Soạn Thảo kế bên).
- [ ] Nhúng thư viện [Google Blockly](https://developers.google.com/blockly) vào Editor của Ska dưới dạng Modal (Z-index cao nhất).
- [ ] **Data Dictionary (Kho Khối Lego):**
  - Code định nghĩa các Khối Lego `[Parameter URL: __]`, `[User Info: __]`.
  - Code định nghĩa Khối So Sánh `[Bằng]`, `[Tồn tại]`.
- [ ] Viết hàm Compiler (Trình biên dịch JS): Chuyển đổi các khối Lego đang ghép thành chuỗi JSON rules lưu vào thuộc tính `visibilityLogic` của block.

## 🏁 PHASE 4: Bộ máy Trảm Quyết (Backend Conditional Render)
*Mảnh ghép cuối cùng - Kết nối JSON Rule sinh ra từ Blockly và bộ máy The Content.*

- [ ] Nâng cấp class `Ska_Dynamic_Content` ở Phase 1.
- [ ] Trước khi nội suy chữ, hệ thống đọc attribute `visibilityLogic` của khối.
- [ ] Chạy Parser (Đánh giá luật True/False) dựa trên ngữ cảnh (Ví dụ `$_GET['id']` có tồn tại không).
- [ ] Nếu vi phạm luật (False), kích hoạt "Máy Chém": Cắt đứt thẻ HTML đó khỏi Response gửi về cho Trình duyệt. Không dùng CSS `display:none`.

---
*Ghi chú: Bản đồ này là kim chỉ nam cho các Agents tham gia vào chu trình Code, bất kỳ thay đổi nào cần cập nhật lại tiến độ tại đây.*
