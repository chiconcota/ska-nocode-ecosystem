# SYSTEM MAP: SKA NO-CODE (v2.0.0)
@status: MILESTONE 1 (POST-MVP) | @git_branch: feature/organisms-categorization | @last_update: 2026-06-01

## 1. TECH STACK (APP BUILDER ARCHITECTURE)
- **Backend:** WP Core 6.x + PHP 8.2+ (Host & API)
- **Data (Ska Data Pro):** Flat Tables MySQL (`ska_data_*`), Schema Manager, Custom Query Builder.
- **Design (Ska Design Engine):** Tailwind CSS v4, Local JIT Compiler, React + Gutenberg API, Alpine.js (Ska Molecule).
- **Logic (Ska Logic Engine):** DAG Canvas Graph (React Flow v11), Expression Evaluator (SkaFX AST), Background Worker (Action Scheduler).
- **Bridge:** html2tailwind Converter, REST API Endpoint (Headless ready).

---

## 2. PROJECT STRUCTURE (MICRO-ECOSYSTEM)
```text
wp-content/
├── themes/
│   └── ska-canvas/           # [THEME] Blank Canvas (Zero CSS/JS overhead)
└── plugins/
    ├── ska-no-code-design/  # [UI/UX] Atomic Blocks, Tailwind JIT, Inspector, Skapine, Molecules
    ├── ska-data-pro/        # [DATA] Flat Tables DB Engine, Smart Object JSON Blueprint
    ├── ska-logic-engine/    # [LOGIC] DAG Workflow Builder, SkaFX AST, Async Worker
    └── ska-bridge/          # [ADAPTER] html2tailwind, API Headless Export
```
*Giao tiếp chéo:* Độc lập tuyệt đối (Decoupled). Không gọi class chéo, chỉ truyền nhận qua WP Action/Filter hooks và Alpine.js global store (`Alpine.store`).

---

## 3. MODULE REGISTRY & STATUS
| Module Name | Path | Core Function | Status |
| :--- | :--- | :--- | :--- |
| **Ska Canvas (Theme)** | `themes/ska-canvas/` | Loại bỏ CSS/JS rác của WP, tạo khung canvas sạch. | 🟢 Stable (v1.0.0) |
| **Ska No-Code Design** | `plugins/ska-no-code-design/` | Custom Blocks, Tailwind JIT, Skapine, Molecules. | 🟢 Stable (v1.0.4) |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Quản lý bảng phẳng MySQL, Schema, Smart Objects. | 🟢 Stable (v1.0.5) |
| **Ska Logic Engine** | `plugins/ska-logic-engine/` | DAG Workflows, Event Pipeline, SkaFX Compiler. | 🟢 Stable (v1.1.5) |
| **Ska Bridge** | `plugins/ska-bridge/` | html2tailwind, API endpoints. | 🟢 Stable (v1.0.0) |

---

## 4. 🟢 CHECKPOINT: PHASES 3 & 4 ACCOMPLISHMENTS
Dưới đây là danh sách các tính năng và kiến trúc cốt lõi đã hoàn thành trong Phase 3 & 4 vừa qua:

### 4.1. Core Engine & Data (Phase 3)
- **Flat Tables First:** Triệt tiêu hoàn toàn `wp_postmeta` (EAV), lưu dữ liệu Smart Object qua các bảng phẳng `ska_data_*`.
- **Ska Loop Block:** Block lặp dữ liệu động cấu hình qua UI Inspector, cơ chế hydration bằng Mustache `{{tag}}` và Zero N+1 Queries.
- **System Multi-Tier Caching:** Caching đa tầng (`System_Cache`) cho Smart Object `app-site` giúp chống quá tải hoặc sập DB.

### 4.2. UI/UX, Symbols & Molecules (Phase 4.1 - 4.4)
- **Ska Symbols (Organisms):** Save as Organism, lưu code HTML dưới dạng JSON Reference, tối ưu hóa zero-query trong Editor nhờ localize JSON Cache.
- **Global Edit (Shadow Scratchpad):** Chỉnh sửa Organisms gốc thông qua Iframe biệt lập sử dụng Shadow CPT (`ska_organism_draft`), an toàn MySQL.
- **Thư viện Ska Molecules:** Tích hợp 10 Block Variations nguyên tử (Tabs, Accordion, Carousel/Slider, Dropdown, Offcanvas mobile menu, Toggle, Tooltip, Radio/Checkbox Groups, Form) bọc Alpine.js và Tailwind CSS.
- **Skapine Engine:** Higher-Order Component (`withSkapineEngine`) mô phỏng live-preview 100% các event Alpine (`@click`, hover, `x-init`, và transition `x-transition`) ngay trong Editor.

### 4.3. Auto-CRUD App Generator (Phase 4.5 - 4.6)
- **One-Click App Generator:** Tự động sinh giao diện List View (dạng Card dynamic grid) và Detail View dựa trên Schema của Data Pro.
- **Dedicated Page Routing:** Bỏ kiến trúc SPA cồng kềnh, chuyển sang định tuyến Dedicated Page (định tuyến theo URL tham số).
- **Notion-Style Form Editor:** Form Thêm mới / Cập nhật riêng biệt dạng Clean Notion-style, tự động giữ nguyên ID (không reset form) khi Lưu thay đổi.
- **TinyMCE Scratchpad Integration:** Tích hợp TinyMCE/Visual editor thô ở frontend cho trường `long_text` (Scratchpad), cơ chế triệt tiêu cảnh báo "Rời trang web" và đồng bộ Visual/HTML.
- **Garbage Collection (GC):** Tự động truy quét và dọn sạch các Template/Organisms mồ côi khi bảng dữ liệu bị xóa khỏi hệ thống.

---

## 5. GLOBAL CONSTRAINTS (FOR AI)
1. **Decoupled Architecture:** Plugins KHÔNG được gọi trực tiếp class của nhau. Mọi giao tiếp bắt buộc qua WP Hooks (`do_action`, `apply_filters`).
2. **Flat Tables First:** Mọi cấu trúc dữ liệu mới phải sử dụng bảng phẳng MySQL (`ska_data_*`), không lạm dụng `wp_options` hay `wp_postmeta`.
3. **SemVer Rule:** Tự động tăng số phiên bản (PATCH/MINOR/MAJOR) trong file header khi chỉnh sửa code nguồn của Plugin/Theme.
4. **Zero-Trash Policy:** Nghiêm cấm tạo file `.md` tự do ngoài 4 thư mục chính. Mọi tài liệu cập nhật phải ghi đè trực tiếp (replace) lên file cũ.

---

## 6. RECENT LOGS (LATEST SHIELD)
- **2026-06-01 - 🟢 Done:** Tích hợp AI JSON Blueprint Import/Export cho Ska Logic Engine (v1.1.5). Cho phép tải về và upload file `.json` để nạp đồ thị cấu hình phức tạp thẳng vào cơ sở dữ liệu. Bổ sung `class-blueprint-api.php` với 2 REST endpoints. Khắc phục lỗi `rest_forbidden` (401) khi click Export trên trình duyệt bằng cách đính kèm REST Nonce (`wp_rest`) trực tiếp vào URL. Vá lỗi giao diện hẹp gây co nút Operations bằng cách nới rộng khung wrapper lên 1200px, chỉnh tỷ lệ cột, và sử dụng flexbox chống xuống dòng.
- **2026-06-01 - 🟢 Done:** Triển khai tính năng Phân loại Ska Organisms (Ska Organisms Categorization & Folder Management). Hỗ trợ lưu category trực tiếp vào bảng phẳng CSDL, Sidebar quản lý CRUD danh mục thời gian thực trên Workspace Panel và optgroup gom nhóm dropdown chọn symbol trong Gutenberg Editor. Cập nhật E2E Test Workflow. Nâng cấp Ska No-Code Design (v1.0.4) và Ska Data Pro (v1.0.5).
- **2026-05-30 - 🟢 Review:** Rà soát toàn bộ tiến độ E2E Test Workflow. Kết quả: 10/17 test cases đã hoàn thành (Logic Engine 4/4, System Table Protection 2/2, Dark Mode 2/3). 7 test cases còn lại (Link Engine 3, Theme Builder 3, Dark Mode TC3) bàn giao cho User tự kiểm thử thủ công.
- **2026-05-30 - 🟢 Done:** Vá lỗi block select kết nối động trên frontend. Sửa đổi render.php của block select để trích xuất fieldName từ dynamic binding khi fieldName bị rỗng hoặc trùng giá trị mặc định 'my_select'; sửa đổi class-dynamic-content.php hỗ trợ tự động trích xuất cấu trúc thẻ label để sinh checkbox/radio list động thay vì chèn thẻ option sai cấu trúc HTML; đồng thời gỡ bỏ `$nextTick` trong form init để triệt tiêu race condition khởi tạo Alpine, và sửa 19 lỗi cú pháp JSX giúp webpack biên dịch thành công. Nâng cấp Ska No-Code Design (v1.0.3) và Ska Logic Engine (v1.1.2).
- **2026-05-30 - 🟢 Done:** Vá lỗi lưu CSDL JSON khi sửa ô trực tiếp trên lưới (cell inline edit) của Ska Data Pro (v1.0.4) bằng cách chuẩn hóa giá trị về NULL/JSON Array trước khi cập nhật.
- **2026-05-30 - 🟢 Done:** Khắc phục lỗi lưu CSDL JSON khi lưu mảng trống hoặc CSV thô bằng cách đưa vào cơ chế chuẩn hóa dữ liệu CSDL tự động (NULL Cast & JSON Array Encoding) tại Ska Data Pro (v1.0.3) và Ska Logic Engine (v1.1.1), cập nhật hàm giải mã dữ liệu của Data Fetcher.
- **2026-05-30 - 🟢 Done:** Vá lỗi block validation khi sinh Portal App (Ska No-Code Design v1.0.1), sửa comment block từ `//->`/`//>` thành `/-->` và chạy script sửa đổi dữ liệu SQL.
- **2026-05-30 - 🟢 Done:** Dịch toàn bộ các chuỗi giao diện tiếng Việt thô trong plugin Ska Data Pro sang tiếng Anh và bọc hàm i18n của WordPress (v1.0.2), cập nhật logic xác nhận từ "XACNHAN" thành "CONFIRM".
- **2026-05-28 - 🟢 Done:** Triển khai cơ chế bảo vệ cấu trúc bảng hệ thống (System Table Schema Protection - Approach A) cho plugin Ska Data Pro (v1.0.1), cấm mọi chỉnh sửa cấu trúc (thêm/sửa/xóa cột, đổi tên, xóa bảng) của các bảng hệ thống phẳng và ẩn toàn bộ UI cấu hình liên quan.
- **2026-05-28 - 🟢 Done:** Hoàn thành refactor lưu trữ Ska Logic Engine sang bảng phẳng MySQL `wp_ska_data_sys_workflows`, liên kết vào Workspace Site Management (`ska_system`) và cấu hình bảo vệ cấm xóa, Hybrid Routing hỗ trợ Dev Mode.
- **2026-05-26 - 🟢 Done:** Khảo sát kiến trúc Blueprint & Phát hiện nợ kỹ thuật Logic Engine (lưu Workflows bằng `wp_options`). Thiết lập backlog Milestone 1 đưa việc Refactor MySQL và AI Import làm trọng tâm.
- **2026-05-24 - 🟢 Done:** Tách biệt hoàn toàn mã nguồn hệ sinh thái Ska khỏi nhân WordPress Core, tối ưu hóa tệp `.gitignore` và làm sạch lịch sử Git (xóa file zip, debug logs cũ).
- **2026-05-23 - 🟢 Done:** Sắp xếp lại Admin Menu (Theme Options -> Organisms -> Theme Builder). Ẩn toàn bộ menu phụ của Logic Engine để dọn dẹp Sidebar.
- **2026-05-23 - 🟢 Done:** Hoàn tất quốc tế hóa (i18n) 100% chuỗi tiếng Việt thô trong code PHP/JS sang tiếng Anh bọc hàm dịch chuẩn của WP.
- **2026-05-22 - 🟢 Done:** Refactor luồng Xóa dòng dữ liệu trên Portal List View qua Logic Workflow (`delete_{table}`). Khắc phục lỗi DOM bubble up khi xóa card lồng nhau.
- **2026-05-22 - 🟢 Done:** Khắc phục lỗi lưu dữ liệu Scratchpad, bỏ qua `sanitize_text_field()` cho các cột `long_text` JSON để bảo vệ comment Gutenberg.

---

## 7. FUTURE ROADMAP (MILESTONE 1)
- [x] **AI JSON Blueprint Import:** Cung cấp endpoint REST API và nút UI để import trực tiếp cấu hình đồ thị JSON do AI tự động sinh.
- **Giao diện cấu hình SkaFX & Async:** Bổ sung UI Autocomplete/Data Picker cho biểu thức SkaFX và UI Edge Customization để bật cờ `async` trực quan trên Canvas.
- [x] **Organisms Categorization:** Phân loại và chia nhóm thư mục/tag cho Ska Organisms (Symbols) để dọn dẹp giao diện quản lý và Inserter.