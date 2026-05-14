# PROJECT MANAGER: AUTO-GENERATED CRUD & MACRO INJECTOR (PHASE 4.6)
@status: TODO
@priority: Medium | @dependency: App Portals (Phase 4.5)

## 1. TẦM NHÌN VÀ SỨ MỆNH (VISION & MISSION)
**Sứ mệnh:** Tiết kiệm 90% thời gian xây dựng phần mềm (SaaS, CRM) cho người dùng Nocode bằng cách tự động hóa công việc tạo Giao diện Danh sách và Form nhập liệu.
**Tại sao cần thiết?**
Trong App Builder, 80% khối lượng công việc là thao tác với Dữ liệu (CRUD: Xem, Thêm, Sửa, Xoá). Việc bắt người dùng Nocode phải tự tay kéo hàng chục khối (Text, Button, Modal, Form), rồi tự cấu hình từng biến dữ liệu cho một chức năng cơ bản là quá rườm rà và tốn sức. Cần một "cú click" để giải quyết toàn bộ cấu trúc nền móng ban đầu.

## 2. BẢO VỆ QUY TẮC HỆ SINH THÁI (SKA ECOSYSTEM CONSTRAINTS)
**Rủi ro gặp phải:** Nếu gộp toàn bộ tính năng Lưới (Grid), Sửa, Xoá vào một khối (Block) duy nhất, nó sẽ trở thành "Hộp đen" (Blackbox). Việc này vi phạm nghiêm trọng quy tắc **Atomic (Nguyên tử)** của hệ thống, và tước đi quyền tuỳ chỉnh **Full Site Editing (FSE)** của người dùng.

**Giải pháp Kiến trúc - "Macro Pattern Injector":**
- **Không Hộp đen (No Blackbox):** Tính năng Auto-Generate không phải là một khối nguyên khối. Nó là một "Hành động Macro" (Macro Action).
- **Rải khối Nguyên tử (Atomic Dropping):** Khi kích hoạt, hệ thống hoạt động như một con Robot: tự động bốc các khối có sẵn trong hệ sinh thái (`Ska Loop`, `Ska Text`, `Ska Button`, `Ska Modal`, `Ska Form`) và xếp chúng thành một Layout hoàn chỉnh trên màn hình thay cho người dùng.
- **Minh bạch & FSE (Transparency):** Sau khi rải xong, quá trình tự động kết thúc. Mọi khối trên màn hình đều là khối độc lập. Nocode Admin có toàn quyền FSE: tùy ý sửa màu nút bấm, đổi vị trí Form, xóa bớt cột dư thừa. Đặc biệt, có thể chèn thêm Logic Engine Node (ví dụ: Bắn Zalo, Gửi Email) vào chuỗi sự kiện của nút Xóa mà không bị giới hạn bởi hộp đen nào.
👉 **Kết luận:** Giữ vững triết lý Ska: *"Chỉ tự động hóa thao tác xếp khối thủ công, tuyệt đối không giấu Logic"*.

## 3. DANH SÁCH NHIỆM VỤ (TASK LIST)
- [ ] **Ska Loop Upgrade:** Cập nhật block `Ska Loop` trên Editor (React UI), thêm nút (Button/Toggle) `Bật Auto-Generate CRUD` ở bảng Inspector.
- [ ] **Schema Fetcher:** Viết API gọi từ Data Pro để lấy danh sách các cột (Schema) của bảng đang chọn.
- [ ] **Macro Injector (React):** Viết script JS dùng cơ chế block editor (`dispatch('core/block-editor').insertBlocks`) để tự động rải khối.
   - Sinh ra các khối `ska-text` ánh xạ tự động vào các cột dữ liệu.
   - Sinh ra khối `ska-button` (Action Slot) chứa Alpine JS Dispatcher chuẩn (`x-on:click="$dispatch(...)"`).
- [ ] **Modal Form Generator:** Tự động tạo khối `Ska Modal` (chứa `Ska Form`) đặt cuối trang để xử lý UI Edit/Create.
- [ ] **Logic Pre-binding:** Tự động tạo ngầm các Event Trigger Node chuẩn trong bảng của Logic Engine để đón sự kiện (Event) phát ra từ các nút UI.
