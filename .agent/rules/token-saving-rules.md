---
trigger: always_on
---

# SKA AI TOKEN-SAVING & CONTEXT OPTIMIZATION RULES (v1.0.0)
@target: All AI Agents | @priority: High | @token_saving: Maximum

Quy tắc này nhằm tối ưu hóa số lượng token nạp vào ngữ cảnh (context window) của AI, giảm thiểu chi phí và tăng tốc độ phản hồi bằng cách hạn chế hành vi tự động đọc file tràn lan và tối ưu hóa việc sử dụng công cụ.

## 1. NGUYÊN TẮC ĐỌC FILE CÓ CHỌN LỌC (SELECTIVE READING PROTOCOL)
* **Chỉ đọc vùng cần thiết:** Khi sử dụng công cụ `view_file` trên các file lớn (trên 150 dòng), AI bắt buộc phải xác định vùng dòng cần đọc và sử dụng tham số `StartLine` và `EndLine`. Tuyệt đối không đọc toàn bộ file nếu chỉ cần kiểm tra một hàm hay một block code.
* **Không đọc thử tràn lan:** Tránh đọc nhiều file cùng lúc để "tìm hiểu". Hãy hỏi trực tiếp User đường dẫn file cụ thể nếu không chắc chắn logic nằm ở đâu.
* **Tận dụng bộ nhớ phiên (Session Memory):** Không đọc đi đọc lại cùng một file trong cùng một lượt hội thoại nếu nội dung file đó chưa thay đổi.

## 2. NGUYÊN TẮC TÌM KIẾM TẬP TRUNG (TARGETED SEARCH PROTOCOL)
* **Giới hạn phạm vi tìm kiếm:** Khi dùng `grep_search`, bắt buộc phải sử dụng bộ lọc `Includes` (ví dụ: `*.md`, `*.js`, `*.php`) và chỉ định chính xác thư mục chứa file cần tìm (`SearchPath`). Cấm tìm kiếm từ gốc dự án không có bộ lọc.
* **Hạn chế liệt kê thư mục (`list_dir`):** Chỉ liệt kê thư mục khi thực sự cần biết cấu trúc file. Hãy nhớ cấu trúc thư mục đã liệt kê trước đó thay vì gọi lại lệnh.

## 3. PHÂN TÁCH NGỮ CẢNH THEO PLUGIN (COMPONENT ISOLATION)
* **Không nạp chéo tài liệu:** Khi đang thực hiện công việc liên quan đến một thành phần cụ thể (ví dụ: `ska-canvas`), nghiêm cấm AI tự ý đọc tài liệu kỹ thuật hoặc mã nguồn của các thành phần khác (`ska-logic-engine`, `ska-data-pro`) trừ khi có hook liên kết trực tiếp được User chỉ định.

## 4. TÀI LIỆU VÀ PHẢN HỒI SÚC TÍCH (CONCISE COMMUNICATION & DOCS)
* **Không copy-paste code vào kế hoạch:** Trong các file `implementation_plan.md` hoặc `walkthrough.md`, cấm copy toàn bộ nội dung code lớn. Thay vào đó, hãy sử dụng đường dẫn link file kèm số dòng cụ thể (ví dụ: `[canvas.php](file:///path/to/canvas.php#L50-L75)`) hoặc chỉ ghi các đoạn code ngắn (diff) đại diện cho thay đổi.
* **Trả lời cực kỳ ngắn gọn:** Tuân thủ nguyên tắc hội thoại súc tích. Trả lời thẳng vào trọng tâm câu hỏi của User, bỏ qua các phần chào hỏi rườm rà hoặc giải thích lý thuyết lập trình cơ bản không cần thiết.
* **Không tạo file rác:** Ưu tiên cập nhật trực tiếp nội dung vào các file tài liệu hiện có thay vì tạo thêm file `.md` mới làm phình to dung lượng bộ nhớ.
