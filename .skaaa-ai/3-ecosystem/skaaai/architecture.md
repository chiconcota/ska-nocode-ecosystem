# MODULE: Skaaai AI Automation Addon
*Plugin tiện ích mở rộng cung cấp các tính năng trí tuệ nhân tạo chuyên sâu trong hệ sinh thái SKAAA.*

**Status:** 🟡 Planning
**Role:** [ADDON] Đăng ký và thực thi các Node AI (Gemini, OpenAI) trên Logic Graph Canvas.
**Dependency:** Yêu cầu kích hoạt `skaaa-logic-engine` và `skaaa-data-pro`.

---

## 1. Kiến trúc phân chia trách nhiệm (Decoupled Integration)
Skaaai hoạt động như một addon decoupled 100% kết nối vào hệ thống lõi thông qua **Pluggable Nodes Framework**:
- **Backend Registry:** Skaaai hook vào filter `skaaa_logic_registered_nodes` để đăng ký metadata và `settings_schema` (JSON Schema) của các Node AI.
- **Frontend Canvas:** Logic Engine tự động giải mã schema để vẽ Settings Panel (API Key, Prompt, Model...) ngoài Editor. Skaaai không cần chứa mã React/Webpack.
- **Data Caching:** Lưu trữ API Key hệ thống trong bảng phẳng `wp_skaaa_data_sys_settings` của Data Pro.

---

## 2. Các Node AI thiết kế cốt lõi

### A. Node AI Prompt (`AIPromptNode`)
- **Tên lớp PHP:** `Skaaai_Node_Prompt` (nằm trong `includes/primitives/class-skaaai-node-prompt.php`).
- **Nhiệm vụ:** Nhận prompt, nội suy biến động, gửi lên LLM và trả kết quả.
- **Cấu hình UI (Settings Schema):**
  - `api_provider`: select (`gemini` -> Google Gemini, `openai` -> OpenAI).
  - `api_key`: password (Key riêng cho node, mặc định trống để dùng key hệ thống).
  - `model`: text (default `gemini-2.5-flash` hoặc `gpt-4o-mini`).
  - `system_instruction`: textarea (Định hướng hành vi AI).
  - `prompt`: textarea (Prompt chính, hỗ trợ gợi ý autocomplete biến).
  - `temperature`: text (default `0.7`).
  - `response_format`: select (`text` -> Plain Text, `json_object` -> Structured JSON).
  - `result_var`: text (Biến đầu ra lưu kết quả, mặc định `payload.ai_response`).

### B. Node AI Parser (`AIParserNode`)
- **Tên lớp PHP:** `Skaaai_Node_Parser` (nằm trong `includes/primitives/class-skaaai-node-parser.php`).
- **Nhiệm vụ:** Đọc văn bản thô phi cấu trúc (email, chat) và trích xuất thành JSON có cấu trúc.
- **Cấu hình UI bổ sung:**
  - `output_schema`: JSON schema định nghĩa cấu trúc dữ liệu trả về mong muốn (ví dụ: `name`, `email`, `sentiment`, `issue_category`).
  - Hệ thống tự động cấu hình tham số gọi API (như Gemini `responseSchema` hoặc OpenAI `response_format: { type: "json_object" }`) để ép LLM tuân thủ cấu trúc.

---

## 3. Luồng xử lý kỹ thuật (Execution Flow)
Mỗi khi Node AI được chạy trong workflow:

1.  **Nội suy Prompt (SkaaaFX Evaluation)**:
    *   Hệ thống quét System Instruction và Prompt tìm các thẻ `{{ ... }}` và biến `[payload.xyz]`.
    *   Gọi `SkaaaFX_Engine::execute()` để giải mã và thay thế dữ liệu động thật của phiên chạy vào prompt.
2.  **Xác thực API Key**:
    *   Nếu trường API Key trong Node trống, tự động gọi `skaaa_get_system_setting( 'skaaai_gemini_key' )` hoặc `skaaai_openai_key` để lấy key chung của hệ thống.
3.  **Gọi HTTP API**:
    *   Sử dụng `wp_remote_post` gửi request tới endpoint của Gemini hoặc OpenAI với cấu hình tương ứng (Timeout = 30s).
4.  **Xử lý dữ liệu đầu ra**:
    *   Nếu `response_format` là `json_object`, chạy `json_decode` nội dung text trả về để ép thành mảng PHP có cấu trúc.
    *   Gán mảng kết quả vào payload đầu ra dưới khóa `result_var` cấu hình.
