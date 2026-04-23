# SYSTEM MAP: SKA NO-CODE (v1.0.0)
@status: STABLE PHASE 3 DONE | @last_update: 2026-04-20

## 1. TECH STACK (APP BUILDER ARCHITECTURE)
- **Backend:** WP Core (Host) + PHP 8.2+
- **Data (Ska Data Pro):** Flat Tables (`ska_data_*`), Schema Manager, Custom Query Builder.
- **Design (Ska Design Engine):** Tailwind CSS v4, Local JIT Compiler, React + Gutenberg API, Alpine.js (Ska Molecule).
- **Logic (Ska Logic Engine):** Workflows, Trigger-Action events.
- **Bridge:** JSON Schema mapping, html2tailwind, Next.js (Headless).
- **Pattern:** Micro-Ecosystem (Decoupled Plugins), Interface-based, Event-driven (WP Hooks).

## 2. PROJECT STRUCTURE (MICRO-ECOSYSTEM)
ska-ecosystem/ (mapped to app/public/)
├── .ska-ai/                 # [OVERSEER] Central Brain & Rules
│   ├── 1-overview/          # System Map & Ecosystem Vision
│   ├── 2-memory/            # Decision logs & Versioning
│   ├── 3-ecosystem/         # Isolated boundaries docs for Plugins & Themes
│   └── 4-rules/             # Global coding rules
├── wp-content/themes/
│   └── ska-blank-theme/     # [THEME] Barebone WP Theme (Zero CSS)
└── wp-content/plugins/
    ├── ska-no-code-design/  # [UI/UX] Base Atomic Blocks, Tailwind JIT, Inspector, Alpine.js (Ska Molecule)
    ├── ska-data-pro/        # [DATA] Flat Tables, Schema, Query Builder
    ├── ska-logic-engine/    # [LOGIC] Workflows, If/Foreach Triggers
    └── ska-bridge/          # [ADAPTER] html2tailwind & JSON API

## 3. CORE LOGIC FLOWS (DECOUPLED VIA HOOKS)
### F1: The Render Pipeline (Frontend)
1. **Core:** Renders static HTML string (e.g. `<div class="bg-red-500">...</div>`).
2. **Design Engine:** Filters HTML, extracts Tailwind classes, generates `<style>`.
3. **Data/Logic Engine:** Filters HTML, replacing `{{tags}}` with Database query results.

### F2: The Editor Pipeline (Backend)
1. **Core:** Registers Blocks via `block.json` & React.
2. **Design Engine:** Injects `TailwindPanel` into InspectorControls via WP JS filters.
3. **Data Engine:** Replaces placeholders in real-time.

## 4. MODULE REGISTRY & STATUS
| App Module | Path | Responsibilities | Status |
| :--- | :--- | :--- | :--- |
| **Ska Canvas (Theme)** | `themes/ska-canvas/` | Nullify WP CSS, Blank Canvas | 🟢 Stable (v1) |
| **Ska No-Code Design** | `plugins/ska-no-code-design/`| Core Blocks, Tailwind JIT, Ska Molecule | 🟢 Stable Phase 3 |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Flat Tables DB, JSON Native Schema | 🟢 Stable Phase 3 |
| **Ska Logic Engine** | `plugins/ska-logic-engine/`| Event Pipeline, SkaFX, Universal Binding | 🟢 Stable Phase 3 |
| **Ska Bridge** | `plugins/ska-bridge/` | html2tailwind, API (JSON Export) | 🟢 Integrated |

## 5. SYSTEM CAPABILITIES (CHECKPOINTS - PHASES 1-3)
Dù chi tiết quyết định đã được lưu vào `archive/decision-log-phase-1-2.md`, đây là bản đồ năng lực (trạng thái) hệ thống đang sở hữu trước khi vào Phase 4:
- **Ska Design Engine (JIT Tailwind v4 & Alpine.js):** Gói lõi xử lý Giao diện bao gồm Biên dịch CSS thời gian thực tại Local (JIT) và Quản lý State/Interaction trên Frontend (Ska Molecule/Alpine.js). Đã xử lý triệt để CSS Specificity Parity, Flat DOM, và chuyển hóa HTML sang Tailwind thông minh (`Ska Bridge`). Hỗ trợ mượt mà Pseudo-classes, Grid/Flex layout, Animation phức tạp.
- **Ska Data Pro (Flat Tables First):** Xóa xổ hoàn toàn rác EAV (`wp_postmeta`). Hệ thống DB quản trị bởi Schema Manager độc lập, có sức mạnh xử lý Rollup (Lookup Virtualization), Heuristic Filters, Auto-Prefix, và xuất nhập dưới dạng **Smart Object Blueprint (JSON)**.
- **Ska Logic Engine (The Trinity):** Kiến trúc Event-Driven kết nối Vỏ (UI), Nhân (Logic), Kho (Data). Đã trang bị ngôn ngữ biểu thức **SkaFX DSL** (AST Evaluator) phục vụ Universal Dynamic Binding (Hiển thị có điều kiện & Inner Text nội suy) ngay trong thiết kế kéo thả. Mọi thao tác form đều tuân thủ nguyên lý Nonce chặt chẽ và Data Healing thông minh.
- **Skapine Engine (Ska Molecule):** Nhúng sâu thư viện Alpine.js directives (`x-data`, `x-bind`, event listeners) cùng với Tailwind JIT lên Gutenberg Editor, đảm bảo live-preview mọi thao tác tương tác giao diện.

## 6. GLOBAL CONSTRAINTS (FOR AI)
1. **Micro-Ecosystem Boundary:** Plugins DO NOT call each other's classes directly. They communicate exclusively via WP `do_action` and `apply_filters`.
2. **Flat Tables First:** All future data models must rely on Ska Data Pro's custom tables (`ska_data_*`), completely abandoning `wp_postmeta`.
3. **Zero-Trash Policy:** Mọi tài liệu bắt buộc phải nằm gọn bên trong 4 ngăn kéo (.ska-ai/1-overview, 2-memory, 3-ecosystem, 4-rules). Tuyệt đối không sinh rác file .md lung tung (Theo điều luật `ska-docs-management.md`).
4. **Single Source Of Truth (SSOT):** Tài liệu phải liên tục được ghi đè, không lưu vết lan man. Dùng Lệnh Replace, không dùng Lệnh Write bừa bãi.
5. **Build Sync Confirmation:** AI BẮT BUỘC phải hỏi ý kiến người dùng trước khi thực hiện `npm run sync`.

## 7. RECENT LOGS (LATEST)
> *Các nhật ký từ Phase 1 và 2 đã được lưu trữ trong `.ska-ai/2-memory/archive/`. Chỉ giữ lại các cập nhật cốt lõi gần đây (Phase 3 -> Phase 4).*

- **2026-04-23 - 🔴 Khủng hoảng UX Nocode Form & Yêu cầu Pivot:** Tạm ngưng triển khai hệ thống Form Backend hiện tại. Nguyên nhân do mức độ phức tạp quá cao đối với non-coder khi phải tự gán thủ công `x-data`, `fields.*`, và `status.*` vào HTML Attributes. Quyết định sẽ thiết kế một 'Abstraction Layer' mới (Form Builder Wizard hoặc khối form tự map data) trong phiên tới.
- **2026-04-23 - 🟢 Khắc phục Alpine Form Integration (Frontend Binding):** Xử lý triệt để lỗi bất đồng bộ Alpine.js và Ska Logic Engine tại Frontend bằng cách thiết lập cấu trúc tải file chính xác (`alpine.min.js` phải load sau `ska-frontend.js`). Cập nhật và đồng bộ 100% Data Architecture giữa HTML Form (`x-data="skaForm('doctor_data')"`, `fields.*`) với cấu hình REST API Submit, cho phép người dùng Post dữ liệu trực tiếp vào hệ thống DB (Workflow `doctor_data`) trơn tru.
- **2026-04-23 - 🟢 React Inspector cho Ska Select:** Hoàn tất hệ thống UI Inspector (React) cho block Ska Select, tích hợp Ska Data Pro Dictionary để bật/tắt nguồn dữ liệu động. Hệ thống Frontend Logic Engine đã được nâng cấp để hỗ trợ cơ chế Template Auto-Generation, cho phép Dropdown tự động render danh sách các option (cột `select/radio/checkbox`) với cấu hình JSON tối giản, chuẩn Zero N+1 Queries.
- **2026-04-22 - 🟢 Ska Loop Block (Backend Core) & Hospital Data Template:** Xây dựng thành công hệ thống Backend Render cho Vòng lặp hiển thị dữ liệu (Data Loop). Áp dụng kiến trúc Zero N+1 Queries thông qua Bulk Loading và Hydration siêu tốc độ bằng biểu thức Mustache `{{key}}`. Cập nhật SkaFX Lexer hỗ trợ tiền tố `$`. Khởi tạo Template `hospital` phục vụ test dữ liệu bác sĩ.
- **2026-04-22 - 🟢 Global Edit (Shadow CPT & Modal Iframe):** Triển khai thành công tính năng "Sửa Bản Gốc" cho Ska Organisms. Sử dụng kiến trúc Shadow CPT (`ska_organism_draft`) chạy trong môi trường Iframe cách ly. Đã xử lý triệt để lỗi ghi đè tên `name` bằng cách tước bỏ thuộc tính `title` của CPT ảo và bổ sung lớp Interception bảo vệ an toàn cho MySQL Database và System Cache.
- **2026-04-22 - 🟢 Fix SPA Lợi hại (Data Injection):** Xóa bỏ độ trễ của Cache khi vừa Lưu mới Organism. Tự động Data Injection thông tin Organism mới thẳng vào `window.skaOrganismsCache` phía Client, giúp tính năng Preview hoạt động Real-time ngay lập tức. Sẵn sàng di chuyển vào Phase 4 - Local Edit Môi trường đóng.
- **2026-04-21 - 🟢 Tối ưu & Vá Lỗi Ska Symbols Editor:** Sửa dứt điểm lỗi "Block rendered as empty" trong Editor do khác biệt cơ chế Output Buffering của hàm `render_block` (WordPress 6.1+). Mã nguồn `render.php` của khối tham chiếu chuyển từ dạng trả đồ về kiểu in thẳng (`echo`) để bắt toàn bộ HTML. Đồng thời Whitelist Attributes trong `block.json` để loại bỏ mã lỗi REST API 400. Mở đường cho Phase chỉnh sửa nội tuyến.
- **2026-04-20 - 🟢 Ska Symbols (Phase 4.1): Editor UI & Zero-Query Storage:** Hoàn thiện luồng React UI cho Ska Symbols (Save as Organism). Khắc phục lỗi "bóng ma" bằng cách bổ sung `ska-organism-ref` vào quy trình Build Webpack. Áp dụng kỹ thuật Zero-Query cho Editor bằng cách `wp_localize_script`, nạp sẵn JSON Cache vào bộ nhớ giúp dropdown danh sách Symbol tải tức thời (0ms delay). Hệ thống DB cũng đã được chèn chính xác Schema `html_content` để hứng Full Dữ liệu.
- **2026-04-20 - 🟢 System Finalization: Packaging, Dashboard UI & User Manual:** Tinh chỉnh cấu trúc Build ZIP cho Plugin (giảm dung lượng), thêm Ecosystem Warning Banner tại Admin Dashboard và áp dụng Zero-Trash Directive. Hoàn thiện Toàn tập bộ Cẩm nang End-User (`docs/`).
- **2026-04-19 - 🟢 Ska System Multi-Tier Caching & Dashboard Integration:** Xây dựng hệ thống Caching đa tầng (`System_Cache`) cho Smart Object `app-site` (`ska_system`). Snapshot JSON Cache chống Database bị sập. Tích hợp Ecosystem Integration để quản lý an toàn dependencies.
- **2026-04-19 - 🟢 Lập kiến trúc Smart Object 'app-site':** Quản trị Custom Blocks (Ska Symbols), Theme Builder, Preset Settings quy về 1 mối trên `Ska Data Pro`. Tránh phình to DB.
- **2026-04-19 - 🟢 Hệ sinh thái Global State (Alpine.store):** Khởi chạy tính năng Global State Management cho Ska Builder để giao tiếp chéo giữa các Block độc lập. Skapine Editor Monkey-patching giúp giả lập 100% tương tác trong Editor.
- **2026-04-18 - 🟢 Nâng cấp UX Skapine Engine:** Bổ sung HTML Attributes OptGroup (chia nhóm logic thuộc tính hợp lý), thuật toán Smart Auto-fill (tự bơm logic mẫu), và Skapine Engine Preview Mode hỗ trợ trực tiếp các Event `onMouseEnter` / `onMouseLeave`.
- **2026-04-16 - 🟢 Hoàn thiện The Core Four & Ska Molecules:** Tích hợp Multi-Step Form Quiz/Wizard. Cập nhật Block Lock giúp khóa chặt cấu trúc giao diện Molecule.

## 8. FUTURE ROADMAP (PHASE 4: SKA SMART OBJECTS & MOLECULES)
- **Ska Smart Object (`app-site`):** Xây dựng hệ thống quản trị trung tâm cho Theme Builder, Custom Blocks (Ska Symbols), và Preset Settings dựa trên Flat Tables của Ska Data Pro. Lưu code HTML dưới dạng JSON Reference.
- **Ska Symbols (Reusable Components):** Lưu lại các block phức tạp dưới dạng template/components độc lập và chèn vào cấu trúc bằng thuộc tính `refId` thay vì nhân bản HTML phình to. Tích hợp Component trực tiếp vào Inserter (+) của Gutenberg.
- **Thư Viện Ska Molecules:** Phát triển các UI Components Nocode (Tabs, Accordion, Slider, Logic Modal) sử dụng Ska Universal Container kết hợp Alpine.js.
- **Ska Interactive Engine:** Cơ chế mô phỏng (Live preview) trạng thái của Alpine.js ngay bên trong Editor, biến JIT thành "React Virtual DOM của Tailwind", giúp Editor phản hồi "sống" với các hiệu ứng tương tác (xổ dropdown, chuyển form nội bộ) theo thời gian thực.