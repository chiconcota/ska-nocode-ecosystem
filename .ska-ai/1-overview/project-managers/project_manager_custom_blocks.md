# PROJECT MANAGER: SITE BLUEPRINT (ORGANISMS & THEME TEMPLATES) - PHASE 4
@status: 🟡 In Progress | @last_update: 2026-04-20 | @context: Quản trị cấu hình cốt lõi (Dogfooding) qua Ska Data Pro.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Đổi tên & Quy chuẩn:** Đã thống nhất sử dụng chuẩn Atomic Design -> Gọi Ska Symbols/Custom Blocks là **Organisms Blocks**.
- **The Dogfooding Model:** Hệ thống hoàn toàn không dùng CPT (`wp_block`). Quản lý hệ thống Theme Builder, Organisms và Global Presets bằng giao diện Flat Tables có sẵn của Ska Data Pro (DataGrid).
- **Kiến trúc App_Site (Ska_System):** Biến cấu hình giao diện thành một Smart Object hệ thống không thể xóa mang tên `ska_system`.
- **Hiệu năng cực đại (Zero JOINs & Caching):** Sử dụng các bảng phẳng. Cập nhật cơ chế Fallback Cache ghi ra file tĩnh `.json` để hỗ trợ môi trường không có Memcached.

---

## 2. ROADMAP KIẾN TRÚC & TIẾN ĐỘ THỰC THI

### 2.1. Tầng Dữ liệu (Ska Data Pro & `ska_system` Smart Object)
- [x] Khởi tạo phân vùng App `ska_system` (nhãn "Site Management") trong `App_Manager` và chặn quyền thay đổi/xoá app này.
- [x] Mồi/tạo 3 Bảng Hệ Thống (`ska_data_sys_organisms`, `ska_data_sys_theme_templates`, `ska_data_sys_presets`) tự động qua hook migration/setup của Ska Data Pro.
- [x] Tuỳ biến `ska_data_dictionary` để 3 bảng này có nhãn UI thuần Việt cực xịn ("Organisms Blocks", "Theme Templates", "Design Tokens").
- [x] Cấu hình hệ thống Cache thông minh: Bất cứ khi nào 3 bảng này bị Update/Delete/Insert, hệ thống tự động ghi lại bản Cache vào wp uploads (file `.json/.php`) giúp Frontend truy xuất cực tốc độ.

### 2.2. Tầng Hiển Thị (Ska No-code Design)
- [x] Giao diện cảnh báo ở menu tổng (Ska Ecosystem) nếu người dùng chối bỏ việc cài Ska Data Pro và Logic Engine.
- [x] Gắn shortcut "Site Blueprint" tại menu Ska Builder Core trỏ sang UI DataGrid tương ứng của `ska_system`.
- [ ] Tạo React UI cho phép user ấn nút "Save as Organism Block" gửi ném Payload (JSON/HTML) thẳng xuống CSDL flat-table `ska_data_sys_organisms`.

### 2.3. Tầng Phối Hợp (Logic Engine - Chờ Phase 5)
- [ ] (Phase 5) Khởi tạo luồng (System Workflows Mặc Định) như Form Liên hệ, Đăng nhập, Đăng ký lúc kích hoạt Logic Engine.

---

## 3. LƯU Ý KỸ THUẬT (TECHNICAL NOTES)
- Cột `json_content` ở các bảng được thiết kế là dạng `LONGTEXT`.
- Bảng `sys_theme_templates` không chứa Code giao diện, mà chứa tham chiếu (ID) đến các khối ở `sys_organisms`, giúp tuân thủ nguyên tắc DRY (Don't Repeat Yourself).
