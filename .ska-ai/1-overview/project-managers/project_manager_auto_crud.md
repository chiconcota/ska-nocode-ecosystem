# PROJECT MANAGER: AUTO-GENERATED CRUD & MACRO INJECTOR (PHASE 4.6)
@status: IN PROGRESS (Tasks 1-4 Completed)
@priority: Medium | @dependency: App Portals (Phase 4.5), Ska Logic Engine

## 1. TẦM NHÌN VÀ SỨ MỆNH (VISION & MISSION)
**Sứ mệnh:** Tiết kiệm 90% thời gian xây dựng phần mềm (SaaS, CRM) cho người dùng Nocode bằng cách tự động hóa công việc tạo Giao diện Danh sách và Form nhập liệu.
**Tại sao cần thiết?**
Trong App Builder, 80% khối lượng công việc là thao tác với Dữ liệu (CRUD: Xem, Thêm, Sửa, Xoá). Việc bắt người dùng Nocode phải tự tay kéo hàng chục khối (Text, Button, Modal, Form) rồi định tuyến từng biến dữ liệu là quá rườm rà. Hệ thống cần tính năng "Nút Magic" tự động cấu trúc nền móng chỉ với 1 click.

## 2. BẢO VỆ QUY TẮC HỆ SINH THÁI (SKA ECOSYSTEM CONSTRAINTS)
**Rủi ro gặp phải:** Nếu tạo một khối `Data View Block` đóng gói nguyên khối (Blackbox), nó sẽ vi phạm nguyên tắc **Atomic (Nguyên tử)** của hệ thống, tước đi quyền **Full Site Editing (FSE)** của người dùng.

**Giải pháp Kiến trúc - "Macro Pattern Injector":**
- **Không Hộp đen (No Blackbox):** Tính năng Auto-Generate kích hoạt bộ rải **Macro Pattern Injector** của Theme Builder.
- **Rải khối Nguyên tử (Atomic Dropping):** Khi chạy, hệ thống tự động bốc các khối có sẵn (`Ska Loop`, `Ska Text`, `Ska Button`, `Ska Modal`, `Ska Form`) và xếp thành một bố cục hoàn chỉnh (Sidebar + Main Content).
- **Asset Tracking (Bảo vệ dữ liệu rác):** Các ID của Template, Organism sinh ra được lưu trong Schema (`portal_config.generated_assets`). Sinh lần 2 sẽ GHI ĐÈ thay vì đẻ thêm rác.
- **Minh bạch & FSE (Transparency):** Nocode Admin có toàn quyền sửa màu sắc, thêm cột, đổi chỗ Form sau khi hệ thống rải xong.
- **Event-Driven Data Flow:** Form sinh ra gửi thẳng Data về REST API (Validation cơ bản). Nếu muốn phức tạp, người dùng đổi Action thành Trigger Workflow (Ska Logic Engine) để chạy logic tuỳ biến.

## 3. DANH SÁCH NHIỆM VỤ (TASK LIST)

- [x] **Task 1: Generator API (Universal Pattern):** 
  - Viết controller `POST /wp-json/ska-builder/v1/generate-portal`.
  - Sinh Organism hiển thị thẻ.
  - Sinh List View (Loop gọi Organism) với Modal Quick Edit đặt BÊN NGOÀI vòng lặp. **Lưu ý:** Form trong Quick Edit bỏ qua các cột `long_text` (Chuẩn UX WordPress).
  - Sinh Detail View (Form đầy đủ).
- [x] **Task 2: Frontend Integration:** Tích hợp nút "Tự động sinh App Portal" vào bảng cài đặt `manage-modals.php` của Ska Data Pro.
- [x] **Task 3: Dynamic Shadow Scratchpad & Ska Form Rich Text:** 
  - Tạo khối mới `ska-builder/form-rich-text` cho trường `long_text` (Ở Detail View). Tích hợp TinyMCE cho phép sửa nhanh.
  - Viết API `scratchpad/create` tạo ID bài viết ảo (phái sinh) và trả về Frontend.
  - Khối Rich Text gọi Iframe Gutenberg mượn ID ảo để thiết kế nâng cao.
  - Viết API `scratchpad/destroy` xóa vĩnh viễn ID ảo sau khi người dùng đóng/lưu Iframe.
- [x] **Task 4: Garbage Collection:** Lắng nghe Hook `ska_data_table_deleted` để tự động dọn dẹp các Template/Organism sinh ra từ Generator.
- [ ] **Task 5: Logic Engine Node - "Table Listener":** Viết Trigger Node mới trong Ska Logic Engine chuyên để lắng nghe Hook `ska_data_updated` và nạp tự động `Record_Data` vào Context của Workflow.
