# PROJECT MANAGER: DESIGN SYSTEM & VISUAL TAILWIND BROWSER
@status: PLANNING | @phase: 4.4 | @plugin: ska-no-code-design & ska-data-pro

## OVERVIEW
Xây dựng lại toàn bộ kiến trúc Design Engine theo tiêu chuẩn chuyên nghiệp với 3 lớp (Lấy cảm hứng từ Metric Flow Design System). Giải quyết dứt điểm lỗi lưu trữ JSON Blob và mang lại trải nghiệm No-code minh bạch (WYSIWYG) qua cơ chế Preset Expansion.

---

## 🟢 PHASE 1: DATABASE & STORAGE REFACTOR (LỚP 1)
**Mục tiêu:** Dọn dẹp cục JSON Blob và chuyển đổi `ska_data_sys_presets` thành chuẩn Flat Table (1 Row = 1 Entity).
- [x] Tái cấu trúc Schema Manager để hỗ trợ Enum Type: `token_color`, `token_font`, `token_spacing`, `token_radius`, `token_shadow`, `preset_typography`, `preset_component`.
- [x] Xóa bỏ code cũ tạo JSON Blob bị lặp dòng (Duplicate Rows). Viết lại Logic CRUD chuẩn cho 1 Entity/Row.
- [x] Xây dựng Hook `ska_after_token_saved` để tự động compile Database thành file cache tĩnh `wp-content/uploads/ska-builder/tokens.json`.

## 🟡 PHASE 2: DESIGN SYSTEM DASHBOARD (LỚP 2)
**Mục tiêu:** Đập bỏ trang Admin 3 Tab lộn xộn cũ (`ska-design-tokens`), xây dựng mới giao diện Single-Page quản trị tập trung toàn bộ Local Variables.
- [x] Khởi tạo giao diện khung sườn Single-Page (Sử dụng React/Alpine.js).
- [x] **Section 1: Colors:** Quản lý mã màu (Color Picker, Swatches UI).
- [x] **Section 2: Typography:** Quản lý Font gốc (`token_font`) và Text Styles (`preset_typography`).
- [x] **Section 3: Spacing & Effects:** Quản lý khoảng cách (Spacing Scales), Bo góc (Radius), và Đổ bóng (Shadows).
- [x] **Section 4: UI Presets:** Quản lý danh sách các Presets. Hỗ trợ Edit tên, sửa chuỗi class, Delete.

## 🟡 PHASE 3: VISUAL TAILWIND BROWSER & JIT SYNC (LỚP 3)
**Mục tiêu:** Nâng cấp Inspector UI trên Gutenberg bằng Class Chips và Khung trượt Popover, loại bỏ sự rườm rà của việc nhập liệu text thủ công.
- [ ] **UI Component - `<ClassChipInput />`:**
  - Chuyển chuỗi text thành các khối Chips độc lập có nút `[x]`.
  - Bắt sự kiện `onPaste` để tự động chia tách chuỗi dài thành nhiều Chips.
  - Tính năng "Copy All": Trích xuất Chips thành chuỗi string lưu vào Clipboard.
  - Tính năng "Save as Preset": Mở Dialog nhập tên -> Gửi API lưu vào Data Pro -> Gom các Chips thành Preset tạm thời (Để bung ra dùng cho các nơi khác).
- [ ] **UI Component - `<StylePopoverDrawer />`:**
  - Khung Menu lơ lửng bên phải Inspector (Không che Editor).
  - Thanh tìm kiếm trung tâm bằng **Vanilla JS Search** (Siêu tốc, Zero-dependency).
  - Cấu trúc thư viện Tailwind chuẩn bị theo hướng **Lazy Load** (Click mở dần các cấp: Nhóm -> Phân loại con -> Lưới hiển thị) để chống phình DOM.
- [ ] **Tương tác Frontend:** Click chọn Class/Preset từ Popover -> Lệnh `Update Attribute` được bắn ra -> Canvas Editor cập nhật tức thì nhờ bộ Ska JIT Compiler.

## 🟣 PHASE 4: E2E TEST & HANDOVER (LỚP KIỂM THỬ)
**Mục tiêu:** Đảm bảo toàn bộ luồng từ lúc thiết lập Token ở Dashboard đến lúc JIT Compile ra CSS ở Editor hoạt động trơn tru 100%.
- [ ] **Test Case 1 (Database -> JSON):** Thêm một biến màu Primary mới ở Dashboard, kiểm tra DB ghi nhận dòng mới (Type=`token_color`) và file `tokens.json` được cập nhật.
- [ ] **Test Case 2 (Visual Browser Sync):** Mở Editor, mở Popover, xác nhận biến màu Primary vừa tạo đã xuất hiện. Click chọn màu đó để áp dụng lên khối Button, xác nhận JIT compile đổi màu ngay tức thời.
- [ ] **Test Case 3 (Class Chips & Save Preset):** Copy-paste một chuỗi 5 classes vào ô nhập liệu để test tính năng tách Chips. Bấm "Save as Preset" và kiểm tra Preset có được lưu về DB và xuất hiện trong menu Popover hay không.
- [ ] **Bàn giao (Handover):** Hoàn thiện test, dọn dẹp code rác. Cập nhật lại toàn bộ `system_map.md`, `design-engine.md`, và `decision-log.md` theo bộ luật Zero-Trash Directive. Gọi quy trình `/end_session` để đóng phase.

---
## 📝 KIẾN TRÚC QUAN TRỌNG (ARCHITECTURAL DECISIONS)
1. **Preset Expansion:** Các Preset (UI Component / Typography) khi được dùng sẽ nổ ra (expand) thành các Chips độc lập để giữ nguyên sức mạnh Override của Tailwind. Không dùng Semantic CSS Classes.
2. **SSOT tại Organism:** Chức năng đồng bộ giao diện toàn trang sẽ do Ska Organism đảm nhiệm, không phải do CSS Preset.
3. **No Fuse.js & No Hover Preview:** Lược bỏ thư viện phụ và loại bỏ luồng hover phức tạp để ưu tiên 100% Performance. Click to Apply.
