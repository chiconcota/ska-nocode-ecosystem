# PROJECT MANAGER: VISUAL TAILWIND BROWSER (HYBRID UX)
@status: COMPLETED | @phase: 3.1 | @plugin: skaaa-no-code-design

## OVERVIEW
Xây dựng tính năng Visual Tailwind Browser theo kiến trúc "Hybrid UX" (Giao diện lai). Mục tiêu tối thượng là **bảo vệ 100% trải nghiệm gõ Text nhanh/nhẹ** của Tailwind Panel cũ, đồng thời cung cấp giao diện chọn Class trực quan (On-demand) dưới dạng Popover/Drawer khi người dùng cần tra cứu hoặc click chọn nhanh các Design Tokens.

---

## 🟢 PHASE 1: PREPARATION & UI MOCKUP (COMPLETED)
**Mục tiêu:** Dọn dẹp tàn dư của kế hoạch cũ (ClassChipInput) và chuẩn bị nút bấm mở Popover.
- [x] Xóa bỏ/Vô hiệu hóa component `ClassChipInput.js` (Kế hoạch cũ rườm rà).
- [x] Cập nhật `TailwindPanel.js`: Thêm nút bấm [Visual Browser] (Kính lúp hoặc Palette icon) bên cạnh nút Copy / Save Preset.
- [x] Xây dựng state quản lý bật/tắt: `isVisualBrowserOpen`.

## 🟢 PHASE 2: STYLE DRAWER (CORE UI) (COMPLETED)
**Mục tiêu:** Xây dựng khung giao diện trượt chứa danh sách Class/Tokens.
- [x] Khởi tạo component `StyleDrawer.js` (hoặc `VisualTailwindBrowser.js`).
- [x] Thiết kế Layout: Sidebar trượt ra từ lề phải, không che lấp Editor, nhưng nghe panel của block comfig hiện tại.
- [x] Xây dựng cấu trúc Tab/Accordion để phân nhóm Tokens:
  - **Colors:** Lấy từ `skaaaDesignTokens.colors`, hiển thị dạng Swatches (ô màu).
  - **Typography:** Lấy từ `skaaaDesignTokens.typography` (Presets) và `skaaaDesignTokens.fonts` (Families).
  - **Spacing/Radius/Shadows:** Hiển thị dạng list hoặc grid.
  - **Presets:** Danh sách UI Component Presets.
- [x] Bổ sung thanh Search bằng Vanilla JS (Filter array siêu tốc, không dùng thư viện ngoài).

## 🟢 PHASE 3: DATA BINDING & HYBRID INTERACTION (COMPLETED)
**Mục tiêu:** Kết nối luồng dữ liệu 1 chiều từ Visual Browser về Text Input gốc.
- [x] Bắt sự kiện `onClick` lên từng Item (Color, Spacing, Preset) trong Visual Browser.
- [x] Khi click, tự động lấy Tailwind Class tương ứng và **Append (cộng nối) / Ghi đè** vào chuỗi text hiện tại của `TailwindPanel.js`. Đã tích hợp logic Replace All cho Presets.
- [x] Cập nhật tức thời (Update Attribute) để JIT Compiler biên dịch và Gutenberg Editor preview ngay lập tức.
- [x] Đảm bảo sau khi append, người dùng vẫn có thể bấm vào ô Text để gõ hoặc xóa tay bình thường.
- [x] Tính năng Custom Arbitrary Class Injection: Cho phép thêm custom class trực tiếp từ ô search khi không có trong list.

## 🟢 PHASE 4: E2E TEST & FINALIZATION (COMPLETED)
- [x] **Test Case 1:** Gõ tay các class bình thường, bấm space liên tục xem có mượt không (Giữ nguyên UX cũ).
- [x] **Test Case 2:** Mở Visual Browser, bấm chọn 3 màu liên tiếp, kiểm tra xem các class màu có được cộng dồn vào ô text cách nhau bằng dấu space không.
- [x] **Test Case 3:** Search một Preset trong Visual Browser, bấm chọn và kiểm tra tính năng Apply (Replace All).
- [x] **Bàn giao:** Cập nhật tài liệu, hoàn thành Phase 3. Sẵn sàng cho Phase 4 (Skaaa Molecule & Theme Builder).
