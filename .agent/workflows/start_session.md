---
description: Bắt đầu phiên làm việc chuẩn chuẩn Vibecoding App Builder (Load Context & Memory)
---

1. **Khởi động bộ nhớ (Context Loading - Mới):**
   - Đọc file `.ska-ai/1-overview/system_map.md`. Đây là bước BẮT BUỘC để nắm cấu trúc App Builder.
   - Đọc file `.ska-ai/1-overview/Ska-no-code-overview.md` để hiểu ranh giới của 4 Plugins và 1 Theme mới (Ska Core, Data Pro, Logic Engine, Design Engine).
   - Đọc file `.ska-ai/2-memory/decision-log.md` để biết những quyết định thiết kế gần nhất.

2. **Xác định tiêu điểm (Focus Definition):**
   - Nếu người dùng chưa chỉ định rõ Plugin mục tiêu trong prompt, hãy hỏi: "Hôm nay chúng ta sẽ làm việc trên Plugin nào? (Ska Core, Design Engine, Data Pro, hay Logic Engine?)".
   - Nếu đã rõ, xác nhận lại với người dùng: "Tôi sẽ focus hoàn toàn vào ranh giới (Boundary) của Plugin: [Tên Plugin]".

3. **Load Plugin Memory (Context-Switching):**
   - Dựa vào Plugin mục tiêu, đọc TOÀN BỘ tài liệu liên đới trong thư mục `.ska-ai/3-ecosystem/[Tên Plugin]/`.
   - Ví dụ: Nếu người dùng báo làm Data Pro -> Đọc các file doc tĩnh trong `.ska-ai/3-ecosystem/ska-data-pro/`.
   - Lưu ý: Tuyệt đối KHÔNG đọc lộn tài liệu của Plugin không liên quan để tránh Hallucination/Tràn RAM.

4. **Sẵn sàng (Ready Check):**
   - Phản hồi ngắn gọn: "Đã nạp kiến trúc App Builder (system_map.md). Đang cô lập vùng làm việc vào Plugin: [Tên]. Sẵn sàng nhận lệnh."