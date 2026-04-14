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
- [ ] Bổ sung control chọn Semantic Tag vào Ska Container (`div`, `section`, `header`, `footer`, `nav`, `aside`, `form`).
- [ ] Đảm bảo Container tương thích ngược (fallback `div` mặc định) và giao diện UI rõ ràng khi có thay đổi Semantics.

### 2.2. Xây Dựng Ska HTML Attributes Panel
- [ ] Tạo Component React `HTMLAttributesPanel` (Repeater: Key - Value) dùng chung cho mọi block lõi (Tiêm qua Filter Hook).
- [ ] Có thể dọn dẹp biến `customStyle` cũ nếu bị chồng chéo, hoặc giữ làm panel riêng.
- [ ] Cập nhật chuỗi `render.php` của Atomic blocks để tự in nội dung `htmlAttributes` vào Frontend (Vd: render `$wrapper_attributes` + attribute tự do). 
- [ ] Tích hợp phát hiện Alpine: Nếu có key dùng Alpine (`x-data`, `x-init`...) tự động enqueue script Alpine.js ở Frontend.

### 2.3. Cải Tiến Bridge Parser (html2tailwind)
- [ ] Nâng cấp `html-to-blocks.js` (Bridge Parser): Khi copy HTML có `<form>`, tự động parser qua block `Ska Container` và bóc tách Action/Method thành Key-Value thuộc tính.
- [ ] Nhận diện tự động toàn bộ thuộc tính Alpine (`x-data`, `x-show`, `@click`...) và `aria-*` từ HTML được copy vào `HTMLAttributesPanel` để chuyển đổi mượt mà các Components của Tailwind UI.
