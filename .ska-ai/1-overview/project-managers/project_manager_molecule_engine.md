# PROJECT MANAGER: SKA MOLECULE & ALPINE.JS INTEGRATION
@status: 🟢 Active | @last_update: 2026-04-16 | @context: Phát triển thư viện Ska Molecules (Block Variations) bằng Alpine.js & Tailwind

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Ska Molecule Architecture:** Xây dựng lớp kiến trúc trung gian (Smart Container) để bọc các nguyên tử Atomic Blocks.
- **Universal Container:** Hợp nhất `ska-container`, Form, Modal container thành một khối duy nhất có khả năng "morphing" thẻ Semantic Tag.
- **Global Key-Value Attributes:** Khai thác Alpine.js làm động cơ Front-end, siêu nhẹ (<15KB) và zero-overhead.
- **Block Variations:** Cung cấp sẵn các Pattern được dựng sẵn (Molecules) dưới dạng native WordPress Block Variations để người dùng không cần setup thủ công từng class Tailwind, hay từng Alpine directive.

---

## 2. ROADMAP PHASE 3: NỀN TẢNG ALPINE & ATTRIBUTES (HOÀN THÀNH)
- [x] Tạo `HTMLAttributesPanel` cho toàn bộ các block lõi.
- [x] Nâng cấp `render.php` cho phép đẩy các attribute ra thẻ wrapper `<div>`.
- [x] Tự động enqueue `alpine.min.js` khi có `x-data`, `x-show` thông qua Server-Side Filtering (zero-overhead).
- [x] Hủy ý tưởng gọi Fetch API thủ công -> Ra mắt AJAX Form tự động.

---

## 3. ROADMAP PHASE 4: THƯ VIỆN SKA MOLECULES
*Xây dựng các block tổ hợp phức tạp, dựng sẵn cho người dùng Nocode (Khai báo bằng `registerBlockVariation`).*

### 3.1. Navigation & Display (Tương Tác Nội Dung)
- [x] **Ska Tabs:** Chuyển đổi nội dung theo cơ chế Tab. Trang bị chuẩn `x-data="{ activeTab: 'tab1' }"`.
- [x] **Ska Accordion (FAQ):** Mở rộng/Đóng nếp gấp nội dung. Trang bị mảng đóng mở và icon mũi tên xoay.

### 3.2. Overlay & Popup (Đang Brainstorm)
- [x] **Ska Modal / Popup:** (ĐÃ LOẠI BỎ) - Chuyển sang phương án sử dụng **Ska Smart Object (Global Template)** để tránh xung đột HTML2Tailwind.
- [ ] **Ska Offcanvas / Mobile Menu:** Sidebar trượt ngang từ một phía màn hình.

### 3.3. Advanced Functional Blocks
- [x] **Ska Standard Form:** 
  - Đã triển khai Form mẫu tiêu chuẩn với đầy đủ các trường (Text, Date, Number, Multi-Select, Checkbox) và Button gửi.
  - Áp dụng cấu trúc mở (không khóa templateLock) ở form để user tùy biến tự do.
- [x]*Ska Dropdown Menu:** Menu sổ xuống dùng kiểu `x-data="{ dropOpen: false }"`.
- [x] **Ska Filter Gallery:** Bộ lọc hình ảnh/danh sách theo tab (ví dụ chọn All, UI, UX, Web sẽ filter ra các item tương ứng).Chuyển sang phương án sử dụng **Ska Smart Object**
- [x] **Ska Countdown Timer:** Đồng hồ đếm ngược với JS tối giản định nghĩa trong `x-init`. Chuyển sang phương án sử dụng **Ska Smart Object**

## 4. CHIẾN LƯỢC KỸ THUẬT TIẾP THEO
1. Nghiên cứu và xây dựng cấu trúc `Ska Multi-Step Form` mới mang tính thực tiễn cao hơn. (không làm nữa)
2. ~~Bổ sung tính năng Khóa (Block Lock)~~ -> **ĐÃ HOÀN TẤT** (Áp dụng API `templateLock: 'all'` thành công cho các block Variation như Accordion).
3. Liên kết Alpine State (Nếu cần điều khiển xuyên Block, sử dụng `Alpine.store()`).
