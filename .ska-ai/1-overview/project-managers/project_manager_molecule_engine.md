# PROJECT MANAGER: SKA MOLECULE & ALPINE.JS INTEGRATION
@status: 🟡 In Progress | @last_update: 2026-04-14 | @context: Tích hợp Alpine.js làm nền tảng cho Smart Containers (Ska Molecule)

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Ska Molecule Architecture:** Xây dựng lớp kiến trúc trung gian (Smart Container) để bọc các nguyên tử Atomic Blocks.
- **Micro-Reactivity với Alpine.js:** Khai thác Alpine.js làm động cơ Front-end, siêu nhẹ (<15KB) và zero-overhead, chỉ nạp khi cần thiết. 
- **Universal Container:** Hợp nhất `ska-container`, Form, Modal container thành một khối duy nhất có khả năng "morphing" thẻ Semantic Tag (`div`, `form`, `section`).
- **Global Key-Value Attributes:** Tạo một Panel dùng chung cho TẤT CẢ các khối Atomic, cho phép cấu hình tham số HTML tuỳ ý (`x-data`, `x-show`, `x-bind`, `data-*`...) mà không cần phải hard-code Inspector UI cho từng trường hợp con.

---

## 2. ROADMAP PHASE 3 - STEP 4: SKA MOLECULE

### 2.1. Nâng Cấp Ska Universal Container
- [x] Bổ sung control chọn Semantic Tag vào Ska Container (`div`, `section`, `header`, `footer`, `nav`, `aside`, `form`).
- [x] Đảm bảo Container tương thích ngược (fallback `div` mặc định) và giao diện UI rõ ràng khi có thay đổi Semantics.

### 2.2. Xây Dựng Ska HTML Attributes Panel
- [x] Tạo Component React `HTMLAttributesPanel` (Repeater: Key - Value) dùng chung cho mọi block lõi (Tiêm qua Filter Hook).
- [x] Có thể dọn dẹp biến `customStyle` cũ nếu bị chồng chéo, hoặc giữ làm panel riêng.
- [x] Cập nhật chuỗi `render.php` của Atomic blocks để tự in nội dung `htmlAttributes` vào Frontend (Vd: render `$wrapper_attributes` + attribute tự do). 
- [x] Tích hợp phát hiện Alpine: Nếu có key dùng Alpine (`x-data`, `x-init`...) tự động enqueue script Alpine.js ở Frontend.

### 2.3. Cải Tiến Bridge Parser (html2tailwind)
- [x] Nâng cấp `html-to-blocks.js` (Bridge Parser): Khi copy HTML có `<form>`, tự động parser qua block `Ska Container` và bóc tách Action/Method thành Key-Value thuộc tính.
- [x] Nhận diện tự động toàn bộ thuộc tính Alpine (`x-data`, `x-show`, `@click`...) và `aria-*` từ HTML được copy vào `HTMLAttributesPanel` để chuyển đổi mượt mà các Components của Tailwind UI.
- [x] Nâng cấp JIT Compiler xử lý `transform`, `prevent default`, `escape` của Alpine.

### 2.4. Khôi Phục Ska Form Builder Cải Tiến (Pivot)
- [x] Hủy bỏ luồng yêu cầu Nocode Users cấu hình logic `fetch` API vào các thuộc tính Alpine (`x-on:click`). Đã xác nhận đây là Deal-breaker đối với trải nghiệm Nocode.
- [ ] Tái sinh và phát triển hệ thống **Ska Form Builder Cải Tiến** chuyên biệt cho tác vụ nhập liệu:
  - Tự động hóa quá trình bắt sự kiện submit và thu thập payload (dựa trên tham số `name` của input).
  - Tự động gọi API AJAX truyền dữ liệu về Logic Engine mà không ép người dùng tự cài đặt JS.
- [ ] Bảo lưu Alpine.js, nhưng giới hạn nghiêm ngặt ở phạm vi **Tương tác UI** (Hiển thị Tab, Modal, Dropdown, Menu, Animation Effect). Không can dự vào vòng đời Logic/Data CRUD.
- [ ] Debug: Xử lý hiệu ứng transition chưa mượt mà trên Form.
