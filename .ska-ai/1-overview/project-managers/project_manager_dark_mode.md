# PROJECT MANAGER: PHASE 4 - DARK MODE THƯỢNG TẦNG (DESIGN ENGINE)
@status: 🟡 TODO | @last_update: 2026-05-13 | @context: Master Roadmap cho việc triển khai Dark Mode toàn diện bằng Tailwind JIT & Alpine.js

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Tích hợp Dark Mode Native:** Kích hoạt chức năng Dark Mode (`darkMode: 'class'`) ở mức độ Compiler và Frontend để người dùng No-code có thể chuyển đổi giao diện dễ dàng.
- **Bảo toàn Design Tokens:** Đảm bảo toàn bộ các màu cấu hình từ Ska Design Tokens (Light Mode) tự động tương thích và có thể mapping sang Dark Mode Palette mà không bị hardcode.
- **Trải nghiệm Frontend (Zero-Latency):** Quản lý trạng thái Dark Mode bằng Alpine.js và Local Storage để tránh tình trạng chớp màn hình (FOUC) khi tải trang.

## 2. ROADMAP THEO HẠNG MỤC (PHASE 4 - DARK MODE TRACKER)

### 2.1. Nâng cấp Ska JIT Compiler (Backend)
- [x] Bổ sung cấu hình `darkMode: 'class'` vào hàm sinh `tailwind.config` của lõi JIT Compiler (`class-core.php` / `class-style-manager.php`).
- [x] Nâng cấp Regex hoặc Lexer của JIT Compiler để cho phép nhận diện và biên dịch các class có tiền tố `dark:` (ví dụ: `dark:bg-slate-900`, `dark:text-white`).
- [x] Kiểm tra cơ chế `Class Doubling` và `Specificity` để đảm bảo class `dark:...` có quyền ưu tiên cao hơn class thường khi thẻ `<html>` hoặc `<body>` có chứa class `dark`.

### 2.2. Xây dựng Dark Mode Switcher (Ska Molecule & Alpine.js)
- [x] Phát triển một UI Component (Ska Molecule) "Dark Mode Toggle / Switcher" để kéo thả trực tiếp vào Header/Navigation.
- [x] Tích hợp Alpine.js State (`Alpine.store('theme')` hoặc `x-data`) vào nút Switcher để xử lý thao tác bật/tắt.
- [x] Viết logic tự động đính class `dark` vào thẻ gốc `document.documentElement` (`<html>`) khi trạng thái là Dark Mode.

### 2.3. Lưu trữ Trạng thái (Storage & FOUC Prevention)
- [x] Lưu trạng thái Light/Dark cuối cùng của người dùng vào trình duyệt thông qua `localStorage`.
- [x] Triển khai một đoạn Script Inline siêu nhẹ gắn ở `<head>` để đọc `localStorage` và áp dụng class `dark` ngay lập tức (trước khi DOM render xong) nhằm ngăn chặn hiện tượng FOUC (Flash of Unstyled Content).

### 2.4. Khả năng Tương thích với Design Tokens
- [ ] Bổ sung giao diện vào `Ska System Dashboard -> Theme Options` để người dùng có thể chọn màu riêng biệt cho chế độ Dark Mode (Ví dụ: `Dark Primary`, `Dark Background`).
- [ ] Ánh xạ (Map) các giá trị Token này thành CSS Variables với tiền tố `--ska-sys-color-dark-*` và cập nhật JIT Registry.

## 3. CÁC QUY TẮC BẢO VỆ (CONSTRAINTS)
- ⚠️ **Zero-Latency Toggle:** Thao tác đổi chế độ Đen/Sáng phải là Instant (Tức thì) sử dụng Client-side JS, TUYỆT ĐỐI KHÔNG reload trang hoặc gọi AJAX về backend.
- ⚠️ **Zero-Trash Policy:** Switcher block không được sinh ra rác DOM. Tuân thủ `[class*='wp-block-ska-builder']` và không gây ảnh hưởng đến layout Flex/Grid chung.
