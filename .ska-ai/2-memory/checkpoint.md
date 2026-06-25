# CHECKPOINT - PHẦN BÀN GIAO TIẾN ĐỘ
*Ngày cập nhật: 2026-06-25*

## 1. Trạng thái hiện tại (Status)
- **Git Branch**: `main` (Đã merge nhánh `feature/scripts-library` vào `main` và push lên GitHub).
- **Công việc đã thực hiện trong phiên**:
  1. **Nesting Block Code inside Container**: Bổ sung `'ska-builder/code'` vào danh sách `allowedBlocks` của block `Ska Container` (`ska-container/index.js`). Khắc phục giới hạn của phiên trước, cho phép người dùng kéo thả block canvas/code lồng trực tiếp vào bên trong block container để tạo các khối giao diện phức hợp (ví dụ: bọc canvas biểu đồ trong card glassmorphism).
  2. **Gutenberg UI Icon Enhancement**: Đăng ký sử dụng icon `code` từ thư viện chính quy `@wordpress/icons` cho block `Ska Code` (`ska-code/index.js`). Cải thiện trải nghiệm trực quan trong Block Inserter.
  3. **Robust API Table Routing**: Cải tiến REST API Portal (`class-rest-api.php`) để tự động phân giải và ánh xạ chính xác tên bảng từ request URL. Hệ thống tự động kiểm tra: tên bảng thô, bảng có prefix mặc định, bảng dạng phẳng (`ska_data_revenue`), hoặc theo Portal Settings Slug (`revenue-api`), định tuyến chính xác về tên bảng phẳng MySQL gốc để phục vụ truy vấn dữ liệu.
  4. **Duplicate Script Render Prevention**: Tối ưu hóa `Scripts_Loader` (`class-scripts-loader.php`) bằng cách theo dõi danh sách `rendered_script_ids` tĩnh. Đảm bảo triệt tiêu hoàn toàn lỗi in lặp script CDN (như thư viện Chart.js) ở cả Header (`wp_head`) và Footer (`wp_footer`) khi nhiều block/trang cùng gọi load.
  5. **Header CSS/JS Lifecycle Conflict Resolution**: Phát hiện và vá lỗi mất CSS/JS ở Header (Inject Location = Header) do block render trong body lỡ nhịp chạy `wp_head`. Xây dựng cơ chế pre-scan tĩnh toàn bộ blocks `ska-builder/code` sớm ở `wp_head` độ ưu tiên 1 để đẩy tài nguyên vào queue kịp thời.
  6. **HTML Script Comment Debugging**: In thêm comment debug HTML `<!-- Ska Script: [script_id] -->` trước khi load script thư viện giúp dễ dàng kiểm tra tính khử trùng lặp và xác minh E2E View Source.
  7. **Versioning Auto-Increment**: Nâng cấp phiên bản plugin **Ska Data Pro** lên `v1.3.2` và plugin **Ska No-Code Design** lên `v1.2.3`, tuân thủ nghiêm ngặt quy tắc SemVer.
- **Trạng thái**: 🟢 Fully Verified, Merged & Released (Các lỗi phát sinh đã được fix triệt để, kiểm thử E2E Test Cases 1-4 thành công tốt đẹp).

## 2. Các quyết định thiết kế đã thống nhất:
- **Dynamic API Mapping**: Tránh hardcode endpoint, cho phép các plugin/JS gọi API thông qua Portal Settings Slug linh hoạt.
- **Strict Allowed Blocks Control**: Kiểm soát chặt chẽ danh sách allowed blocks của Container để bảo vệ layout và tính nhất quán của hệ sinh thái.
- **Render Track Cache**: Theo dõi trạng thái rendered script tại runtime để ngăn ngừa xung đột/in thừa tài nguyên JS ngoài frontend.
- **Early wp_head Pre-scan**: Đăng ký quét sớm blocks ở hook `wp_head` độ ưu tiên 1 ngoài frontend để giải quyết triệt để vấn đề assets lỡ nhịp vòng đời WordPress.
- **HTML Comment Debugging**: In ID script dưới dạng comment HTML để debug và tối ưu hóa quy trình kiểm thử tự động.

## 3. Danh sách file thay đổi & tạo mới trong phiên:
- **Cập nhật mã nguồn**:
  - `[NEW]` `wp-content/plugins/ska-no-code-design/blocks/class-ska-code-block-queue.php` (Quản lý hàng đợi in và quét block sớm).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/blocks/init.php` (Load sớm class queue).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/src/ska-code/render.php` (Dọn dẹp định nghĩa class trùng).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/build/ska-code/render.php` (Dọn dẹp định nghĩa class trùng).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/ska-no-code-design.php` (Nâng cấp version lên 1.2.3).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/package.json` (Nâng cấp version lên 1.2.3).
  - `[MODIFY]` `wp-content/plugins/ska-data-pro/inc/core/class-scripts-loader.php` (In comment debug HTML).
  - `[MODIFY]` `wp-content/plugins/ska-data-pro/ska-data-pro.php` (Nâng cấp version lên 1.3.2).
  - `[MODIFY]` `wp-content/plugins/ska-data-pro/package.json` (Nâng cấp version lên 1.3.2).
  - `[MODIFY]` `wp-content/plugins/ska-data-pro/inc/api/class-rest-api.php` (Phân giải bảng thông minh qua prefix/slug).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/src/ska-code/index.js` (Thêm icon code từ `@wordpress/icons`).
  - `[MODIFY]` `wp-content/plugins/ska-no-code-design/src/ska-container/index.js` (Cho phép lồng `ska-builder/code` vào allowedBlocks).
  - `[MODIFY]` Các file biên dịch trong thư mục `build/` của plugin Design.
- **Cập nhật tài liệu hệ thống**:
  - `[MODIFY]` `.ska-ai/2-memory/checkpoint.md` (Cập nhật tiến trình bàn giao phiên này).
  - `[MODIFY]` `.ska-ai/1-overview/system_map.md` (Cập nhật logs v1.2.3 & v1.3.2).
  - `[MODIFY]` `.ska-ai/2-memory/decision-log.md` (Bổ sung quyết định thiết kế lồng block, sửa lỗi nạp head, comment debug và API).
  - `[MODIFY]` `.ska-ai/3-ecosystem/ska-no-code-design/blocks.md` (Cập nhật ghi chú về khả năng Nesting và cơ chế pre-scan).

## 4. Gợi ý công việc cho phiên tiếp theo (QUAN TRỌNG)
- **Chuẩn bị Milestone 2**: Phiên tiếp theo có thể bắt đầu lập kế hoạch và thiết kế cho các tính năng của Milestone 2 (như Pluggable Community Nodes, Async Workflows nâng cao, v.v.). Nhánh Git làm việc sẽ bắt đầu từ nhánh `main` sạch.
