# PROJECT MANAGER: SKAAA LINK ENGINE
@status: MILESTONES 1, 2 & 3 COMPLETED | @phase: 4.5 (Interruption) | @focus: Dynamic Links & Hyperlink Architecture

## 1. TỔNG QUAN (OVERVIEW)
Skaaa Link Engine là hệ thống cho phép chèn liên kết (Links) vào các phần tử giao diện của Skaaa Builder. Để đảm bảo trải nghiệm No-code toàn diện và hỗ trợ kiến trúc dữ liệu động (Dynamic Data), hệ thống Link được chia làm 2 cơ chế hoạt động song song:
1. **Block-Level Link (Tại Inspector Panel):** Biến toàn bộ một khối (Image, Container, Button) thành siêu liên kết.
2. **Inline Dynamic Link (Tại Toolbar):** Bôi đen văn bản trong khối Text và gán siêu liên kết.

Hệ thống bắt buộc phải hỗ trợ **Dynamic Link**, cho phép lấy URL từ Dữ liệu Hệ thống (Home URL, Current Post) hoặc Dữ liệu Vòng lặp (Loop Fields) thông qua cơ chế Hydration bằng Regex của Skaaa Loop (`{{field_name}}`).

---

## 2. KIẾN TRÚC LÕI (CORE ARCHITECTURE)
- **JSON Standard:** Mọi block tĩnh đều có chung một cấu trúc Attribute `link`:
  ```json
  "link": {
      "type": "object",
      "default": { "url": "", "target": "_self", "dynamic": { "source": "static", "key": "" } }
  }
  ```
- **UI Components (React):** 
  - `SkaaaLinkControl`: Dùng cho Block-level (Panel).
  - `SkaaaInlineLinkFormat`: Dùng cho Inline-level (Toolbar - Format Type).
- **Backend Resolution (PHP):** Lớp `\Skaaa\Builder\Utils\Dynamic_Data` sẽ phân giải thuộc tính `link` trước khi render ra HTML. Nếu là dữ liệu Loop, trả về cú pháp Mustache `{{key}}` để hệ thống Skaaa Loop tự động Hydrate.

---

## 3. LỘ TRÌNH TRIỂN KHAI (MILESTONES & TASKS)

### Milestone 1: Core Backend & UI Component (Block-Level)
*Xây dựng tiện ích phân giải PHP và giao diện Inspector React dùng chung cho các Block.*
- [x] Tạo file `inc/utils/class-dynamic-data.php` chứa hàm `resolve_dynamic_link()` và `get_dynamic_content()`.
- [x] Import `Dynamic_Data` vào `skaaa-no-code-design.php`.
- [x] Xây dựng Component React `SkaaaLinkControl.js` (Ô nhập URL, Nút Toggle Target, Nút bật/tắt chế độ Dynamic, Dropdown chọn Nguồn dữ liệu).

### Milestone 2: Tích hợp Block-Level Link vào Core Blocks
*Áp dụng `SkaaaLinkControl` vào Panel và xử lý thẻ HTML đầu ra cho các Block cốt lõi.*
- [x] **Skaaa Image:** Update `block.json`, thêm Panel, update `render.php` bọc thẻ `<a>` ngoài thẻ `<img>`.
- [x] **Skaaa Button:** Update `block.json`, thêm Panel, update `render.php` đổi `<button>` thành `<a>`.
- [x] **Skaaa Container:** Update `block.json`, thêm Panel, update `render.php` đổi `tagName` thành `<a>` nếu có URL.

### Milestone 3: Inline Dynamic Link (Toolbar Format)
*Mở rộng thanh Toolbar của RichText để chèn link động vào văn bản.*
- [x] Khởi tạo Custom Format Type `skaaa/dynamic-link` bằng `registerFormatType` tại `extensions/html-attributes.js`.
- [x] Xây dựng UI Popover cho nút Link trên Toolbar sử dụng lại `SkaaaLinkControl`.
- [x] Thiết lập Parser `resolve_inline_links()` tại `Dynamic_Data` để dịch các tag `<a>` sinh ra từ format thành thẻ link thực tại SSR.
- [x] Cập nhật các block (vd: `skaaa-text`, `skaaa-list-item`) để chạy hàm nội suy inline.

### Milestone 4: Kiểm thử (E2E Testing)
*Đảm bảo hệ thống Link tương thích hoàn toàn với Skaaa Loop.*
- [x] Test Static Link trên Image/Container.
- [x] Test System Dynamic Link (Home URL) trên Button.
- [x] Test Loop Dynamic Link (Gắn URL vào biến vòng lặp) trên Inline Text và Block-Level Container.

---

## 4. QUY TẮC PHÁT TRIỂN (DEVELOPMENT RULES)
1. **DRY (Don't Repeat Yourself):** Dùng chung component `SkaaaLinkControl` cho mọi Block. Không code lặp lại UI Link ở từng block.
2. **SEO Optimization:** Luôn xuất ra thẻ `<a href="...">` thật ở Frontend (SSR), tuyệt đối không dùng JavaScript `onclick` để chuyển trang nhằm đảm bảo Bot Google đọc được link.
3. **Decoupled Hydration:** Để Dynamic Link tương thích với Skaaa Loop, PHP Render Engine chỉ cần xuất ra href chứa `{{tên_biến}}`, hệ thống Loop sẽ tự động bắt regex và bơm dữ liệu.

---
**[Cập nhật gần nhất]**: Hoàn thành Milestone 1, 2 và 3. Hệ thống Link Engine (Block-Level và Inline Dynamic Link) đã được tích hợp đầy đủ. Sẵn sàng cho kiểm thử E2E (Milestone 4).
