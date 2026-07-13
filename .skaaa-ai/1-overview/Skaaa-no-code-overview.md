# SKAAA ECOSYSTEM - MASTER PLAN (AI-READABLE)
@version: 3.0.0 | @stack: WP-Core, Tailwind JIT, Flat Tables, Logic DAG, AI (Gemini/OpenAI) | @focus: Native SSR Monolith + AI Automation

## 1. TẦM NHÌN HỆ SINH THÁI (THE TRINITY + AI ADDRON ARCHITECTURE)
Chúng ta định vị **SKAAA** là một **Hệ điều hành ứng dụng tự chủ (Self-hosted App OS)** chạy trực tiếp trên WordPress core dưới dạng **Native SSR Monolith** (tối ưu hóa tốc độ tải trang 0ms bằng Alpine.js và Tailwind JIT, loại bỏ định hướng headless Next.js).

Hệ sinh thái SKAAA được xây dựng vững chắc dựa trên 3 trụ cột cốt lõi (The Trinity) và 1 Addon trí tuệ nhân tạo:

1.  **System Design (Skaaa No-Code Design):** Xử lý bộ khung giao diện nguyên tử (Atomic Blocks) và Design Engine (Tailwind CSS v4, Local JIT Compiler, Skaaapine Engine mô phỏng Alpine.js tương tác thời gian thực). Tích hợp sẵn bộ parser chuyển đổi mã nguồn `html2tailwind`.
2.  **Key Database (Skaaa Data Pro):** Hệ thống cơ sở dữ liệu bảng phẳng phẳng MySQL (`skaaa_data_*`) thay thế hoàn toàn mô hình EAV (`wp_postmeta`). Cung cấp Schema Manager tạo bảng tự động, DataGrid Strategy, và các cổng truy cập dữ liệu tĩnh **Integration REST APIs**.
3.  **AI Automation (Skaaa Logic Engine):** Bộ não điều khiển luồng hoạt động kéo thả đồ thị (DAG Graph Canvas). Chịu trách nhiệm về luồng sự kiện (Events Pipeline), biểu thức nội suy dữ liệu (SkaaaFX DSL) và bắt tín hiệu sự kiện từ bên ngoài thông qua **Webhooks**.
4.  **AI Integration (Skaaai - Plugin mới):** Addon chuyên biệt tích hợp các trạm xử lý AI (AIPromptNode, AIParserNode, Semantic Classifier) kết nối trực tiếp với LLM API (Gemini/OpenAI) và chạy các Agentic Workflows tự chủ.

---

## 2. DIRECTORY ARCHITECTURE (SKAAA ECOSYSTEM)
```text
skaaa-ecosystem/
├── .skaaa-ai/ (BRAIN)        -> .cursorrules, system_map, memory/, modules-docs/
├── wp-content/themes/
│   └── skaaa-canvas/         -> [THEME] Blank canvas, zero CSS/JS overhead
└── wp-content/plugins/
    ├── skaaa-no-code-design/ -> [UI/UX] Base Blocks + Tailwind JIT + html2tailwind
    ├── skaaa-data-pro/       -> [DATA] Flat Tables Schema + DataGrid + Integration REST APIs
    ├── skaaa-logic-engine/   -> [LOGIC] DAG Graph Editor + SkaaaFX + Webhooks
    └── skaaai/               # [AI ADDON] Prompt Node, Structured Parser & Agentic Flow (Gemini/OpenAI)
```

---

## 3. BẢN ĐỒ PHÂN CHIA TRÁCH NHIỆM (BOUNDARY ISOLATION)
Để tuân thủ triết lý **Decoupled Monolith**, các plugin giao tiếp hoàn toàn qua WordPress Action/Filter hooks và Alpine.js global store (`Alpine.store`), tuyệt đối không gọi class chéo nhau trực tiếp.

*   **Design ➔ Logic**: Gửi dữ liệu form submit lên endpoint để kích hoạt workflow.
*   **Logic ➔ Data**: Các node `DBQueryNode` và `DBActionNode` gọi WP Filters để đọc/ghi dữ liệu vào các bảng phẳng của Data Pro.
*   **Skaaai ➔ Logic**: Đăng ký các Node AI mới vào bộ đăng ký tập trung của Logic Engine thông qua registry filter `skaaa_logic_registered_nodes`.

---

## 4. DEVELOPMENT ROADMAP
*   **Phase 1 & 2 (COMPLETED):** Kiến trúc nền tảng (Base Blocks, Tailwind JIT, Flat Tables Schema, App Portals, shadow scratchpad).
*   **Phase 3 (COMPLETED - Pluggable Nodes):** Hoàn thành Registry Node tập trung (`Skaaa_Node_Registry`), Sidebar nạp động và Extensions Manager bật/tắt vật lý plugin addon trên Dashboard.
*   **Phase 4 (CURRENT - AI & Monolith Rebranding):**
    *   **Bước 1**: Đổi tên toàn bộ codebase và MySQL tables sang thương hiệu **SKAAA**.
    *   **Bước 2**: Quy hoạch lại vai trò (Phân rã Bridge về Design, Data, Logic).
    *   **Bước 3**: Khởi tạo plugin mở rộng **Skaaai** và viết Node AI đầu tiên (`AIPromptNode`).
    *   **Bước 4**: Triển khai `AIParserNode` trích xuất thông tin JSON có cấu trúc bằng Gemini/OpenAI API.
    *   **Bước 5**: Hoàn thiện kịch bản tự động hóa thông minh (Form Auto-responder) để kiểm thử E2E.

---

## 5. TECHNICAL CONSTRAINTS
*   **No-Postmeta Rule**: Tuyệt đối không lạm dụng `wp_postmeta` để lưu trữ dữ liệu ứng dụng. Bắt buộc tạo và dùng Flat Tables MySQL.
*   **Agnostic Core Libraries**: Mã nguồn lõi của `Skaaapine`, `SkaaaFX` và `Tailwind JIT` phải viết sạch sẽ, hoàn toàn không phụ thuộc WordPress để có thể đóng gói Composer/npm sau này.
*   **i18n Compliance**: Mọi chuỗi hiển thị trên UI mặc định viết bằng tiếng Anh bọc hàm dịch đa ngôn ngữ chuẩn của WP. Comments code viết bằng tiếng Việt.
*   **Zero-Trash Policy**: Không tự ý tạo tệp `.md` ngoài 4 ngăn kéo tài liệu.