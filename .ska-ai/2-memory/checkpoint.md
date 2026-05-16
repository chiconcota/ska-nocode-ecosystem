# 🛑 SKA BUILDER CHECKPOINT BÀN GIAO

## 1. Trạng thái hiện tại
- Đã thực hiện **Kiến trúc Pivot (Quay xe)**: Phế bỏ hoàn toàn mô hình Single Page Application (SPA) dùng `x-show` để hiển thị các luồng App Portal.
- Chuyển sang mô hình **Dedicated Pages (Trang độc lập)**. Mỗi view (List, Detail, Create, Edit) sẽ được cấp một Template riêng biệt (`ska_theme_builder`).
- Xây dựng khái niệm **App Categorization (Virtual Folder)** trong Theme Builder để nhóm các giao diện theo từng App (LMS, CRM...) mà không cần sinh Taxonomy rác.
- Đã thiết lập `project_manager_dedicated_pages.md` thay thế các lộ trình SPA cũ để dẫn dắt việc dọn dẹp (Cleanup) và triển khai Dynamic Router V2.
- Giới hạn Scope của UI "Portal Visibility" (`portal-visibility.js`) chỉ xuất hiện trong CPT `ska_theme_builder`.
- Các tài liệu cốt lõi (`decision-log.md`, `system_map.md`, `design-engine.md`) đã được cập nhật đồng bộ kiến trúc mới.

## 2. Nhiệm vụ cho phiên tiếp theo (Next Session)
**Chủ đề:** Dọn dẹp tàn dư SPA và Triển khai App Categorization.
1. **Task 1.1:** Gỡ bỏ các UI SPA cũ trong Inspector (Panel điều hướng List/Detail) trong `portal-visibility.js` hoặc file tương ứng (vì giờ không cần switch view trên cùng 1 trang nữa).
2. **Task 1.2:** Loại bỏ logic inject `x-show="$store.skaPortal.view === ..."` trong hook `render_block` (`init.php`).
3. **Phase 2:** Bắt đầu xây dựng Filter Bar "App Categorization" trên trang Admin Theme Builder (`admin-panel.php`).

## 3. Ngữ cảnh tập tin đang thao tác
- `.ska-ai/1-overview/project-managers/project_manager_dedicated_pages.md`
- `decision-log.md`
- `system_map.md`
- `ska-no-code-design/design-engine.md`
- `checkpoint.md`
