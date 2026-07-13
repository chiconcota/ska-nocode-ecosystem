# TIẾN TRÌNH HOÀN THIỆN NODE RENDER TEMPLATE (HTML RENDER)
@status: DONE | @phase: Milestone 1 | @last_update: 2026-06-03

Tài liệu này theo dõi tiến độ phát triển và tích hợp tính năng **Pure Render Template Node** (Cách 2) trong hệ sinh thái Skaaa Logic Engine.

---

## 1. DANH SÁCH NHIỆM VỤ (TODOS)

### 🔴 A. Cấu trúc Backend (PHP)
- [x] **Refactor Class Node Render Template:**
  - File: `wp-content/plugins/skaaa-logic-engine/includes/primitives/class-skaaa-logic-render-template.php`
  - Nhiệm vụ:
    - Loại bỏ code query DB trực tiếp tới bảng `skaaa_data_sys_organisms`.
    - Viết lại phương thức `execute()` chạy bộ nội suy 2 bước (Two-Pass Interpolation) trên cấu hình `template_html`.

### 🟡 B. Giao diện Cấu hình (React Flow Settings Panel)
- [x] **Tạo UI Biên tập & Preview:**
  - File: `wp-content/plugins/skaaa-logic-engine/assets/src/builder/components/SettingsPanel.jsx`
  - Nhiệm vụ:
    - Thay thế giao diện settings cũ bằng Giao diện Thuần túy (Pure Editor UI).
    - Tạo các ô nhập: **Template HTML / Variable** và **Result Variable** (`result_var`).
    - Thiết kế phân hệ **Live Testing & Preview** (gồm Mock HTML textarea, Mock Payload JSON textarea, và Khung Preview HTML).
    - Triển khai hàm nội suy chuỗi client-side thời gian thực khi người dùng gõ.

### 🟢 C. Build & Kiểm thử (Verification)
- [x] **Biên dịch Frontend Bundles:**
  - Chạy lệnh build: `npm run build` inside `wp-content/plugins/skaaa-logic-engine/`.
- [x] **Kiểm thử Thủ công (E2E Test):**
  - Kéo thả node Render Template vào Canvas.
  - Test nhập Template động từ biến (`{{payload.popup.popup_design}}`).
  - Điền Mock HTML + Mock JSON, kiểm tra xem Preview có tự động cập nhật đúng chuẩn không.
  - Lưu và tải lại để đảm bảo cấu hình đồ thị không bị mất hoặc sai lệch.

---

## 2. NHẬT KÝ TIẾN ĐỘ (PROGRESS LOG)
- **2026-06-03:** Hoàn thành refactor backend PHP class, nâng cấp SettingsPanel.jsx (React) với Live Testing & Preview Glassmorphism, nới rộng Sidebar lên w-[400px], compile thành công bundle. Tăng version lên v1.1.6.
- **2026-06-01:** Khởi tạo tài liệu theo dõi tiến trình sau khi brain storming và thống nhất chuyển đổi sang thiết kế **Pure Render Node** (Cách 2).
