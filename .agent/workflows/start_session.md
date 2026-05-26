---
description: Bắt đầu phiên làm việc chuẩn chuẩn Vibecoding App Builder (Load Context & Memory)
---

1. **Khởi động bộ nhớ (Context Loading - Mới):**
   - Đọc file `.ska-ai/2-memory/checkpoint.md` LÀ BƯỚC ĐẦU TIÊN để nhận bàn giao trạng thái công việc, Git branch, và bug đang làm dở từ phiên trước.
   - Đọc file `.ska-ai/1-overview/system_map.md`. Đây là bước BẮT BUỘC để nắm cấu trúc App Builder.
   - Đọc file `.ska-ai/1-overview/Ska-no-code-overview.md` để hiểu ranh giới của 4 Plugins và 1 Theme mới (Ska Core, Data Pro, Logic Engine, Design Engine).
   - Đọc file `.ska-ai/2-memory/decision-log.md` để biết những quyết định thiết kế gần nhất.

2. **Xác định tiêu điểm & Nhánh làm việc (Focus & Git Branch Decision):**
   - Nếu người dùng chưa chỉ định rõ Plugin mục tiêu, hãy hỏi để cô lập vùng làm việc.
   - Kiểm tra nhánh Git hiện tại (dùng `git branch` hoặc `git status`).
   - Cùng người dùng quyết định: **Nên làm việc trực tiếp trên nhánh `main` hay cần khởi tạo/chuyển sang một nhánh tính năng mới (`feature/ten-tinh-nang`)?** 
   - Ghi nhận thông tin nhánh này để cập nhật vào `checkpoint.md` khi kết thúc phiên.

3. **Load Plugin Memory (Context-Switching):**
   - Dựa vào Plugin mục tiêu, đọc TOÀN BỘ tài liệu liên đới trong thư mục `.ska-ai/3-ecosystem/[Tên Plugin]/`.
   - Lưu ý: Tuyệt đối KHÔNG đọc lộn tài liệu của Plugin không liên quan để tránh Hallucination/Tràn RAM.

4. **Sẵn sàng (Ready Check):**
   - Phản hồi ngắn gọn: "Đã nạp kiến trúc App Builder (system_map.md). Đang làm việc trên nhánh Git: [Tên Nhánh]. Đang cô lập vùng làm việc vào Plugin: [Tên Plugin]. Sẵn sàng nhận lệnh."