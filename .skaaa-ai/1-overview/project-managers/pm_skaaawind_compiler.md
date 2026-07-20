# PROJECT MANAGER: SKAAAWIND EDITOR JIT COMPILER
@status: 🟡 In Progress (Plan Approved) | @target_milestone: MILESTONE 2 (PHASE 6) | @last_update: 2026-07-20


> [!NOTE]
> Tài liệu này quản lý tiến độ, kiến trúc và kế hoạch triển khai của bộ biên dịch JIT client-side **`SkaaaWind JS`** thuộc plugin **Skaaa No-Code Design**. Bộ biên dịch này thay thế hoàn toàn Tailwind CDN ngoài Editor, đem lại khả năng lập trình offline 100%, khắc phục triệt để các lỗi bất đồng bộ hiển thị (Parity) và specificity giữa Editor và Frontend.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Hoạt động Offline 100% (Zero-Network JIT):** Khai tử hoàn toàn liên kết tới `https://cdn.tailwindcss.com` bên trong Gutenberg Editor, đảm bảo môi trường phát triển local hoạt động mượt mà không cần internet.
- **Đồng bộ logic JIT tuyệt đối (Absolute Compiler Parity):** Port toàn bộ cấu trúc map class, regex và quy tắc phân giải màu sắc, kích thước, bo góc, khoảng cách từ `Tailwind_Compiler` và `Tailwind_Config` bên PHP sang Javascript.
- **Độ ưu tiên cao (CSS Specificity Parity):** Sử dụng chung cơ chế bọc selector `html body.skaaaaa-builder` / `.editor-styles-wrapper` kết hợp Double Classing (`.class.class`) để đè CSS Theme WP mà không dùng `!important` bừa bãi.
- **Lắng nghe phản ứng (Reactive Editor Compilation):** Theo dõi thay đổi trạng thái block tree của Gutenberg bằng `wp.data.subscribe`, tự động biên dịch lại khi người dùng gõ class mới và cập nhật tức thời stylesheet trong editor canvas.

---

## 2. KIẾN TRÚC & PHÂN CHIA TRÁCH NHIỆM

### A. Nhân biên dịch SkaaaWind (Core Compiler Module)
- Xây dựng lớp độc lập `SkaaaWindCompiler` bằng Vanilla JS (ES6 Class) không phụ thuộc WordPress để có thể đóng gói độc lập.
- **Dữ liệu cấu hình:** Nạp bảng màu Brand Colors động từ `window.skaaaEditorConfig.brandColorsJson` làm Single Source of Truth (SSOT).
- **Trình phân tích Modifier:** Hỗ trợ tách responsive (`sm:`, `md:`, `lg:`...), trạng thái chuột/focus (`hover:`, `focus:`, `group-hover:`...) và dark mode (`dark:`).
- **Bộ tạo Selector:** Sinh CSS với hai dòng rules song song cho Frontend và Editor Preview.

### B. Bộ lắng nghe và điều phối Editor (Integration Layer)
- Tích hợp bên trong file `/assets/js/skaaa-editor-helper.js`.
- **Gutenberg Listener:** Đăng ký hàm subscribe theo dõi store `core/block-editor`. Quét đệ quy toàn bộ block tree qua `getBlocks()` thu thập thuộc tính `tailwindClasses` và `className`.
- **Iframe CSS Injector:** Quét các Editor Iframes (`iframe[name="editor-canvas"]`), tự động cập nhật thẻ `<style id="skaaawind-compiled-css">` bằng CSS mới biên dịch.
- **Skaaapine Store Hook:** Tận dụng `SkaaapineStore` để lắng nghe sự thay đổi của các biến môi trường (như Dark Mode `skaaaTheme`) để thay đổi class `.dark` trên document root của iframe đồng bộ với CSS biên dịch.

---

## 3. KẾ HOẠCH HÀNH ĐỘNG (ACTION ITEMS)

- [ ] **Phase 1: Porting mã nguồn JIT Compiler sang JS**
  - [ ] Khởi tạo file [skaaawind.js](file:///home/chiconcota/Local%20Sites/skaaa-no-code-ecosystem/app/public/wp-content/plugins/skaaa-no-code-design/assets/js/skaaawind.js).
  - [ ] Port đầy đủ config maps từ PHP `Tailwind_Config`.
  - [ ] Viết hàm `resolveClass` khớp regex màu sắc, spacing, dimension, border, transition.
  - [ ] Tích hợp nạp `skaaaEditorConfig.brandColorsJson` vào bộ dịch màu tùy chỉnh.

- [ ] **Phase 2: Tích hợp Gutenberg Store & Subscriber**
  - [ ] Đăng ký script `skaaawind.js` làm dependency của `skaaa-editor-helper` trong [class-core.php](file:///home/chiconcota/Local%20Sites/skaaa-no-code-ecosystem/app/public/wp-content/plugins/skaaa-no-code-design/inc/design-engine/class-core.php).
  - [ ] Viết bộ lọc/quét đệ quy `getBlocks()` trong helper để lấy danh sách class từ Gutenberg.
  - [ ] Áp dụng cơ chế hash kiểm tra chuỗi class để tránh biên dịch lặp lại vô ích (Performance optimization).

- [ ] **Phase 3: Bơm Stylesheet đa ngữ cảnh (Iframe Support)**
  - [ ] Loại bỏ đoạn code tiêm script CDN tailwindcss trong `skaaa-editor-helper.js`.
  - [ ] Viết hàm `updateEditorStylesheets(css)` tự động tìm và cập nhật thẻ style trong cả main document lẫn các iframe editor canvas.

- [ ] **Phase 4: Tích Hợp Skaaapine & Live Preview**
  - [ ] Móc nối với sự kiện thay đổi của `SkaaapineStore` để bắt được các cập nhật state cục bộ.
  - [ ] Kiểm thử luồng đổi màu tối (Dark Mode) hoạt động mượt mà thời gian thực trong Iframe Editor.

- [ ] **Phase 5: Kiểm thử Độ tương thích (Parity Testing)**
  - [ ] So sánh CSS được biên dịch bởi PHP (ở frontend) và SkaaaWind JS (trong editor) cho 50 class Tailwind phổ dụng.
  - [ ] Xác nhận không có hiện tượng bể layout hoặc lệch màu sắc giữa 2 môi trường.

---

## 4. TIÊU CHÍ NGHIỆM THU (ACCEPTANCE CRITERIA)
1. Gutenberg Editor không còn gọi ra CDN ngoài `tailwindcss.com` (Kiểm tra Tab Network trong DevTools).
2. Khi thêm/sửa class Tailwind trong Inspector, khối block tương ứng trên Editor preview cập nhật style ngay lập tức.
3. Dark Mode bật/tắt qua Alpine Action trong Editor phản hồi tức thì và chính xác màu của các class `dark:bg-*`.
4. Độ ưu tiên CSS (Specificity) của style biên dịch trên Editor đủ mạnh để đè các class mặc định của theme WordPress (sử dụng Double Classing).
