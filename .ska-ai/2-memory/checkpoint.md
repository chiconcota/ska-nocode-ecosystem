# CHECKPOINT (HANDOVER)
@last_update: 2026-05-04
@status: Sẵn sàng Code Implementation cho Ska Theme Builder

## 1. TÌNH TRẠNG HIỆN TẠI (CURRENT STATE)
- Kiến trúc Ska Theme Builder đã được chốt: **Smart Virtual Wrapper** kết hợp **Gutenberg as a Component Engine**. 
- Quyết định loại bỏ sự phụ thuộc vào FSE của WordPress và `wp_postmeta`. Toàn bộ thao tác lưu trữ Theme Template (Header, Footer, Single) sẽ được thực hiện trực tiếp vào `ska_data_sys_organisms`.
- Tài liệu kiến trúc `design-engine.md`, `system_map.md` và `decision-log.md` đã được cập nhật và đồng bộ 100%.
- Đã xóa bỏ file `.ska-ai/2-memory/chua-quyet-dinh-duoc.md` theo chỉ đạo dọn dẹp.

## 2. NHIỆM VỤ PHIÊN TỚI (NEXT SESSION TASKS)
1. **Thiết lập Menu Admin:** Tạo `add_menu_page` (Ska Theme Panel) trong wp-admin.
2. **Ska Theme Panel UI:** Bắt đầu code giao diện CRUD cho Template sử dụng **Alpine.js + Tailwind CSS** (tuyệt đối không dùng React cho UI quản lý này).
3. **REST API & Data Binding:** Viết API endpoint để lấy và lưu dữ liệu Template vào bảng `ska_data_sys_organisms` (Schema `theme_templates`).
4. **Isolated Editor Setup:** Thiết lập khung Iframe Editor dành riêng cho việc biên tập Template toàn màn hình.

## 3. LƯU Ý CHO AGENT (AGENT NOTES)
- Bạn đang làm việc trong phân hệ `Ska No-code Design` (`app/public/wp-content/plugins/ska-no-code-design`).
- Tuân thủ nguyên tắc Zero-Postmeta và Decoupled (Sử dụng Flat Tables).
- Không được dùng `wp_postmeta` để lưu cấu hình.
