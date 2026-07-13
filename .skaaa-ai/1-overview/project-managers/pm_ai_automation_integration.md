# PROJECT MANAGER: AI AUTOMATION INTEGRATION
@status: 🟡 Planning | @target_milestone: MILESTONE 2 (AI AUTOMATION) | @last_update: 2026-07-13

> Tài liệu này quản lý tiến độ phát triển, thiết kế kiến trúc và quy trình tích hợp các tính năng AI (Gemini, OpenAI) vào hệ sinh thái **SKAAA**, lấy plugin **Skaaai** (AI Addon) làm trung tâm để mở rộng **Skaaa Logic Engine**.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Tạo Plugin Skaaai độc lập**: Module mở rộng decoupled 100% chuyên trách cho các tác vụ AI, đăng ký node thông qua Pluggable Registry API.
- **Node AI Prompt (`AIPromptNode`)**: Cho phép gửi prompt tự do có nội suy biến `{{ ... }}` đến LLM và nhận kết quả Text hoặc JSON.
- **Node AI Parser (`AIParserNode`)**: Đọc dữ liệu phi cấu trúc (email/tin nhắn) và trích xuất thành mảng JSON có cấu trúc để ghi vào database phẳng.
- **Quản lý API Key toàn cục**: Cấu hình chung cho Gemini/OpenAI API Keys tại Skaaa Dashboard, hỗ trợ ghi đè (override) cục bộ trong từng node.
- **Agentic Workflow**: Khả năng tạo các node Agent tự vận hành và tự động gọi các công cụ (Tool Call) có sẵn trong hệ thống (như đọc/ghi DB, gọi API).

---

## 2. TIẾN ĐỘ THỰC HIỆN (ROADMAP & STATUS)

### 🟢 Phase 1: Khởi tạo Hạ tầng Plugin Skaaai
- [ ] Thiết lập thư mục và tệp chính `wp-content/plugins/skaaai/skaaai.php`.
- [ ] Tạo trang quản lý cài đặt API Keys (Gemini, OpenAI) tích hợp vào Skaaa Dashboard.
- [ ] Thiết lập lưu cấu hình API Keys vào bảng phẳng `wp_skaaa_data_sys_settings` bằng hàm helper `skaaa_set_system_setting()`.

### 🟡 Phase 2: Phát triển Node AI Prompt (`AIPromptNode`)
- [ ] Xây dựng class `Skaaai_Node_Prompt` triển khai interface `Skaaa_Logic_Node`.
- [ ] Đăng ký node vào registry thông qua filter `skaaa_logic_registered_nodes` kèm settings schema vẽ form UI:
  - `api_provider`: select (`gemini`, `openai`).
  - `api_key`: password (input ẩn).
  - `model`: text (default `gemini-2.5-flash` hoặc `gpt-4o-mini`).
  - `system_instruction`: textarea.
  - `prompt`: textarea (hỗ trợ autocomplete biến).
  - `temperature`: text (default `0.7`).
  - `response_format`: select (`text`, `json_object`).
- [ ] Viết logic nội suy biến SkaaaFX `{{ ... }}` trong prompt trước khi gửi đi.
- [ ] Xử lý HTTP request gọi API LLM qua fastcgi / wp_remote_post.
- [ ] Triển khai tự động parse dữ liệu JSON từ LLM về mảng PHP nếu chọn `response_format = json_object`.

### ⚪ Phase 3: Phát triển Node AI Parser (`AIParserNode`)
- [ ] Xây dựng class `Skaaai_Node_Parser` kế thừa để xử lý trích xuất dữ liệu có cấu trúc.
- [ ] Định nghĩa schema trả về mong muốn bằng JSON Schema trong settings node.
- [ ] Ép LLM trả về đúng cấu trúc và validate kết quả đầu ra trước khi nạp vào payload.

### ⚪ Phase 4: Kiểm thử E2E & Nghiệm thu (Testing & Verification)
- [ ] Xây dựng kịch bản kiểm thử: Form liên hệ ngoài Frontend ➔ Nhận dữ liệu ➔ AI Parser phân loại cảm xúc và thông tin khách hàng ➔ Ghi vào Flat Table `leads` ➔ Gửi email phản hồi tự động cá nhân hóa.
- [ ] Viết tài liệu quy trình kiểm thử E2E thủ công.

---

## 3. TIÊU CHÍ NGHIỆM THU (ACCEPTANCE CRITERIA)
1. Plugin **Skaaai** hoạt động độc lập và đăng ký thành công các Node AI lên Canvas của Logic Engine khi kích hoạt.
2. API Key được bảo mật và tự động fallback về key hệ thống nếu node trống key.
3. Prompt hỗ trợ nội suy chính xác tất cả các biến dynamic `{{ payload.xyz }}`.
4. Đầu ra dạng JSON của AI phải được parse thành mảng PHP sạch và có thể ghi trực tiếp vào MySQL Flat Tables ở các node database sau đó.
