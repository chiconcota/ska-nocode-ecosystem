---
description: Kết thúc phiên làm việc App Builder (Ghi nhớ kiến trúc & Decision Log)
---

Bước 1. **Tổng hợp kiến thức (Knowledge Consolidation):**
   - Rà soát lại toàn bộ các tool `write_to_file`, `replace_file_content` đã thực hiện trong phiên hiện tại.
   - Xác định các thay đổi kiến trúc: Có thêm WP Hook (Action/Filter) nào mới để giao tiếp xuyên Plugin không? Tên bảng Flat Tables (`ska_data_*`) nào được tạo?

Bước 2. **Cập nhật Ecosystem Documentation (Đường sinh mệnh của App Builder):**
   - Mở thư mục `.ska-ai/3-ecosystem/` và tìm Plugin tương ứng mà bạn vừa thao tác (Vd: `ska-data-pro`).
   - Tạo hoặc Cập nhật file markdown kiến trúc của Plugin đó với nội dung:
     - Boundary Rules (Giới hạn trách nhiệm của đoạn code vừa viết).
     - WP Hooks được Expose để Plugin khác móc vào.
     - Luồng dữ liệu mới.

Bước 3. **Ghi sổ quyết định (Decision Log , System Map, roadmap & Update):**
   - Đọc và cập nhật file `.ska-ai/2-memory/decision-log.md` bằng cách thêm ngày hôm nay và các Quyết Định Kỹ Thuật Lõi (Core Technical Decisions) đã được chốt/triển khai.
   - Đọc và cập nhật file `.ska-ai/1-overview/system_map.md`:
     - **Tình trạng Plugin/Theme:** Chuyển trạng thái từ 🔴 Pending -> 🟡 In Progress -> 🟢 Done trong bảng Module Registry.
     - **Change Log:** Thêm dòng gạch đầu dòng ngắn ngọn cho ngày cập nhật.
   - Tìm, Đọc và cập nhật file module tương ứng trong folder '.ska-ai\3-ecosystem' đã sửa trong phiên làm việc.
   - Mở và GHI ĐÈ dữ liệu vào file tương ứng với module tương ứng `..ska-ai\1-overview\project-managers` để lưu lại tiến độ đang dự án.
   - Mở và GHI ĐÈ dữ liệu vào file `.ska-ai/2-memory/checkpoint.md` để lưu lại tiến độ đang code dở, danh sách file, các lỗi hiện tại, và đặc biệt phải ghi rõ tên nhánh Git hiện tại đang làm việc để bàn giao cho Agent phiên sau.
   - Cập nhật Git: Hỏi ý kiến User xem có nên commit và push lên GitHub không? Nếu đang ở nhánh feature, có cần tạo Pull Request hoặc merge vào `main` luôn không? Thực hiện theo quyết định của User.
 
Bước 4. **Xác nhận kết thúc:**
   - Thông báo rõ ràng: "Sổ bộ nhớ App Builder đã được niêm phong. 
     - Nhánh Git làm việc: [Tên Nhánh] (Đã ghi nhận tại checkpoint.md)
     - Trạng thái Push/Merge: [Quyết định và kết quả đã thực hiện]
     - File bàn giao (Checkpoint) đã được lưu mốc an toàn.
     - Danh sách công việc (project manager) đã được cập nhật.
     - Docs lưu tại: `.ska-ai/3-ecosystem/`
     - Lịch sử thiết kế được dán vào Decision Log.
     Phiên làm việc kết thúc an toàn, hệ sinh thái sẵn sàng cho phiên kế tiếp không mất ngữ cảnh."