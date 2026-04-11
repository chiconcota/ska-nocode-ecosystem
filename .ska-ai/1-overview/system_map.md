# SYSTEM MAP: SKA NO-CODE (v1.0.0)
@status: MVP DONE | @last_update: 2026-03-28

## 1. TECH STACK (APP BUILDER ARCHITECTURE)
- **Backend:** WP Core (Host) + PHP 8.2+
- **Data (Ska Data Pro):** Flat Tables (`ska_data_*`), Schema Manager, Custom Query Builder.
- **Design (Ska Design Engine):** Tailwind CSS v4, Local JIT Compiler, React + Gutenberg API.
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
    ├── ska-no-code-home/    # [MASTER] Ecosystem Manager, Dashboard & Settings
    ├── ska-no-code-design/  # [UI/UX] Base Atomic Blocks, Tailwind JIT, Inspector
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
| **Ska Canvas (Theme)** | `themes/ska-canvas/` | Nullify WP CSS, Blank Canvas | 🟢 Done (v1) |
| **Ska No-Code Home** | `plugins/ska-no-code-home/`| Master Ecosystem Manager, Settings | 🔴 Plan Phase |
| **Ska No-Code Design** | `plugins/ska-no-code-design/`| Base Blocks, Tailwind JIT, Inspector UI | 🟢 Done (v1) |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Flat Tables DB, Schema, Templates | 🟢 Done (MVP & Packaged) |
| **Ska Logic Engine** | `plugins/ska-logic-engine/`| (Ska-xi măng) Bắt Sự kiện (Workflows), Xử lý Data & Logic (If/Then) | 🟢 Done (Manager UI & Linear Builder MVP) |
| **Ska Bridge** | `plugins/ska-bridge/` | html2tailwind, API (JSON Export) | 🟡 In Progress (html2tailwind Done) |

## 5. GLOBAL CONSTRAINTS (FOR AI)
1. **Micro-Ecosystem Boundary:** Plugins DO NOT call each other's classes directly. They communicate exclusively via WP `do_action` and `apply_filters`.
2. **Flat Tables First:** All future data models must rely on Ska Data Pro's custom tables (`ska_data_*`), completely abandoning `wp_postmeta`.
3. **Divide & Conquer:** File size < 500 lines. Exceed = Split.
4. **Context Switching:** Always read the specific plugin's documentation inside `.ska-ai/3-ecosystem/` trước khi thao tác.
5. **Build Sync Confirmation:** AI BẮT BUỘC phải hỏi ý kiến người dùng trước khi thực hiện `npm run sync`.

## 6. RECENT UPDATES
- **2026-04-11 - 🟢 Done - Semantic Slug & Universal Binding Logic Fixes:**
  - **Ska Data Pro:** Nâng cấp cỗ máy sinh ID bảng từ UUID vô nghĩa (`app_Do5p1uxz`) sang "Semantic Slug" (`app_viet_cong_cu_moi`) thân thiện với con người khi tạo App Blueprint dựa theo hàm `sanitize_title`. Triển khai tính năng phát hiện trùng lặp ID thông qua `WP_Error` trả thẳng ra Frontend UI thay vì tạo tiền tố `_1`, `_2` ngầm.
  - **Ska Logic Engine:** Nâng cấp `Ska_Dynamic_Content` (Universal Binding) để cho phép **Chuỗi Rỗng (`""`)** được phép Override vào văn bản tĩnh thay vì bị bỏ qua. Bổ sung bắt các hằng số Literals (`true, false, null`) vào `SkaFX_Evaluator` để chống lỗi AST parse biến sai khi người dùng nhập Text hằng số.
- **2026-04-09 - 🟢 Done - Cỗ máy SkaFX AST Evaluator:** Hoàn tất đúc lõi SkaFX Engine (Parser/Evaluator) bằng PHP. Trạm thông dịch AST có khả năng chạy Script nội bộ (`var x = y;`), xử lý hàm nội bộ (`IF`, `CONCAT`), phân loại Toán Tử với tốc độ ánh sáng `0 mili-giây`. Nuốt gọn mọi lỗi Syntax Error bảo vệ Website.
- **2026-04-10 - 🟢 Done - Xuất/Nhập Bản Thiết Kế Smart Object (App Blueprint Export/Import):** Đã chốt kiến trúc Blueprint dạng file `.json`. Phát triển cơ chế Xử lý Xung đột Ký danh (Auto Slug Re-wiring) và Hook Pipeline (`ska_export_smart_object`) giúp giữ vững quy tắc Decoupling, cho phép Logic Engine và Portals tự nạp dữ liệu vào Blueprint file mà không bắt Data Pro bóc tách. Hoàn thành phát triển logic tải file JSON và giao diện Import kéo thả. Sẵn sàng cho việc liên kết đa không gian.
- **2026-04-09 - 🟢 Done - Smart Object / App Blueprint Architecture (Phase 1):** Đại tu hệ thống Bảng `ska-data-pro` từ mô hình Group tĩnh (Text Category lỏng lẻo) sang định danh Application Workspace phân cấp (Quản lý qua Option API `ska_data_apps`). Triển khai Modal UI (Tạo/Sửa/Xóa App), Kéo bảng vào App bằng thẻ `<select>`, và xây dựng cơ chế Bảo Vệ (Safe Drop) - khi xoá App, tự động ném các Bảng vào môi trường an toàn thay vì Xóa SQL.
- **2026-04-08 - 🟢 Done - Universal Dynamic Binding:** Khởi chạy dự án Data Hydration. Hoàn tất Phase 1 đến Phase 4 (Động cơ Backend đục Frontend the_content & render_block). Chốt sổ kiến trúc Máy chém CSS (Visibility) và Bơm Máu Data (Hydration) qua khối CodeMirror Inspector.
- **2026-04-07 - Quản Lý Băng Chuyền Đa Không Gian (Logic Manager):** Triển khai mô hình Dual-View cho Logic Engine. Tách chế độ Danh Sách trực quan (Quản lý CRUD, Đổi tên) với chế độ Builder (Chuyên sâu logic) nhằm tránh người dùng NoCoder nhầm lẫn gây xóa dữ liệu (Đã khóa ID tại Mode Builder). Cập nhật Event Handler ở Backend chặn sự cố DOM Destruction gây nhiễu cho Chrome Extensions (Anti Promise-Break).
- **2026-04-07 - Hệ Cứu Hộ "Data Healing" & Schema Mapping UI (Ska Logic Engine):** Nâng cấp module Form Insert với bảng Mapping trực quan giữa Database MySQL và Biến nội suy do user tạo. Tích hợp Native Swap Up/Down cho hệ thống Băng Chuyền, gia tăng UX Nocode kéo thả không mã. Tự động Data Healing cho Client POST variables chứa khoảng trắng. Bước ngoặt hoàn tất MVP Architecture cho Logic Engine "The Trinity".
- **2026-04-07 - Hệ thống Băng Chuyền Linear Builder MVP (Logic Engine):** Hoàn tất giai đoạn MVP - Giao tiếp "The Trinity". Xây dựng "Linear Builder" bằng Vanilla JS thay thế NodeUI (Phase 4). Tái thiết lập mảng dữ liệu xuất bảng thành chuẩn `JsonGraph` đồng nhất dành cho core Pipeline Processor. Nâng cấp Linear Builder hỗ trợ Datalist tuỳ biến cho phép liên kết khối lượng Table khổng lồ trực tiếp (Kể cả Core Tables của WordPress).
- **2026-04-06 - Vá lỗ hổng Modifier Validation (JIT Compiler Leak):** Vá khẩn cấp lỗi vòng lặp parser của JIT Compiler. Xây dựng chốt lọc `$is_valid_modifiers` để hủy bỏ và tống xuất mọi lớp Tailwind có chứa tiền tố lạ phân loại (như `dark:text-white` hoặc `sida:bg-red`) vào mảng thẻ chưa giải quyết. Ngăn chặn tuyệt đối tình trạng compiler dại dột bóc tách phần đuôi class và tiêm Global CSS phá nát hệ sinh thái (tôn trọng triệt để quyết định Hoãn Dark Mode trước đó).
- **2026-04-05 - 🔵 [PHASE 3 INITIATION] Ska Form Engine & Tailwind Preflight Parity:**
  - **Ska Form (Container):** Cấu trúc Flat DOM an toàn, sử dụng `InnerBlocks.Content` trong hành vi `save()` để mở khóa HTML cho Frontend JIT Compiler quét class. Không quy chụp wrapper mặc định bằng cách cho qua quyền tự do nhúng Layout Grid qua `Ska Container`.
  - **Ska Input & Select (Atomic State):** Sửa soạn xong trạng thái Clean Slate. Từ chối cung cấp bất cứ Class Tailwind nào mặc định trong `block.json` để nhường toàn quyền "sinh sát" cho người thiết kế.
  - **Form Preflight Parity (Kiện Toàn JIT):** Nâng cấp hàm `get_core_reset_css` của Tailwind Config. Giải quyết triệt để lỗi viền đen của WP Admin Editor bằng cách tái lập cấu trúc `appearance: none` và `border-color: #e5e7eb` của hệ sinh thái Tailwind vào Core Code. Đảm bảo giao diện Frontend/Backend hòa làm một.
  - **Tailwind CDN Proxy Mutation (2026-04-06):** Khắc phục lỗi Race Condition khiến cờ `important: true` bị Tailwind CDN `v3` phớt lờ trong môi trường Iframe. Triển khai phương pháp `Proxy Mutation` (can thiệp trực tiếp vào Object `win.tailwind.config` sau khi Script Onload) kết hợp bơm Polyfill Layout CSS (`-outline-offset`) giả lập hệ V4. Đạt 100% Editor Visuality Parity cho khối Form. Bắt đầu Phase 3 an toàn.
  - **Button Reset Specificity Fix (2026-04-06):** Giảm Specificity của bộ Clean CSS cho nút bấm bằng cách lồng pseudo-class `:where()` vào selector Reset (VD: `button:not(:where(.components-button))`). Qua đó trải thảm xanh cho các class JIT Tailwind utilities tự đo ghi đè style (Padding, BG) khi hiển thị khối Form Submit.

- **2026-04-04 - ✅ [DONE] The Great Refactor & Packaging Ska Data Pro:**
  - Hoàn tất đập đi xây lại cấu trúc Javascript Backend nguyên khối 1200 dòng của DataGrid thành mô hình ES6 Modules, kết hợp biên dịch tối tân qua **Vite**. Output file giảm thiểu còn `18.82 kB (gzip: 5.22 kB)`.
  - Áp dụng quy chuẩn **Strategy Pattern** cho DataGrid Cell Engine: Các thao tác Inline Edit cho ô (Media, Boolean, Dropdown Options, Nhập Văn bản) được tách lập thành từng Strategy Class riêng biệt để giải quyết hiện trạng If/Else nhằng nhịt, sẵn sàng tiếp nhận thêm 50 loại Data Type trong Phase tới.
  - Sửa lỗi (Hotfix) mất Logic Rollup Cascading và Relation Search do bất cẩn đợt Refactor nguyên khối. Phục hồi qua `RelationCell.js` Component độc lập.
  - Nâng cấp API: Đồng bộ tất cả Request HTTP từ jQuery (`$.post`) sang ES6 Fetch Core (`apiFetch`) nhằm dứt điểm lệ thuộc thư viện. Tích hợp giải pháp Cache-Busting bằng `filemtime()` tự động cập nhật URL version sau mỗi lần biên dịch gói Vite để chống trình duyệt lưu Cache file `.js` thô thiển.
  - Triển khai kịch bản đóng gói `node-archiver` chuẩn xác cho Ska Data Pro. Hoàn tất chu kỳ phát triển MVP!
- **2026-04-03 - Nâng cấp Cỗ máy Rollup & Heuristic Filters:**
  - Hoàn thiện UI DataGrid bằng cách tích hợp trực tiếp **Heuristic Filter** vào SQL Query của AJAX Backend. Chặn đứng hàng trăm meta keys rác của WordPress (`_wp_%`, `session_%`, `_edit_%`) lọt vào khung tra cứu Rollup, đem lại trải nghiệm "Clean Slack" gọn gàng ở Dropdown.
  - Sửa lỗi UX (Async Race Condition & Stale Computed Data) bằng cách bắt buộc **trình duyệt Reload** (F5) ngay sau khi Lưu Tham Chiếu mảng Relation ID. Thay đổi tĩnh này buộc hệ thống Backend Server đẩy mảng ID mới chạy qua Data_Fetcher để "bơm" (Enrich) tức thời Data lên toàn bộ các Cột Toán Hạng (Rollup) cùng dòng bị phụ thuộc, xóa bỏ tận gốc cảnh user bấm lưu mà Rollup "trơ" như đá.
  - Phân phát thành công cấu trúc Cột Rollup với tính chất hoàn toàn Ảo (Virtual). Áp dụng chiến lược không lưu dữ liệu (NULL) xuống DB nhằm cắt đứt Data Redundancy, song song đó trang bị tính năng Gom mảng N+1 tại tầng Data_Fetcher giúp ép MySQL hoàn trả dữ liệu Rollup với tốc độ 1 cú `SELECT IN (...)`.
  - Giữa vững máy chiếu Shortcode `[ska_dump_table]` giúp Test giao tiếp API từ Frontend tiện lợi.
- **2026-04-01 - ✅ [DONE] Triển khai DataGrid Relation & Formula Engine Ready:**
  - Hoàn tất xây dựng Cổng Nối Bảng (Relation) theo chiến lược lưu CSV ID tại Bảng Phẳng `ska_data_*`.
  - Hook phân giải Enrichment Resolution được cấy êm ái tại tầng Lõi Data Fetcher, giúp đái ra cục JSON Objects sẵn sàng cho API / Logic Engine thụ hưởng (tránh giật lag Query 1-N ở View Layer).
  - Cập nhật Bypass bảo mật ở Backend Data Fetcher và AJAX Search để hỗ trợ tích hợp trực tiếp bảng truyền thống lõi của WordPress (`$wpdb->posts`, `$wpdb->users`) vào thẳng tính năng Cột Tham Chiếu (Relation) mà không cần cấu trúc Table mới. Mảng `json` phân giải sẽ tự động ánh xạ cấu trúc (Title, Name) thông minh.
  - Hoàn tất UI Popover Relation Search (Debounce) cho Grid Dashboard. 
  - Đóng gói Update sửa lỗi Cache Click Outsite Dropdown UI DataGrid và bổ sung đổi Label Icon `dashicons-edit` (Bút chì) cho Cột Editable.
- **2026-04-01 - Extensibility POC (Data Providers) & Logic Engine Hooking:**
  - Sửa lỗi nghiêm trọng (Plugin Load Order Bubble): Các Data Provider hiện tại bị WordPress Alphabetical Load Order bóp chế chết từ trong nôi do gọi quá sớm. Cập nhật dời luồng Load Adapter vào `init()` (`plugins_loaded`) để tương thích với Ska Builder Core.
  - Sửa lỗi Frontend không render biến: Bật Filter `the_content` (độ ưu tiên 90) cho Logic Engine để tự động biên dịch `{{...}}` ngoài Frontend.
- **2026-04-01 - Tạm Hoãn Formula Component & Hoàn thiện DataGrid Controls (Phân tích Data/UI):**
  - Pivot kiến trúc từ việc triển khai "Virtual Column Formula Engine" tốn kém thành việc tập trung hoàn thiện 3 tính năng DataGrid then chốt: Filter, Sort, Group.
  - Xây dựng DataGrid View State (URL-Driven) cho phép Frontend chia sẻ và load các Bộ lộc data trực tiếp thông qua URL `?filter_field=x&orderby=y`.
  - Triển khai "Nhóm dữ liệu" tối ưu (Zero-overhead Grouping): Data Backend dùng MySQL `ORDER BY` thuần túy, và PHP tự sinh dải Heading chia nhóm mỗi khi phát hiện bước nhảy dữ liệu nhằm giữ cho Data flow không bị phình to dưới dạng nested Array tĩnh.
- **2026-03-31 - Relational DB (Reference) & Compute Fields (Formula):** Lên kế hoạch mô hình hóa Dữ liệu Quan hệ cho Flat Tables. Chốt lưu khóa ngoại (Foreign Keys) dưới dạng chuỗi `TEXT` CSV để đáp ứng Multi-reference (1-N). Chốt phương án Render cột Formula thông qua hàm suy luận (Virtual Column logic) lúc Runtime Backend thay vì ép chết SQL format.
- **2026-03-31 - Ska Data Pro UX Vanguard:**
  - Hoàn thiện xử lý va chạm Z-index giữa WP Media Modal và Custom JS Popover của DataGrid.
  - Phế bỏ Default Checkbox, triển khai Component CSS Toggle Switch (1-click trigger AJAX) để tối đa hóa UX.
  - Tự động hóa lớp Query_Builder: Auto-prefix `ska_data_` nếu Dev truy xuất bằng Alias ngắn nhằm nâng cao độ thân thiện của APIs.
- **2026-03-30 - Brainstorm: Frontend App Portals (Unified Canvas):** Chốt hạ tư tưởng kiến trúc không sử dụng `wp-admin` và code thuần PHP để làm trang quản lý cho Sub-admin (VD: Kế toán, Giảng viên). Sử dụng phương pháp Single Canvas: Mở rộng `ska-builder-core` tạo ra CPT `App Portals` chuyên biệt, kết hợp RBAC của Logic Engine và Form CRUD của Data Pro để user tự cấu hình Giao diện Dashboard quản trị bằng kéo thả hoàn toàn trên Frontend ngoài.
- **2026-03-30 - Ska Data Pro Schema Core & UI:**
  - Hoàn tất Table CRUD (Tạo, Sửa Ký Danh, Xóa Bảng) và Column CRUD trên nền tảng `ska_data_dictionary` (Alias Label) giúp bảo vệ toàn vẹn MySQL query.
  - Tích hợp Dynamic App Category (`__table_info['group']`) vào Modals & Sidebar để tổ chức Database như một Workspace thư mục (E-Commerce, LMS, v.v).
  - Hoãn thực thi Quốc tế hóa I18n cho đến giai đoạn Packaging để tối đa tốc độ code MVP.
- **2026-03-29 - Phase 2 (Ska Data Pro) Initialization:** Brainstorm kết thúc. Chốt kiến trúc Flat Tables, Template Gallery, Data Providers (`wp_users`, WooCommerce). Khởi tạo giao diện Dashboard Admin bằng Tailwind UI tĩnh. Cập nhật roadmap tại `project_manager_phase2.md`.
- **2026-03-29 - Hardening Ska Builder CSS Engine & Editor Parity:**
  - **JIT Compiler Fractional Resolving:** Hỗ trợ tính tỷ lệ phần trăm (percent `%`) cho CSS width/height theo cấu trúc đuôi phân số của Tailwind CDN như `w-1/2` (50%), `w-1/3` (33.333333%).
  - **Inline Element Fix (Atomic Reset):** Ấn định block Ska Button luôn khởi tạo bằng class Default Inline-Flex (`display: inline-flex; align-items: center; justify-content: center;`) để Button/Link tag có thể nhận diện và thích ứng các class margin padding (-mt-10) mà vẫn bám sát "Clean Slate".
  - **Constrained Layout Nullification:** Trị dứt điểm căn bệnh Theme Default Content Size. Bằng cách gán `null` Layout trong `theme.json` và chèn base `margin: 0` từ JIT Compiler Reset CSS cho mọi Block (`[class*='wp-block-ska-builder']`), dỡ bỏ hoàn toàn đặc quyền `margin: auto` cấy ngầm vào khối.
  - **Editor Context Cleanup:** Giải nén cờ CSS `margin: 0 !important;` mặc định trong `ska-editor-helper.js` bị trỏ lầm vào block con, khai thông các hiệu ứng `mt-`, `mb-` từ Tailwind CDN trên Editor.
- **2026-03-29 - Deployment Standardization:** Triển khai Node Archiver thay thế Windows zip script để khắc phục triệt để lỗi giải nén plugin trên môi trường Linux/TasteWP ("Tập tin của plugin không tồn tại").
- **2026-03-28 - JIT Compiler Comprehensive Audit:**
  - **Color Palette (Critical):** Bổ sung đầy đủ 11 shade (50-950) cho 17 màu chromatic. Trước đó chỉ có 5 shade → shade thiếu fallback `#000000` (đen).
  - **Border Section:** Thêm `border-solid/dashed/dotted`, `border-{width}`, `border-t/r/b/l-{width}`, `border-{color}-{shade}`, `border-white/black/transparent`.
  - **Filters & Effects:** `backdrop-blur-*`, `blur-*`, `brightness-*`, `contrast-*`, `opacity-*`, `isolate`.
  - **Typography Expansion:** `whitespace-*`, `text-align`, `text-decoration`, `text-transform`, `leading-*`, `tracking-*`, `line-clamp-*`.
  - **Transforms:** `translate-x/y-*` (numeric, fraction, arbitrary), `rotate-*`.
  - **Negative Prefix:** Xử lý universal `-` prefix cho negative values (`-mt-4`, `-translate-y-1/2`).
  - **Extras:** `size-*`, `flex-1/auto/none`, `self-*`, `order-*`, `cursor-*`, `pointer-events-*`, `select-*`, `group` marker.
  - **Arbitrary Values:** `[120px]`, `[50vh]`, `[calc(...)]` cho dimensions, spacing, position.
  - **TailwindPanel Categorization Fix:** Cập nhật tất cả regex phân loại (Typography, Layout, Spacing) + negative prefix support.
  - **CSS Reset Scoping (Critical):** Scope tất cả JIT resets sang `[class*='wp-block-ska-builder']` thay vì toàn body — fix blog/archive page bị vỡ layout.
  - **Ring:** `ring-*`, `ring-{color}-{shade}`, `ring-offset-*`, `ring-inset`.
  - **Backgrounds & Gradients:** `bg-cover/contain/auto`, `bg-center/top/bottom`, `bg-no-repeat`, `bg-fixed`, `bg-clip-*`, `bg-gradient-to-*`, `from-*/via-*/to-*`.
  - **Link Underline Fix:** Thêm scoped `a { text-decoration: none; color: inherit; }` cho Ska blocks.
- **2026-03-27 - Bridge/html2tailwind Convert Placement Bug:** Thay đổi core logic của bộ chuyển đổi từ `insertBlocks` sang `replaceBlocks(clientId, blocks)` nhằm đảm bảo khối kết quả nằm chính xác vị trí lồng nhau (Nested) của Container mẹ thay vì văng ra gốc.
- **2026-03-27 - Bridge/html2tailwind Insertion Fix:** Bổ sung `ska-builder/html2tailwind` vào danh sách `ALLOWED_BLOCKS` của `ska-container` và `ska-list-item` để cho phép thả công cụ convert vào các thẻ layout.
- **2026-03-27 - Ska Button Atomic Action & Icon Support:** 
  - Bổ sung nhóm thuộc tính Icon (`hasIcon`, `iconName`, `iconPosition`, `iconClasses`) và Action (`actionType`: link/submit/popup) cho khối Button. 
  - Sửa lỗi Navigation URL: Ép kiểu Semantic HTML theo Logic (`actionType === 'link' -> <a>`). Người dùng không còn lo thẻ bị kẹt ở `<button>` khiến link chết.
  - Sửa lỗi Thay Đổi Giao Diện Button Mặc Định: Gỡ bỏ class mượn danh `wp-element-button` đang mang CSS Theme bóp méo Tailwind. Triển khai file `index.css` chặn default underline của WP qua quy tắc specificity đặc biệt giúp Tailwind tùy ý ghi đè class `underline`.
  - Cải tiến `html2tailwind` parser để tự nhận diện thẻ nút bấm và trích xuất thành công Icon ra khỏi chữ, ĐỒNG THỜI giữ nguyên trọn vẹn mọi Custom Tailwind Class của thẻ <span> chứa Icon để lưu vào mục "Icon Custom Classes".
- **2026-03-27 - Icon Font Migration & Smart Text Detection:**
  - **Material Symbols Outlined (Critical):** Hoàn tất chuyển đổi từ thư viện `material-icons-outlined` cũ sang `material-symbols-outlined` cho toàn dự án. Cập nhật `TailwindPanel` (Icon `auto_awesome` active state), và `ska-image` (placeholder icons).
  - **Icon Library Array:** Tách dữ liệu ra `src/utils/material-icons.js` với 4207 icons. Triển khai Modal UI + Search logic trong `ska-icon/index.js` block.
  - **Smart Text Detection:** Parser `html-to-blocks.js` tự động thăng cấp `p`/`span` thành `ska-builder/container` nếu chúng chứa các block element (như Icon, Button, Image). Text nodes sẽ thành con và tự động kế thừa (inherit) CSS của cha.
  - **Flat DOM Preservation:** Các đoạn text thuần (TextNode) đặt cạnh icon sẽ giữ vai trò thẻ `span: ska-builder/text` mộc, không ôm dữ liệu class thừa để đảm bảo Clean Slate.
- **2026-03-27 - Editor Flex-Col Alignment Fix:**
- **2026-03-26 - Editor Layout Fidelity & Tailwind CDN Fix:**
  - **TailwindPanel Single Attribute Model (Critical):** Loại bỏ `splitTailwindClasses()` bên trong TailwindPanel. `tailwindClasses` là Single Source of Truth duy nhất, `className` luôn rỗng.
  - **useInnerBlocksProps (Critical):** Chuyển Container, List, List-Item từ `<InnerBlocks>` sang `useInnerBlocksProps` — loại bỏ intermediate wrapper divs (`block-editor-inner-blocks`) gây hỏng absolute positioning.
  - **Tailwind CDN Config Fix (Critical):** Phát hiện `important: true` không hoạt động do config bị CDN ghi đè khi khởi tạo. Fix: set config qua `script.onload` callback thay vì trước CDN load.
  - **JIT Compiler Position Offsets:** Thêm `inset-*`, `inset-x-*`, `inset-y-*`, `top-*`, `right-*`, `bottom-*`, `left-*` (numeric, full, auto, arbitrary) vào `class-tailwind-compiler.php`.
  - **Inline Positioning (Container block):** Detect positioning classes → convert thành inline styles trong `useBlockProps` để override Gutenberg CSS.
  - **Editor CSS:** Button border reset (`:not(.border)`), container `.border` fallback (`border-style: solid`), positioning overrides cho `.wp-block-ska-builder-container.absolute`.
- **2026-03-22 (Late Evening - Debugging Session):**
  - **Editor Positioning Context Fix (Critical):** Fixed absolute/fixed/sticky positioning in Gutenberg Editor (CSS injection to `ska-editor-helper.js`).
  - **Container Block Class Bug Fix (Critical):** Fixed Tailwind classes not applying - removed erroneous `setAttributes({ className: tailwindClasses })` from `src/ska-container/index.js` line 26.
  - **Comprehensive Debugging:** Explored 6 layers (TailwindPanel, JIT, Style Manager, Render, Config, System) - verified only Container block had issue.
- **2026-03-22:**
  - **Total Layout Fidelity Restoration & Flat DOM Optimization:**
    - Gỡ bỏ hoàn toàn cấu trúc lồng nhau (inner divs) như `ska-container-content` hay `ska-list-content`. Đảm bảo các block structural (Container, List, Button, Text) luôn là **DOM phẳng (Flat DOM)** để bảo toàn 100% quan hệ parent-child cho `flex` và `grid`.
    - Fix lỗi **"Missing Tailwind Classes" (The Non-Empty Fallback Bug)**: Thay thế lỗi logic `??` bằng kiểm tra `!empty()`. Đảm bảo nếu `tailwindClasses` mới là chuỗi rỗng (mặc định), hệ thống sẽ luôn tìm nạp class từ `className` cũ (legacy data).
    - Đồng bộ hóa 2 tầng Attribute: `tailwindClasses` (Source of Truth cho Ska) và `className` (Mirror cho Gutenberg Standard).
    - Hoàn tất rollout mô hình mới cho toàn bộ 8 block lõi, khắc phục các lỗi "Block encountered an error" do thiếu import hoặc sai cú pháp.
  - **Backward Compatibility Fix (Missing Classes in Editor):**
    - Khôi phục thuộc tính `className` trong `block.json` cho cả 8 block lõi để đảm bảo Gutenberg truyền dữ liệu cũ vào Editor.
    - Triển khai cơ chế **Auto-Migration** trong JS: Tự động chuyển data từ `className` sang `tailwindClasses` khi người dùng thao tác, giúp dọn dẹp dữ liệu cũ một cách an toàn.
    - Sửa lỗi mất icon ✨ (Tailwind Active) và các panel phân loại class khi nạp block cũ.
  - **Video block render fix (2026-03-22):** Khắc phục mismatch attribute `videoUrl` -> `url` trong `ska-video/render.php` và thêm parse logic cho `youtube/vimeo`.
  - **Session finalization:** Update system map + module docs.
- **2026-03-21:**
  - **Structural Style Isolation (The "Red Box" Fix):**
    - Thực hiện cuộc đại tu kiến trúc: Chuyển đổi tên thuộc tính từ `className` sang `tailwindClasses` cho tất cả 8 block lõi.
    - **Tại sao?** Để ngăn chặn Gutenberg tự động ghép class vào wrapper `div`. Giúp tách biệt hoàn toàn Logic CSS của Ska khỏi hạ tầng của WordPress.
    - **Inner Element Isolation (Frontend):** Cập nhật `render.php` cho `Ska Button` và `Ska Image` để di chuyển `get_block_wrapper_attributes()` sang thẻ con (`<a>`, `<img>`). Giúp xóa bỏ lỗi hover "tràn khung" (Red Box) và đảm bảo tính bao đóng cho block.
    - **Backward Compatibility:** Thêm cơ chế Fallback `$tailwindClasses ?? $className ?? ''` trong mã Render, đảm bảo các block cũ vẫn hiển thị đúng kiểu dáng.
  - **JIT & UI Enhancements:**
    - Cập nhật `TailwindPanel.js`: Thêm phân loại **"Transitions & Animation"**, hỗ trợ các class `transition-*`, `duration-*`, `ease-*`, `scale-*`.
    - **Atomic Reset:** Thêm mã CSS Reset cho thẻ `button` trong phạm vi `.ska-builder` (xóa border 1px mặc định, xóa nền xám) để đảm bảo nút luôn sạch sẽ trên mọi thiết kế.
  - **Sync & Deployment:** Thực hiện `npm run build` và `npm run sync` (Đồng bộ PHP sang `build/`) để dập tắt triệt để các "Ghost Bug" ở frontend.
- **2026-03-20:**
  - **Expert Debugger: Clean Specificity & Layout Fidelity Fix (Issue #4):**
    - Trả lại sự toàn vẹn cho kiến trúc **"Clean Slate"**: Gỡ bỏ hoàn toàn việc ép class `relative` vào block PHP & JS, đảm bảo block hoàn toàn sạch sẽ.
    - Sửa lỗi hạ tầng Editor cho phần tử `absolute` và áp dụng filter CSS tinh tế `:not([class*="max-w-"]):not(.container)` trong `ska-editor-helper.js` để tự động mở khóa giới hạn 600px của WP mà không đè bẹp các class định cỡ của User.
    - Cập nhật **Tailwind Compiler** để hỗ trợ class `.container` (width: 100%, max-width: 1280px, mx-auto).
  - **Expert Debugger: Hybrid Source & Inline Style Fix (Issue #3):**
    - Triển khai kiến trúc **Hybrid Source** cho Editor: PHP Core (Source of Truth) tự tạo CSS cho brand colors.
    - **Inline Style Support (Issue #3)**: Thêm thuộc tính `customStyle` để bảo toàn `background-image: url()` và các style đặc thù khác khi convert HTML sang block.
    - Cập nhật Bridge Parser và Block Index (JS) để hỗ trợ preview `customStyle` trong Editor thông qua `parseStyle` helper.
    - Bổ sung ô nhập liệu **Custom Inline Style** dạng `TextareaControl` vào component `TailwindPanel.js` cho TẤT CẢ 8 khối lõi. Giúp người dùng hiển thị "Ghost Data" thành UI trực quan và có thể chỉnh sửa tự do.
    - Mở rộng bảng màu tiêu chuẩn và hỗ trợ Opacity (`/50`).
    - Cải tiến `Style_Manager` để quét thêm `tailwindClasses` và nội dung `content`.
    - Sửa lỗi mapping Bridge và gỡ bỏ Dark Mode v1.
- **2026-03-19:**
  - **Grid & Specificity Fixes:**
    - Triển khai cơ chế **"Class Doubling"** (`.class.class`) kết hợp tiền tố `html body.ska-builder` trong `Tailwind_Compiler` để thắng CSS Theme (đặc biệt là `gap`) mà không dùng `!important`.
    - Fix lỗi **CSS Locale**: Sử dụng `number_format` với dấu chấm thập phân (`.`) cho toàn bộ giá trị spacing/dimensions, đảm bảo CSS không bị trình duyệt từ chối ở môi trường Việt Nam/Châu Âu.
  - **Block & Icon Improvements:**
    - Sửa lỗi **Icon Rendering**: Áp dụng `html_entity_decode` và `wp_kses` tùy chỉnh cho block `Ska Text` để hiển thị đúng icons Material Symbols/Icons.
    - **Global "Clean Slate"**: Loại bỏ triệt để class `w-full` mặc định khỏi `render.php` của Container, Video, List và xóa khỏi Whitelist của `Style_Manager`.
  - **Workflow & Build:**
    - Thiết lập quy trình **Build Sync**: Đồng bộ thủ công file PHP từ `src/` sang `build/` để đảm bảo WordPress luôn nạp code mới nhất (khắc phục lỗi nạp file cũ từ build gây sai lệch layout).
  - **Bridge Enhancements:**
    - Cải thiện `html-to-blocks.js` để bảo toàn thẻ tag gốc (`section`, `header`, `footer`...) cho `Ska Container`.
    - Thêm cơ chế trích xuất class từ thẻ `<body>` khi convert file HTML đầy đủ và bọc nội dung trong một root container.
    - Cập nhật `src/ska-container/index.js` hỗ trợ dynamic tag rendering trong editor.
- **2026-03-18:**

  - **Bridge Status:** 🟢 Done | Đã hội quân vào Core, có nút Toggle trong Dashboard.
  - **Bridge Integration:** Hoàn thành hội quân module **Bridge** vào **Ska Builder Core**.
  - Di chuyển `html-to-blocks.js` vào Core assets, sửa lỗi "Parser not loaded".
  - Thêm Toggle bật/tắt module Bridge trong **Admin Dashboard** (lưu vào `ska_bridge_enabled` option).
  - Fix lỗi mất CSS classes khi convert bằng cách đồng bộ attribute `className`.
  - **Custom Color Registry:** Thêm cơ chế Brand Colors vào JIT Compiler + Dashboard UI. Hỗ trợ `bg-{name}`, `text-{name}`, `border-{name}`, `ring-{name}` + opacity modifier (`bg-primary/10`).
  - **🔧 Planned Refactor:** Tách `class-tailwind-compiler.php` (>500 dòng) thành 3 file: `compiler`, `config`, `color-registry`. Thực hiện sau khi html2tailwind ổn định.
- **2026-03-13:**
  - Giải quyết triệt để lỗi layout của block **Ska Video**:
    - Nâng cấp `Style_Manager` để quét toàn bộ attributes của block (JIT Scanning full context).
    - Triển khai `MutationObserver` trong `ska-editor-helper.js` để đồng bộ style ngay khi block được render trong editor.
    - Sửa lỗi Grid trong Editor bằng `display: contents` cho wrapper của video block.
    - Tăng độ ưu tiên CSS (`!important`) và force `relative` layout cho video wrapper trên frontend.
    - **Atomic Defaults:** Loại bỏ toàn bộ class "ý kiến chủ quan" (`rounded-lg`, `shadow-md`...) trong `block.json` (cả src & build) để blocks bắt đầu ở trạng thái sạch.
    - **Isolation Fix:** Sử dụng `isolation: isolate` thay vì ép `overflow: hidden` mặc định để người dùng chủ động kiểm soát layout mà vẫn đảm bảo cắt góc chuẩn.
- **2026-03-15:**
  - Fix lỗi render của **Ska Video** block ở frontend (Regression sau cập nhật 2026-03-14) bằng `body.ska-builder` specificity.
  - **UI Refinement:** Thay thế "Dot Indicator" bị biến dạng bằng "Icon Indicator" ✨ (`auto_awesome`) từ Material Icons để đảm bảo thẩm mỹ và độ ổn định cao trên Sidebar WordPress. Đã loại bỏ hoàn toàn các chấm tròn debug thừa.
  - **Standardization:** Chốt cơ chế **Selector Specificity** cho toàn bộ Design Engine, loại bỏ 100% `!important` khỏi compiler.
- **2026-03-14:**
  - Hoàn thiện luồng `[/brainstorm]` - Thêm mới cụm block **Ska List** & **Ska List Item** làm tiền đề cho tính năng HTML2Tailwind.
  - Tổng vệ sinh **Atomic CSS Standardization**: Gỡ bỏ hoàn toàn Inline CSS rác (`padding-bottom hack`, `flow-root`, `display: grid !important`, `box-sizing: border-box !important`) ra khỏi file PHP Render của `Ska Video`, `Ska Container`, `Ska List`, `Ska List Item`.
  - Fix lỗi duplicate class Tailwind do xung đột giữa `get_block_wrapper_attributes()` và biến cục bộ.
  - Xử lý lỗi tràn ảnh (Image Overflow) trong Flex/Grid layout bằng các class responsive chuẩn (`max-w-full h-auto object-cover`).
  - **JIT Compiler Refactoring:** Gỡ bỏ **100% cờ `!important`** trong `class-tailwind-compiler.php` và cơ chế CDN Fallback (`class-core.php`). Áp dụng cơ chế **CSS Specificity Scope** (`body .ska-builder`) để tự nhiên ghi đè CSS của WP Themes mà vẫn giữ được sự tinh khiết của Tailwind classes, sẵn sàng cho hạ tầng Headless.

- **2026-03-12:** 
- **2026-03-10:** Hoàn thiện block **Ska Image** (sửa lỗi chọn ảnh & layout frontend) và block **Ska Icon** (Material Icons selection, visual rendering trong editor). Thực hiện cải tiến UI/UX: Thay thế status bar/nhãn text bằng "Indicator dạng chấm tròn" (dot indicator) trên toàn hệ thống để tăng tính thẩm mỹ (Lưu ý: Sidebar indicator đang được xử lý triệt để lỗi cache).
- **2026-03-09:** Nâng cấp **Tailwind Panel** (Reset button, Tag UI). Chuyển đổi **Ska Text** sang JSX & chuẩn hóa quy trình build (`src/` vs `build/`). Đặc biệt: Sửa lỗi **Grid Layout trong Editor** qua `ska-editor-helper.js` (Iframe injection, `display: contents` fix, `important` modes & cache bypass).
- **2026-03-07:** Thêm module **Admin Dashboard** (`inc/admin-dashboard`). Tạo block **Ska Container** (hỗ trợ `InnerBlocks`). Nâng cấp **Design Engine** (hỗ trợ `@media` queries responsive prefixes `sm:`, `md:`, css grid `grid-cols-*`, auto margin `mx-auto`, max width và áp dụng cờ `!important` chống đè class WP default).
- **2026-02-10:** Review & Documentation update. Finalized Phase 1 (MVP) documentation. Verified Design Engine (Local JIT Compiler with Colors, Spacing, Dimensions support) and Logic Engine (If/Foreach support).
- **2026-02-09:** Implemented Logic Engine (If/Foreach) and Local JIT Compiler (Regex-based). Fixed Editor real-time styling issues in Iframe.
- **2026-02-05:** Initial Project Setup & System Mapping.

## 7. FUTURE ROADMAP (PLANNED) - PHASE 2: APP BUILDER ARCHITECTURE
- **Ska Theme (Barebone):** Tạo WordPress theme trống, loại bỏ 100% CSS mặc định của WP, nhường toàn quyền layout cho khối Ska.
- **Ska App Portals (CPT Dashboards):** Tích hợp CPT `ska_portal` vào `ska-builder-core` chuyên dùng để dựng các màn hình Frontend Admin (Dành cho Học viên, Giảng viên, Manager) sử dụng các Builder Blocks tiêu chuẩn và cô lập hoàn toàn khỏi hệ thống phân tích SEO (No-index by default) + Cơ chế chặn URL dựa trên Role (Logic Engine).
- **Micro-Ecosystem Decoupling:** Tách `ska-no-code-design` (trước là `ska-builder-core`) mảng Data và Logic ra thành các plugins độc lập (`ska-data-pro`, `ska-logic-engine`). Quản lý toàn bộ thông qua Master Plugin `ska-no-code-home`.
- **Ska Data Pro (Flat Tables):** Khởi tạo hệ thống Database tự trị với bảng phẳng `ska_data_*`. Thoát ly hoàn toàn khỏi bảng `wp_postmeta`. Xây dựng Schema Manager và Query Builder trực quan.
- **Ska Attributes (Key-Value Dynamic Panel):** Trong plugin Design Engine, thay thế `customStyle` bằng mảng `htmlAttributes` để rải thuộc tính HTML (`data-*`, `aria-*`, `style`...) vào thẻ wrapper Frontend.
- **Tailwind CSS v4:** Nâng cấp cỗ máy Local JIT từ v3 lên cấu trúc của v4 để hỗ trợ mở rộng động.
- **🔴 REST JIT Compiler (Editor Refactor):** Đại tu lõi `ska-no-code-design` sau khi MVP hoàn tất. Bãi bỏ Tailwind CDN khỏi môi trường Gutenberg Editor. Áp dụng cơ chế **AJAX Polling / wp.data.subscribe** để gọi thẳng sức mạnh biên dịch của hàm PHP `Tailwind_Compiler` từ local server nhằm kết nối 100% Logic biên dịch thành một cục (Single Source of Truth), gia tăng hiệu năng và chấm dứt các lỗi sai biệt render class của CSS Editor.