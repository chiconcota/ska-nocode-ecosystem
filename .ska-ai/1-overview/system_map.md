# SYSTEM MAP: SKA NO-CODE (v2.0.0)
@status: MILESTONE 1 (POST-MVP) | @git_branch: feature/refactor-logic-db | @last_update: 2026-05-26

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
| **Ska No-Code Design** | `plugins/ska-no-code-design/` | Custom Blocks, Tailwind JIT, Skapine, Molecules. | 🟢 Stable (v1.5.0) |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Quản lý bảng phẳng MySQL, Schema, Smart Objects. | 🟢 Stable (v1.2.0) |
| **Ska Logic Engine** | `plugins/ska-logic-engine/` | DAG Workflows, Event Pipeline, SkaFX Compiler. | 🟡 Refactoring (v1.1.0) |
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
- **2026-05-26 - 🟢 Done:** Khảo sát kiến trúc Blueprint & Phát hiện nợ kỹ thuật Logic Engine (lưu Workflows bằng `wp_options`). Thiết lập backlog Milestone 1 đưa việc Refactor MySQL và AI Import làm trọng tâm.
- **2026-05-24 - 🟢 Done:** Tách biệt hoàn toàn mã nguồn hệ sinh thái Ska khỏi nhân WordPress Core, tối ưu hóa tệp `.gitignore` và làm sạch lịch sử Git (xóa file zip, debug logs cũ).
- **2026-05-23 - 🟢 Done:** Sắp xếp lại Admin Menu (Theme Options -> Organisms -> Theme Builder). Ẩn toàn bộ menu phụ của Logic Engine để dọn dẹp Sidebar.
- **2026-05-23 - 🟢 Done:** Hoàn tất quốc tế hóa (i18n) 100% chuỗi tiếng Việt thô trong code PHP/JS sang tiếng Anh bọc hàm dịch chuẩn của WP.
- **2026-05-22 - 🟢 Done:** Refactor luồng Xóa dòng dữ liệu trên Portal List View qua Logic Workflow (`delete_{table}`). Khắc phục lỗi DOM bubble up khi xóa card lồng nhau.
- **2026-05-22 - 🟢 Done:** Khắc phục lỗi lưu dữ liệu Scratchpad, bỏ qua `sanitize_text_field()` cho các cột `long_text` JSON để bảo vệ comment Gutenberg.

---

## 7. FUTURE ROADMAP (MILESTONE 1)
- **Ska Logic Engine MySQL Refactor:** Di chuyển cấu trúc lưu trữ workflows từ `wp_options` sang flat table MySQL `ska_logic_workflows`.
- **AI JSON Blueprint Import:** Cung cấp endpoint REST API và nút UI để import trực tiếp cấu hình đồ thị JSON do AI tự động sinh.
- **Giao diện cấu hình SkaFX & Async:** Bổ sung UI Autocomplete/Data Picker cho biểu thức SkaFX và UI Edge Customization để bật cờ `async` trực quan trên Canvas.
- **Organisms Categorization:** Phân loại và chia nhóm thư mục/tag cho Ska Organisms (Symbols) để dọn dẹp giao diện quản lý và Inserter.