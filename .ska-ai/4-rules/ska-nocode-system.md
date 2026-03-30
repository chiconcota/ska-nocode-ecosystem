# SKA APP BUILDER RULES (v2.0.0)
@target: Ska Micro-Ecosystem | @architecture: 4 Plugins + 1 Theme | @ai_token_saving: Maximum (Decoupled Focus)

## 0. KHẨU QUYẾT TỐI THƯỢNG (THE PRIME DIRECTIVES)
1. **No-Postmeta Rule:** Ska Data Pro sử dụng bảng phẳng (Flat Tables `ska_data_*`). Tuyệt đối không xài lại `wp_postmeta` cho việc xây App. Mọi model mới phải sinh table mới.
2. **Framework-Agnostic WP:** WordPress từ nay bị xem là một nền tảng cung cấp Authentication & Admin UI tạm thời, KHÔNG phải môi trường rễ. Tránh phụ thuộc các class nội địa cũ nếu rủi ro hiệu năng cao.
3. **Decoupled Plugins Rule (Microservices):** Tuyệt đối KHÔNG ĐƯỢC để Plugin A gọi trực tiếp một class của Plugin B. Sự giao tiếp giữa 4 Plugins bắt buộc phải truyền qua WordPress Hooks (`do_action`, `apply_filters`). 

## 1. PHÂN CÁCH TRÁCH NHIỆM (BOUNDARY ISOLATION)
Trước khi Code, Agent BẮT BUỘC nhận diện code mình viết sẽ rơi vào Plugin nào để không lẫn lộn:
- **Ska Blank Theme:** Loại bỏ `.wp-block-library` và CSS của WordPress.
- **Ska Builder Core:** Render ra mã HTML sạch (Ska Container, Text, Image). Không chứa logic CSS.
- **Ska No-code Design:** Phụ trách hệ thống JIT Tailwind v4, biến `<div class="bg-red">` thành CSS thật.
- **Ska Data Pro:** Đọc/Ghi dữ liệu dưới Database (`ska_data_*`). Schema Manager.
- **Ska Logic Engine:** Nhận `<div ska-if="{{check}}">` và quyết định Render hay Ẩn mất thẻ đó.

## 2. AI WORKFLOW PROTOCOL BẮT BUỘC
- **Step 1 (Context):** Quét `/1-overview/` để nắm giới hạn ranh giới (System Map). 
  🚨 **QUAN TRỌNG:** Dự án đang ở Phase 2 (Ska Data Pro). **BẮT BUỘC ĐỌC** file `.ska-ai/1-overview/project_manager_phase2.md` và tuân thủ lộ trình Phase 2 trước khi code bất kỳ tính năng gì liên quan tới Data, Form hay UI Block có dính líu đến dữ liệu.
- **Step 2 (Isolate Memory):** Thay vì đọc toàn bộ, hãy hỏi user xem đang làm việc với Plugin nào? Rồi chỉ quét tài liệu trong `/3-ecosystem/[Tên Plugin]/`. (Ví dụ: Đang dev Theme thì cấm AI đọc doc của Logic Engine).
- **Step 3 (Schema First):** Lên thiết kế Interface & WP Hooks giữa các khối TRƯỚC khi gõ Code vào File. Cấm vội vã.
- **Step 4 (Documentation Loop):** Code xong, cập nhật tài liệu ở `/3-ecosystem/[Plugin]/` và ném Log lịch sử vào `/2-memory/decision-log.md`.

## 3. FILE SIZE LIMIT (DIVIDE & CONQUER)
- Cấm để file to vượt mốc 700 lines. 
- Giữ logic ở các Hook callback, thay vì ném code dồn toa vào constructor `__construct`.

## 4. STYLING & FRONTEND RULE
- Nếu có Tailwind, chỉ xài Design Engine.
- Ở ngoài Editor (Backend JSX), hạn chế hardcode CSS, bắt mọi thuộc tính phải quy về class Tailwind (Nguồn gốc: Single Source Of Truth).
- Tránh ghi đè global nếu không có scope `.ska-builder [class*='wp-block-ska-builder']`. Tránh làm gãy Theme khác.

## 5. QUY LUẬT TẠO TÀI LIỆU (ANTI-CLUTTER DIRECTIVE)
- **Hạn chế tạo rác:** Yêu cầu các Agent Tuyệt đối KHÔNG ĐƯỢC tự ý tạo mới bất kỳ file Markdown (`.md`) nào ở thư mục gốc (Root) của project hoặc thư mục gốc của `.ska-ai` nếu chưa xin phép.
- **Bắt buộc hỏi Ý kiến:** Nếu trong lúc thực hiện luồng /end_session hoặc theo lệnh từ Workflow mà nhận thấy CẦN PHẢI TẠO file mới nằm ngoài 4 thư mục lớn (`1-overview`, `2-memory`, `3-ecosystem`, `4-rules`) thì **BẮT BUỘC phải đặt câu hỏi xin phép User trước**. Nếu User trả lời OK/Yes thì mới được phép khởi tạo.
- Khuyến nghị: Ưu tiên update trực tiếp (Replace_content) trên các tài liệu đã có sẵn trong 4 ngăn kéo để hạn chế sinh sôi nảy nở file rác.
