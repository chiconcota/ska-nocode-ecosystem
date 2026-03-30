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

Bước 3. **Ghi sổ quyết định (Decision Log , System Map & Update):**
   - Đọc và cập nhật file `.ska-ai/2-memory/decision-log.md` bằng cách thêm ngày hôm nay và các Quyết Định Kỹ Thuật Lõi (Core Technical Decisions) đã được chốt/triển khai.
   - Đọc và cập nhật file `.ska-ai/1-overview/system_map.md`:
     - **Tình trạng Plugin/Theme:** Chuyển trạng thái từ 🔴 Pending -> 🟡 In Progress -> 🟢 Done trong bảng Module Registry.
     - **Change Log:** Thêm dòng gạch đầu dòng ngắn ngọn cho ngày cập nhật.
   -  Tìm, Đọc và cập nhật file module tương ứng trong folder '.ska-ai\3-ecosystem' đã sửa trong phiên làm việc.
   - Cập nhật git và push lên github cho dự án sau mỗi phiên làm việc.
 
Bước 4. **Xác nhận kết thúc:**
   - Thông báo rõ ràng: "Sổ bộ nhớ App Builder đã được niêm phong. 
     - Docs lưu tại: `.ska-ai/3-ecosystem/`
     - Lịch sử thiết kế được dán vào Decision Log.
     Phiên làm việc kết thúc an toàn, hệ sinh thái sẵn sàng cho phiên kế tiếp không mất ngữ cảnh."