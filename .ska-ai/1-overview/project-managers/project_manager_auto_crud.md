# PROJECT MANAGER: AUTO-GENERATED CRUD & MACRO INJECTOR (PHASE 4.6)
@status: TODO
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
- **Minh bạch & FSE (Transparency):** Sau khi rải, thao tác tự động kết thúc. Nocode Admin có toàn quyền FSE: sửa màu sắc, thêm cột, đổi chỗ Form.
- **Event-Driven Data Flow:** Các thao tác Submit/Update từ form ở Frontend sẽ ngầm bắn ra Hook (ví dụ: `ska_data_updated`). Ska Logic Engine sẽ hứng sự kiện này bằng Trigger Node "Table Listener" để tiếp tục các chuỗi tự động hóa (Gửi email, Gọi API...).

## 3. DANH SÁCH NHIỆM VỤ (TASK LIST)
- [ ] **Schema Fetcher:** Viết API gọi từ Data Pro để lấy danh sách các cột (Schema) của Smart Object (bảng) đang được chọn.
- [ ] **Macro Injector (React):** Viết script JS dùng cơ chế block editor (`dispatch('core/block-editor').insertBlocks`) để rải khối Atomic dựa trên Schema.
   - Sinh ra các khối `ska-text` ánh xạ tự động vào các trường dữ liệu.
   - Sinh ra khối `ska-button` (Action Slot) tích hợp Alpine JS Dispatcher chuẩn (`x-on:click="$dispatch(...)"`).
- [ ] **Modal Form Generator:** Tự động tạo khối `Ska Modal` (chứa `Ska Form`) đặt ẩn ở cấu trúc trang để phục vụ thao tác Edit/Create.
- [ ] **Logic Engine Node - "Table Listener":** Viết Trigger Node mới trong Ska Logic Engine chuyên để lắng nghe Hook `ska_data_updated` và nạp tự động `Record_Data` vào Context của Workflow.
