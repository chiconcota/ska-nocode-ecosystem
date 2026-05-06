# SYSTEM MAP: SKA NO-CODE (v1.0.0)
@status: STABLE PHASE 3 DONE | @last_update: 2026-05-04

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
| **Ska Logic Engine** | `plugins/ska-logic-engine/`| Event Pipeline, SkaFX, Universal Binding | 🟢 Stable (MVP Primitives) |
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

- **2026-05-04 - 🟢 Chốt Kiến Trúc Ska Theme Builder (The Ultimate Hybrid):** Chính thức loại bỏ FSE (Full Site Editing) để giải quyết dứt điểm mâu thuẫn giữa "App Builder" và "Website Builder". Triển khai "Gutenberg as a Component Engine" với "Smart Virtual Wrapper" (đánh chặn hook `template_include`). Dữ liệu Theme (Header/Footer/Single) sẽ được quản lý bằng Ska Theme Panel (Alpine.js + Tailwind) độc lập và lưu trực tiếp vào bảng phẳng `ska_data_sys_organisms`, tuyệt đối không dùng `wp_postmeta`.
- **2026-05-04 - 🟢 Thiết kế Kiến trúc Smart Virtual Wrapper:** Quyết định giải pháp "Tách rời lũy tiến" (Progressive Decoupling) để trung hòa chức năng Theme Builder và App Builder. Xây dựng nền tảng `virtual-wrapper.php` thay thế hoàn toàn cấu trúc DOM của Theme cũ khi cần xây App độc lập. Lưu lại chi tiết nghiên cứu mâu thuẫn vào `chua-quyet-dinh-duoc.md` để triển khai trong Phase 4.
- **2026-05-04 - 🟢 Tối ưu Hóa Hệ thống & Fix Lỗi Ska Loop Conditional Rendering:** Nâng cấp Design Engine (`class-style-manager.php`) để sửa lỗi bỏ sót class `responsive` và `pseudo-class` bằng cơ chế merge class thông minh. Bổ sung cơ chế *Case-Insensitive Resolution* cho SkaFX, và tối ưu đánh giá logic *Truthy* (`1`, `"1"`, `true`) cho Ska Loop nhằm chống rớt dữ liệu trong mọi trường hợp Boolean Database. Dọn dẹp sạch debug code giúp Loop Block chạy siêu tốc trên Production.
- **2026-05-04 - 🟢 Nâng cấp Ska Loop (Structural Container):** Khai tử kiến trúc dàn trang "Ghost Block" phụ thuộc CSS hack của Ska Query Loop. Chuyển đổi hoàn toàn block này thành một Layout Container độc lập, tích hợp TailwindPanel để người dùng tự do điều khiển Flex/Grid. Thiết lập một thẻ div bọc ngoài chuẩn mực để đồng bộ hoá 100% cấu trúc DOM giữa Editor và Frontend.
- **2026-04-30 - 🟢 Chuyển giao Phase 4 (Theme Builder - Loop Block):** Chính thức đóng hồ sơ Phase 3 sau khi ổn định bộ 9 Primitives. Bắt đầu triển khai giao diện React cho Ska Loop Block. Thiết lập Implementation Plan cho UI Inspector và cơ chế Slot Repeater.
- **2026-04-29 - 🟢 Hoàn tất Iterator/Loop Node (Group Node Architecture):** Triển khai thành công tính năng xử lý vòng lặp mảng cho Ska Logic Engine. Khác với thiết kế các node nối tiếp thông thường, Iterator sử dụng kiến trúc Parent/Group Node trên React Flow. Các node con được đặt bên trong sẽ được biên dịch JIT (Just-In-Time) bằng thuật toán Topological Sorting để tạo thành chuỗi thực thi tuyến tính trong mỗi vòng lặp. Cung cấp sẵn các biến ngữ cảnh (`$item`, `$index`, `$first`, `$last`) giúp người dùng dễ dàng thao tác với dữ liệu danh sách mà không vi phạm nguyên tắc Circuit Breaker.
- **2026-04-29 - 🟢 E2E Testing MVP Primitives Hoàn Tất:** Xác thực thành công toàn bộ luồng tích hợp của DB Query Node và Render Template Node. Cơ chế Smart Fallback của SkaFX Engine phát huy tác dụng tối đa, cho phép Nocode User gọi biến tĩnh không cần tiền tố `payload.`. Gói lõi tự động hóa Ska Logic Engine (MVP) chính thức đóng băng (Stable) để chuyển hướng sang Phase 4: Thiết kế Ska Molecule.

- **2026-04-28 - 🟢 Nâng cấp Render Template Node (Decoupled Architecture):** Chuyển đổi mô hình Render Template từ "Node tự truy vấn DB" sang mô hình "Cỗ máy nội suy thuần túy" (Pure Interpolation Primitive). Hỗ trợ nhận diện 2 nguồn Source Type: 1) System Organism ID. 2) Dữ liệu Raw Variable/Text truyền từ payload qua SkaFX. Giờ đây người dùng có thể tự do lấy HTML custom từ Ska Data Pro bằng DB Query Node, sau đó truyền vào Render Template để xử lý linh hoạt.
- **2026-04-28 - 🟢 Hoàn thiện DB Query Node & Mở rộng SkaFX:** Triển khai thành công `[D2] DB Query Node` hỗ trợ truy vấn (Read) dữ liệu linh hoạt từ CSDL bảng phẳng với điều kiện động. Nâng cấp bộ máy nội suy SkaFX Engine: hỗ trợ thuộc tính đếm mảng `.length`, thêm hàm built-in `LIST_COL` trích xuất cột, và cơ chế Smart Fallback tự động dọn dẹp tiền tố `payload.` giúp tối ưu hóa UX Nocode.
- **2026-04-28 - 🟢 Brainstorm Kiến Trúc Trigger (Frontend vs Backend):** Thống nhất ranh giới trách nhiệm cực kỳ rõ ràng giữa Frontend và Backend. Các sự kiện Giao diện (UI Interactions như Cuộn chuột, Hiện Popup sau 10s, Bật tắt Modal) BẮT BUỘC phải do **Ska Design Engine (Alpine.js)** xử lý nội bộ trình duyệt để đảm bảo độ trễ bằng 0. Logic Engine (Backend) chỉ lo liệu dòng chảy tự động hóa dữ liệu (Lưu DB, Gọi API ngoài) để giảm tải Server.
- **2026-04-28 - 🟢 Brainstorm Webhook & System Hook:** Khẳng định chiến lược dùng `[T1] Trigger Node` tiếp nhận `Webhook URL` để giao tiếp với các nền tảng bên ngoài (Stripe, Momo). Các sự kiện nội bộ của site (VD: Đăng ký thành viên) sẽ được triển khai bằng `System Hook` với độ trễ 0.001s, nghiêm cấm dùng Webhook cho tác vụ cục bộ để tối ưu Network Overhead. Lùi kế hoạch tính năng "Template Settings (Display Rules)" của Popup sang giai đoạn Theme Builder.
- **2026-04-28 - 🟢 Tinh gọn Kiến Trúc Lõi (9 Core Primitives):** Hợp nhất thành công Action Click vào Event Trigger để tạo ra khối `[T1] Trigger Node` toàn năng với đa dạng nguồn kích hoạt (Form/AJAX, Webhook, Cron). Chính thức bổ sung `[D2] DB Query Node` vào lộ trình MVP nhằm lấp đầy khoảng trống truy vấn đọc dữ liệu. Toàn bộ 9 Hạt cơ bản đã được tài liệu hóa thành chuẩn mực trong hệ sinh thái tại `primitive-nodes.md`.

- **2026-04-27 - 🟢 Cập Nhật Kiến Trúc 7 Hạt Cơ Bản (True Primitives):** Chốt định hướng kiến trúc Node của Logic Engine với 7 Hạt Cơ Bản: Trigger, Set Payload, If/Else/Loop, DB Action, Raw HTTP Request, Render Template, và Client Response. Kiến trúc này đóng vai trò nền tảng để xây dựng mọi kịch bản tự động hóa phức tạp (Email Marketing, Modal, Redirect) bằng cách lắp ghép các primitive nhỏ mà không làm phình mã nguồn. Bổ sung định hướng `Render Template` + `Raw HTTP Request` thành Composite Node `Send Email Marketing` trong tương lai.
- **2026-04-27 - 🟢 Kiến Trúc Phân rã & Hoàn thiện Client Action Nodes (Phản hồi UI):** Chốt phương án phân tách "UI Response" thành 3 Node độc lập (`[C1] Client Redirect`, `[C2] Client Notification`, `[C3] Client State`) để bảo vệ triết lý Primitive Node của Hệ sinh thái DAG, tránh tạo ra khối lệnh quá tải trách nhiệm. Đã hoàn thiện xong ClientResponseNode (Settings Panel UI + Logic backend) để ném sự kiện (`toast`, `redirect`, `modal`) xuống `ska-core.js` thông qua payload `_ska_events`.
- **2026-04-27 - 🟢 Nâng cấp Ska Button (Hợp nhất Logic Action):** Hợp nhất tùy chọn Trigger Popup và Trigger Logic API trong khối `ska-button` thành một lựa chọn duy nhất là "Trigger Logic Workflow". Mọi hành động mở Modal từ giao diện giờ đây đều được gọi từ Logic Engine thông qua Client Response Node, giúp tách bạch trách nhiệm UI và Logic hoàn toàn.
- **2026-04-27 - 🟢 Hoàn thành Raw HTTP Request (GET) & Action Click Trigger:** Hoàn thiện `Ska_Logic_Http_Request` node cho phép gọi API GET/POST ngoại vi, kết hợp cơ chế Nội suy biến `{{...}}` thông minh. Đồng thời nâng cấp kiến trúc kích hoạt Event bằng `Action Click Listener` (class `.ska-action-[workflow_id]`), tháo gỡ hoàn toàn sự phụ thuộc vào HTML Form, mở đường cho hệ thống UI Popup/Smart Object tự do điều khiển luồng Logic.
- **2026-04-26 - 🟢 Debug Hoàn tất Luồng Dữ liệu Ska Logic Engine & Giao diện Node:** Sửa lỗi giao diện `SetDataNode` không hiển thị phần mô tả (description) do bị ghi đè bởi React UI. Hướng dẫn người dùng sửa cú pháp điều kiện trong khối If/Else để máy chủ SkaFX trả về boolean hợp lệ thay vì lỗi rỗng. Xác nhận chuỗi pipeline từ Form Frontend đến MySQL Insert chạy hoàn hảo 100%. Sửa lỗi nghiêm trọng khiến hệ thống từ chối lưu dữ liệu vào Database mặc dù báo thành công do `DBActionNode` thiếu interface `Ska_Logic_Node`. Bổ sung fix lỗi ghi đè Auto-map khi field bị null, hướng dẫn người dùng sử dụng Data Picker trỏ thẳng Root biến. Tích hợp cơ chế **Smart Fallback** cho SkaFX và DB Action Node, xử lý mượt mà việc người dùng điền chuỗi tĩnh (Literal strings/Template interpolation) thay vì báo rớt do Syntax Error.
- **2026-04-24 - 🟢 Hoàn thành Primitive Node: Set Data & Gán Biến (Ska Logic Engine):** Triển khai xong node cơ sở đầu tiên `Set Data` trong kiến trúc DAG. Node này thực hiện nguyên lý Mutate-by-Reference thông qua payload. Tích hợp SkaFX engine để parse và tính toán biểu thức động (Dynamic Expressions) cho phép gán trực tiếp dữ liệu phức tạp hoặc biến có tên tiếng Việt vào payload cho các bước sau sử dụng.
- **2026-04-24 - 🔴 Tái Cấu Trúc Lõi (Primitive & Composite Nodes):** Chuyển đổi định hướng thiết kế Node Logic từ các "Node đóng hộp" (như Send Slack) sang mô hình khối cơ sở (Core Primitives: Trigger, If/Else, JSON, Raw HTTP, DB Action). MVP sẽ tập trung giao hàng nhóm Primitives để tăng tốc độ phát triển. Composite Nodes (nhóm các Primitive thành Macro) sẽ được đưa vào Post-MVP. Cấu trúc lõi được trang bị thêm `Circuit Breaker` (chống đệ quy vô hạn) và `Data Vector / Batch Processing` (chống N+1 queries khi chạy vòng lặp) để đảm bảo Performance.
- **2026-04-24 - 🟢 DAG Builder UI (React Flow & BaseNode):** Triển khai thành công giao diện Canvas DAG 2D với thư viện React Flow v11. Tích hợp `ReactFlowProvider` hỗ trợ Sidebar Drag & Drop. Đã dựng `Settings Panel` với cơ chế đồng bộ 2 chiều (Real-time 2-Way Binding) và chuẩn hóa cấu trúc component `BaseNode` làm nền móng để đúc 10 Atomic Nodes ở phiên kế.
- **2026-04-24 - 🔴 Pivot Tối Ưu UX (Logic Engine):** Chấp nhận tạm hoãn tiến độ Ska Query Loop Block (đã chuyển sang file tracking độc lập) để tập trung toàn lực "đập đi xây lại" giao diện của Ska Logic Engine (Node Workflow UX). Quyết tâm đẩy Logic Engine đạt tiêu chuẩn Nocode 100% thông qua Schema API, Component Data Picker (chọn biến trực quan), và thuật toán Auto-Mapping thay vì bắt người dùng gõ tay.
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
- **🔴 [PENDING] Ska Logic Engine (Automation Platform):** Nâng cấp Logic Engine thành một nền tảng Automation với giao diện 2D Canvas Graph (giống n8n). Hỗ trợ kiến trúc DAG (Directed Acyclic Graph) để rẽ nhánh (Success/Error), đa dạng hóa Trigger (Webhook, Cron), chạy nền (Async Process), và đặc biệt hỗ trợ **AI JSON Blueprint Import** để AI thiết kế 100% logic tự động.
- **🟢 [POST-MVP] Ska Scripts Library & `ska-code`:** Hệ thống quản lý mã nguồn JS/CSS tập trung và khối `ska-code` thông minh. Tích hợp cấu hình vị trí nạp (Header/Footer) và phạm vi nạp (Global/On-demand) thay thế hoàn toàn Custom HTML, tự động chống lặp (Deduplication).