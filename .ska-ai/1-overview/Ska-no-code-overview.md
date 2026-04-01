# SKA NO-CODE ECOSYSTEM - MASTER PLAN (AI-READABLE)
@version: 2.0.0 | @stack: WP-Core, Tailwind JIT, Flat Tables, Logic Engine | @focus: Framework-Agnostic App Builder

## 1. TẦM NHÌN HỆ SINH THÁI (THE CORE FOUR ARCHITECTURE)
Chúng ta đang chuyển dịch từ một "Page Builder" thông thường sang một **No-code App Builder Framework**. Hệ thống được tách nhỏ tuyệt đối thành các Module độc lập để tối ưu hóa hiệu năng (Enterprise Scale) và khả năng mở rộng. WordPress từ nay chỉ mang vai trò "Vật chủ" (Host) cung cấp Admin UI và Authentication.

**The Core Ecosystem (Bộ Quyền Lực):**
1. **Ska No-Code Home (Plugin):** Hệ điều hành trung tâm, quản lý mọi thiết lập, giấy phép và định tuyến cho toàn bộ hệ sinh thái.
2. **Ska No-Code Design (Plugin - trước đây là Core):** Xử lý bộ khung chuẩn (Base Atomic Blocks) và Design Engine (Tailwind CSS v4, Local JIT Compiler, React UI).
3. **Ska Data Pro (Plugin):** Hệ thống Database tự trị. Tách biệt hoàn toàn khỏi `wp_posts` và `wp_postmeta` (Bỏ EAV). Sử dụng bảng phẳng (Flat Tables: `ska_data_*`). Hỗ trợ Schema Manager, Query Builder (Filter, Sort, Aggregate, JOIN).
4. **Ska Logic Engine (Plugin):** "Bộ não" điều khiển các Workflow, Trigger-Action và kết nối mạch não UI với Data.

*(Extra)* **Ska Bridge:** Công cụ chuyển đổi HTML (html2tailwind) và trích xuất JSON cho kiến trúc Next.js (Headless).
*(Extra)* **Ska Theme:** Cốt lõi siêu nhẹ (Barebone), zero CSS, chống mọi conflict từ WP Theme.

## 2. DIRECTORY ARCHITECTURE (DỰ KIẾN PHASE 2)
```text
ska-ecosystem/
├── .ska-ai/ (BRAIN)        -> .cursorrules, system_map, memory/, modules-docs/
├── wp-content/themes/
│   └── ska-blank-theme/    -> [THEME] Barebone WP Theme
└── wp-content/plugins/
    ├── ska-no-code-home/   -> [MASTER] Ecosystem Dashboard
    ├── ska-no-code-design/ -> [UI/UX] Base Blocks + Tailwind v4 Compiler
    ├── ska-data-pro/       -> [DATA] Flat Tables Schema + Query Builder
    ├── ska-logic-engine/   -> [LOGIC] Workflows + Events
    └── ska-bridge/         -> [ADAPTER] Import/Export
```

## 3. CHIẾN LƯỢC SKA DATA PRO (XƯƠNG SỐNG MỚI)
- Lõi hiệu năng: Bảng phẳng (`ska_data_*`) thay vì `wp_postmeta`.
- Tính năng: Schema Manager trực quan tạo bảng tự động, Query Builder cho phép Filter/Sort/SUM/AVG không cần viết câu lệnh SQL.
- Hybrid: Có khả năng nối gót (JOIN) ngược lại với `wp_posts` để lấy Meta nếu khách hàng muốn lai tạp WordPress chuẩn.

## 4. DEVELOPMENT ROADMAP
- **Phase 1 (COMPLETED):** Kiến trúc nền tảng (Base Blocks, Editor UI, Regex Tailwind v3).
- **Phase 2 (Decoupling & Data Pro):** Phân tách ra 4 Plugins + 1 Theme. Xây dựng Ska Data Pro (Flat tables) và Nâng cấp Tailwind v4 cho Design Engine.
- **Phase 3 (Logic Engine):** Event Triggers & Action Workflows.
- **Phase 4 (Ecosystem):** JSON Bridge & Headless Next.js.

## 4. CONVERSION STRATEGY (MANUAL VS. AI)
| Type | Scope | Method |
| :--- | :--- | :--- |
| **Deterministic** | Ska Blocks, SCF Data, Design Tokens, Routing | Manual Code (Fast, 100% Stable) |
| **Probabilistic** | 3rd Party HTML/CSS, Legacy Code, Complex Logic | AI Intervention (Refactor to Tailwind) |

## 5. TECHNICAL CONSTRAINTS & PRD
- **Prefix:** Use standard Tailwind classes. Avoid custom prefixes.
- **Data Integrity:** JSON Output must be "Clean" (No WP-specific junk).
- **Performance:** 90+ PageSpeed score. Modular loading only.
- **Nested Loops:** Key differentiator. Must handle Parent-Child ID context passing.
- **Scalability:** Support 1000s of records (LMS/Booking focus).

## 6. MODULE REGISTRY (STATUS)
- **Design Engine:** 🟢 Implemented (Alpha) | Local JIT & Hybrid CDN.
- **Data Engine:** 🟢 Implemented (Beta) | SCF/WC Binding.
- **Atomic Blocks:** 🟢 Implemented | Core layout & text blocks.
- **Logic Engine:** 🟢 Implemented (v1) | `{{#if}}` & `{{#foreach}}` tags.
- **Bridge System:** 🟢 Implemented (v1) | html2tailwind (Import) & Next.js Mapping (Export).

**AI Instruction:** Use this file as the primary source for Project Scope. Refer to `.ska-ai/modules-docs/` for specific implementation details of each component.