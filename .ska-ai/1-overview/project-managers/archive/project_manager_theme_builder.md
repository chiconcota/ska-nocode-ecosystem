# PROJECT MANAGER: SKA THEME BUILDER
@status: PAUSED (Tạm dừng để phát triển Hệ thống Link) | @phase: 4.3 | @focus: Smart Virtual Wrapper & Dual-Table Architecture

## 1. TỔNG QUAN (OVERVIEW)
Ska Theme Builder là hệ thống quản trị và khởi tạo Giao diện Toàn cục (Global Templates: Header, Footer, Single, Archive, 404) của App Builder. 
Mục tiêu là **tách rời hoàn toàn khỏi FSE (Full Site Editing)** của WordPress để tránh xung đột, đạt hiệu năng cao bằng cơ chế "Dual-Table" (lưu logic và điều kiện riêng, lưu nội dung thô riêng) và sử dụng "Smart Virtual Wrapper" để render độc lập.

## 2. KIẾN TRÚC LÕI (CORE ARCHITECTURE)
- **Dual-Table Storage:**
  - `ska_data_sys_theme_templates`: Bảng quản lý siêu dữ liệu (Metadata), vị trí (Location: header, footer, single), điều kiện hiển thị (Display conditions), và trạng thái kích hoạt (Active/Inactive).
  - `ska_data_sys_organisms`: Bảng phẳng truyền thống lưu trữ nội dung thô (HTML/JSON) của Component.
- **Admin UI:** Sử dụng Alpine.js + Tailwind CSS (Không dùng React để đảm bảo tốc độ tải trang cực nhanh).
- **Frontend Rendering:** `Smart Virtual Wrapper` sử dụng hook `template_include` (priority 99) đánh chặn vòng đời của WordPress Theme.
- **Editor:** Chạy trong `Isolated Iframe` (Môi trường cách ly Gutenberg toàn màn hình).

---

## 3. LỘ TRÌNH TRIỂN KHAI (MILESTONES & TASKS)

### Milestone 1: Ska Theme Panel & Data Infrastructure
*Thiết lập hạ tầng lưu trữ và giao diện quản lý CRUD cho các Template.*
- [x] Tạo `add_menu_page` (Ska Theme Panel) trong thư mục con `inc/theme-builder/` của `ska-no-code-design`.
- [x] Code giao diện Admin Dashboard (CRUD) sử dụng Alpine.js (`workspace-panel.php` / `admin-panel.php`) & Tailwind CSS. Đảm bảo load đúng Asset.
- [x] Nâng cấp/Cập nhật API `Organisms_API` để cho phép tạo symbol/template với payload JSON rỗng (Fix lỗi validation `empty()`).
- [x] Thiết lập Schema Builder cho `ska_data_sys_theme_templates` (các cột: id, name, location, conditions, organism_id, is_active).
- [x] Xây dựng REST API Endpoint cho CRUD Theme Templates. Đảm bảo lưu đúng cơ chế Dual-Table (Tạo template -> Liên kết với 1 Organism ID).

### Milestone 2: Isolated Editor (Gutenberg Bypass)
*Xây dựng môi trường biên tập kéo thả toàn màn hình cho Template, cách ly hoàn toàn với rác CSS/JS của WP Theme hiện tại.*
- [x] Khởi tạo luồng mở Iframe Editor khi nhấn nút "Edit" trên giao diện quản lý Template.
- [x] Cấu hình CPT ảo (`ska_template_draft` hoặc tận dụng `ska_organism_draft`) trong Iframe để không ghi rác vào `wp_posts`.
- [x] Đảm bảo cơ chế PostMessage Bridge hoạt động: Lưu trong Iframe -> Cập nhật ở cha -> Đóng Iframe.
- [x] Nạp JSON Cache & Hydration tức thời cho JIT Compiler để khi lưu Template xong, Preview có thể nhận CSS ngay lập tức.

### Milestone 3: Smart Virtual Wrapper & Frontend Output
*Can thiệp vào luồng Render của WordPress để xuất HTML của App thay thế cho Theme.*
- [x] Viết bộ lọc `template_include` (Priority 99) chặn việc WordPress gọi các file `header.php`, `footer.php`, `index.php` của theme đang kích hoạt.
- [x] Xây dựng `Ska_Template_Router`: 
  - Phân tích URL hiện tại (Is Home, Is Single Post, Is Archive).
  - Truy vấn `ska_data_sys_theme_templates` lấy Template đang "Active" tương ứng.
  - Lấy HTML từ `ska_data_sys_organisms` theo `organism_id`.
- [x] Render Output bằng cấu trúc `virtual-wrapper.php` (Đảm bảo chèn đủ `wp_head()` và `wp_footer()` cho các plugin khác hít thở).

### Milestone 4: Điều kiện hiển thị nâng cao (Display Conditions)
*Hoàn thiện UI và Logic của các luật hiển thị phức tạp.*
- [x] Giao diện (Alpine.js): Form cấu hình Rule Builder (VD: Include -> Pages -> All, Exclude -> Post -> ID 15).
- [x] Backend: Xây dựng hàm `match_conditions($template_conditions)` trả về boolean kiểm tra xem Template hiện tại có hợp lệ để render ở URL/Page này không.

### Milestone 5: Kiểm thử & Bàn giao E2E (End-to-End)
*Kiểm chứng toàn diện hệ thống Ska Theme Builder từ giao diện quản trị đến kết quả Frontend.*
- [x] Xây dựng kịch bản kiểm thử (Test Cases) cho các trường hợp: tạo mới Template, chỉnh sửa nội dung trong Iframe, và cấu hình Rule.
- [x] Kiểm tra khả năng đánh chặn của `Smart Virtual Wrapper` để render Header/Footer/Single mà không ảnh hưởng tới Plugin khác.
- [x] Xác nhận luồng lưu trữ kép Dual-Table và Hydration cho JIT Compiler chạy mượt mà (Fixed CSS Injection logic during wp_head).
- [x] Viết tài liệu bàn giao `test-workflow-process.md` dành cho hệ thống Theme Builder.

---

## 4. QUY TẮC PHÁT TRIỂN (DEVELOPMENT RULES)
1. **Tuân thủ Zero-Postmeta:** Tuyệt đối không dùng `wp_insert_post` hay `update_post_meta`.
2. **Alpine.store Integration:** Mọi trạng thái UI (mở modal xoá, form tạo mới) phải đưa vào `Alpine.store('themeBuilder')`.
3. **Luôn hỏi trước khi phá Hook:** Khi can thiệp vào `template_include`, phải test kỹ với các trang không thuộc quyền quản lý của Ska Builder (VD: Trang wp-login hoặc wp-admin) để tránh crash hệ thống.

---
**[Cập nhật gần nhất]**: Đã hoàn thành toàn bộ hệ thống Ska Theme Builder, bao gồm Cấu trúc Dual-Table, Admin Dashboard, Smart Virtual Wrapper, Display Conditions, và fix thành công lỗi CSS JIT Injection ở Frontend. Dự án sẵn sàng bàn giao E2E và tiến sang Phase tiếp theo (Molecules/Components).
