# SYSTEM MAP: SKA NO-CODE (v2.0.0)
@status: MILESTONE 1 (POST-MVP) | @git_branch: main | @last_update: 2026-06-17

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
| **Ska No-Code Design** | `plugins/ska-no-code-design/` | Custom Blocks, Tailwind JIT, Skapine, Molecules. | 🟢 Stable (v1.1.0) |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Quản lý bảng phẳng MySQL, Schema, Smart Objects. | 🟢 Stable (v1.1.1) |
| **Ska Logic Engine** | `plugins/ska-logic-engine/` | DAG Workflows, Event Pipeline, SkaFX Compiler. | 🟢 Stable (v1.2.6) |
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
- **Global Edit (Shadow Scratchpad):** Chỉnh sửa Organisms gốc thông qua Iframe biệt lập sử dụng Shadow CPT (`ska_organism_draft`), an sau MySQL.
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
- **2026-06-17 - 🟢 Done:** Đồng bộ hóa SSH Key kết nối với GitHub, merge thành công nhánh tính năng vào `main` và tự động đóng gói, phát hành phiên bản hệ sinh thái `v1.2.0` (bao gồm các cập nhật chuẩn hóa bảng phẳng Workspace và cơ chế Redirect Fallback).
- **2026-06-15 - 🟢 Done:** Vá lỗi tự động đóng Sidebar Popup Menu (Table & Workspace dropdowns) khi click ra ngoài và đổi nhãn từ "Rename Space" thành "Workspace Settings" trong [manage-sidebar.php](file:///home/chiconcota/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-data-pro/inc/admin/views/parts/manage-sidebar.php). Khắc phục triệt để lỗi `ReferenceError: wp is not defined` bằng cách chuyển sang enqueue script `admin-datagrid.bundle.js` qua hệ thống `wp_enqueue_script` chuẩn của WordPress với các dependency `['wp-i18n', 'wp-util']` trong [class-admin-menu.php](file:///home/chiconcota/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-data-pro/inc/admin/class-admin-menu.php). Nâng cấp phiên bản plugin `Ska Data Pro` lên `v1.1.1`.
- **2026-06-15 - 🟢 Done:** Chuẩn hóa lưu trữ Workspace từ option `wp_options` sang bảng phẳng hệ thống `wp_ska_data_sys_apps` trực thuộc Ska Data Pro. Thiết lập cơ chế Redirect Fallback 2 cấp (Table -> Workspace) và tích hợp tùy biến trang lỗi 403 (location/condition 403) qua Ska Builder với fallback 403 mặc định tuyệt đẹp. Nâng cấp phiên bản cả hai plugin Ska Data Pro và Ska No-Code Design lên `v1.1.0`.
- **2026-06-07 - 🟢 Done:** Khởi tạo cơ chế Continuous Learning Loop của Agent, thiết lập tệp tự sửa đổi hành vi `self-improve.md` và cập nhật các workflow `start_session` và `end_session` có bước đánh giá tương tác (Interactive Session Review).
- **2026-06-07 - 🟢 Done:** Hoàn thành kiểm thử thủ công E2E cho tính năng gợi ý tự động (SkaFX Autocomplete & Data Picker), chuyển toàn bộ 6 test cases sang dạng check list để dễ kiểm soát. Thống nhất phương án kiến trúc phát triển Community Nodes (Pluggable Nodes) cho các logic tùy biến phức tạp ở Milestone 2+ nhằm bảo vệ tuyệt đối tính an toàn của lõi và triết lý No-code.
- **2026-06-07 - 🟢 Done:** Nghiên cứu và sửa lỗi ký tự gạch dưới `_` bị tàng hình/biến thành khoảng trắng trong Gutenberg Sidebar (Settings Panel). Đã giải quyết triệt để bằng cách chèn class `ska-builder` vào body của WP Admin thông qua hook `admin_body_class` trong `class-core.php`, giúp CSS override font-family hệ thống kích hoạt thành công trên các inputs và textareas ở sidebar. Nâng version `ska-no-code-design` lên `v1.0.9`.
- **2026-06-07 - 🟢 Done:** Đồng bộ hóa chính tả Workflow ID giữa Database và Frontend (`test-skafx-autocomplate-node` -> `test-skafx-autocomplete-node`), giải quyết triệt để lỗi không tìm thấy workflow khi submit form và khôi phục hoạt động của ConditionNode If/Else E2E test. Nâng version `ska-logic-engine` lên `v1.2.6`.
- **2026-06-07 - 🟢 Done:** Dọn dẹp cấu trúc thư mục agent trong workspace. Di chuyển `debug-workflow.md` từ thư mục `.agents` (số nhiều) sang `.agent` (số ít), loại bỏ thư mục rác `.agents` bị bỏ qua bởi Git, đồng bộ hóa các slash commands và giữ sạch cấu trúc thư mục của dự án.
- **2026-06-07 - 🟢 Done:** Khắc phục hoàn toàn lỗi khuất hiển thị (viewport clipping) của dropdown gợi ý bằng cơ chế tự động căn chỉnh vị trí lên trên (above) hoặc xuống dưới (below) ô nhập liệu tùy thuộc vào không gian trống còn lại của màn hình. Bổ sung hỗ trợ gợi ý cả biến `[payload.render_template]` và `[payload.rendered_template]` cho RenderTemplateNode. Nâng phiên bản lên `v1.2.5`.
- **2026-06-07 - 🟢 Done:** Vá lỗi autocompletion biến đầu ra của `RenderTemplateNode` (`[payload.rendered_template]`) và `DBQueryNode` (`[payload.query_results]`) khi các biến này chưa được người dùng cấu hình thủ công. Tối ưu hoá cỗ máy `SkaFX_Evaluator` để tự động fallback và phân giải biến nằm sâu trong mảng mock `payload` do UI Sandbox truyền lên backend. Nâng version lên `v1.2.4`.
- **2026-06-06 - 🟢 Done:** Triển khai tính năng gợi ý biến động (SkaFX Autocomplete & Data Picker) tích hợp trong SettingsPanel.jsx. Hỗ trợ tự động hoàn thành biến payload, database fields, built-in functions và loop context ([$item], [$index]) khi người dùng gõ `[`, `{` hoặc chữ cái đầu. Nâng version lên `v1.2.0`.
- **2026-06-05 - 🟢 Done:** Triển khai quy trình đóng gói tự động thành file ZIP phân phối duy nhất cho người dùng cuối (release.js), lập tài liệu quy trình release-workflow.md và đẩy tag Git v1.1.11 thành công.
- **2026-06-03 - 🟢 Done:** Tích hợp tính năng Switch View (Graph / JSON) cho Ska Logic Engine (v1.1.11). Cho phép xem cấu trúc đồ thị dạng JSON Blueprint thời gian thực, sao chép nhanh (Copy JSON) và dán đè/chỉnh sửa trực tiếp (Apply & Return) với bộ kiểm lỗi cú pháp (Syntax validation) an toàn chống crash. Nâng version lên `v1.1.11`.
- **2026-06-03 - 🟢 Done:** Vá lỗi JIT Tailwind cho nội dung động bằng cách đăng ký hook `ska_design_classes_to_scan` trong Ska Logic Engine và phát triển tính năng quét CSDL bảng phẳng tự động (`scan_database_flat_tables_classes`). JIT compiler giờ tự động trích xuất mọi class Tailwind động trong các bảng phẳng MySQL của app và cache transient 12 giờ (tự động invalidate cache khi có thay đổi DB action), giải quyết triệt để lỗi vỡ giao diện 3 cột của Client Response Modal ngoài frontend. Nâng version Ska Logic Engine lên `v1.1.10`.
- **2026-06-03 - 🟢 Done:** Nâng cấp Shadow Scratchpad Modal (v1.0.6) cho Ska No-Code Design. Loại bỏ padding của overlay, gỡ bỏ các thuộc tính giới hạn chiều rộng/chiều cao, thiết lập modal container chiếm trọn vẹn 100% chiều rộng và chiều cao màn hình (full-screen) cùng việc loại bỏ bo góc để mang lại trải nghiệm review thiết kế trung thực và tối ưu nhất.
- **2026-06-03 - 🟢 Done:** Triển khai Dynamic Modal cho Client Response Node (v1.1.7). Hỗ trợ truyền trực tiếp nội dung HTML động đã render (`modal_content`) từ backend và tự động khởi tạo Dynamic Modal ở frontend với overlay mờ (`backdrop-filter`), nút đóng nổi và hỗ trợ phím ESC / click-outside, loại bỏ hoàn toàn việc bắt buộc khai báo Modal ID tĩnh. Nới rộng Sidebar cấu hình ClientResponseNode lên `w-[400px]`.
- **2026-06-03 - 🟢 Done:** Triển khai và tích hợp Pure Render Template Node (v1.1.6) cho Ska Logic Engine. Loại bỏ query DB trực tiếp tới bảng organisms, thực thi Two-Pass Interpolation và tích hợp hàm `do_blocks()` trên PHP backend để tự động dịch Gutenberg Block Markup sang HTML sạch. Nâng cấp React `SettingsPanel.jsx` với giao diện cấu hình template_html, tự động nới rộng Sidebar lên w-[400px] cho Render/Api Node, tích hợp sandbox Live Testing & Preview (Visual/Raw HTML) chạy bộ nội suy 2 bước client-side.
- **2026-06-01 - 🟡 Planning:** Phác thảo thiết kế Pure Render Template Node (hoạt động độc lập không phụ thuộc CSDL) với cơ chế nội suy 2 bước (Two-Pass Interpolation). Đồng thời hoàn thành brainstorming cho Client Response Node và thống nhất kế hoạch xây dựng hệ thống Node Cộng đồng (Community Nodes) trong Milestone 2+. Khởi tạo tài liệu tiến độ pm_render_template.md và file nháp giao diện settings_panel_mockup.html.
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