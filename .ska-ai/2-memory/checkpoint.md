# CHECKPOINT (HANDOVER)
@last_update: 2026-05-07
@status: Chuẩn bị thiết kế Schema và API cho Ska Theme Builder (Dual-Table)

## 1. TÌNH TRẠNG HIỆN TẠI (CURRENT STATE)
- Đã khắc phục lỗi nạp Assets (Tailwind/Alpine) cho `Ska Theme Panel`.
- Đã sửa lỗi API tạo Symbol với chuỗi rỗng (Fix `empty()` validation).
- Đã tách lộ trình của Theme Builder ra một Project Manager riêng tại `.ska-ai/1-overview/project-managers/project_manager_theme_builder.md`.
- Kiến trúc **Dual-Table** đã được chốt: `ska_data_sys_theme_templates` (lưu siêu dữ liệu, điều kiện) và `ska_data_sys_organisms` (lưu HTML/JSON thô).

## 2. NHIỆM VỤ PHIÊN TỚI (NEXT SESSION TASKS)
1. **Thiết lập Schema Builder:** Xây dựng bảng `ska_data_sys_theme_templates` thông qua hệ thống `Ska Data Pro` (có các cột: id, name, location, conditions, organism_id, is_active).
2. **REST API Endpoint:** Viết API `POST / GET` cho CRUD Theme Templates. Đảm bảo luồng tạo Theme Template đồng thời liên kết sinh ra một bản ghi trong `ska_data_sys_organisms`.
3. **Data Binding (UI):** Tích hợp UI Alpine.js của `Ska Theme Panel` (`workspace-panel.php`) để gọi API lưu dữ liệu.
4. **Isolated Editor:** Thiết lập khung Iframe Editor dành riêng cho việc biên tập Template toàn màn hình.

## 3. LƯU Ý CHO AGENT (AGENT NOTES)
- Bạn đang làm việc trong phân hệ **Ska No-code Design** (Thư mục con: `inc/theme-builder/` và `inc/design-engine/`).
- Tuân thủ nguyên tắc Zero-Postmeta. Tuyệt đối dùng Flat Tables (`ska_data_*`).
- Đọc kỹ file `project_manager_theme_builder.md` trước khi bắt đầu code.

