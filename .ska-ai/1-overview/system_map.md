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
    ├── ska-builder-core/    # [FOUNDATION] Base Atomic Blocks & Dashboard
    ├── ska-no-code-design/  # [UI/UX] Tailwind v4 JIT & Inspector Panel
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
| **Ska Builder Core** | `plugins/ska-builder-core/`| Base Blocks, Admin Menu | 🟢 Done (v1) |
| **Ska Design Engine** | `plugins/ska-no-code-design/`| Tailwind JIT, Inspector UI | 🔴 Setup Pending |
| **Ska Data Pro** | `plugins/ska-data-pro/` | Flat Tables DB, Schema | 🔴 Arch Phase |
| **Ska Logic Engine** | `plugins/ska-logic-engine/`| If/Foreach Tags, Workflows | 🔴 Plan Phase |
| **Ska Bridge** | `plugins/ska-bridge/` | html2tailwind, API | 🟢 Done (v1) |

## 5. GLOBAL CONSTRAINTS (FOR AI)
1. **Micro-Ecosystem Boundary:** Plugins DO NOT call each other's classes directly. They communicate exclusively via WP `do_action` and `apply_filters`.
2. **Flat Tables First:** All future data models must rely on Ska Data Pro's custom tables (`ska_data_*`), completely abandoning `wp_postmeta`.
3. **Divide & Conquer:** File size < 500 lines. Exceed = Split.
4. **Context Switching:** Always read the specific plugin's documentation inside `.ska-ai/3-ecosystem/` before executing tasks.
5. **Build Sync Confirmation:** AI BẮT BUỘC phải hỏi ý kiến người dùng trước khi thực hiện `npm run sync`.

## 6. RECENT UPDATES
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
- **Micro-Ecosystem Decoupling:** Tách `ska-builder-core` hiện tại thành 3 plugins độc lập (No-code Design, Data Pro, Logic Engine).
- **Ska Data Pro (Flat Tables):** Khởi tạo hệ thống Database tự trị với bảng phẳng `ska_data_*`. Thoát ly hoàn toàn khỏi bảng `wp_postmeta`. Xây dựng Schema Manager và Query Builder trực quan.
- **Ska Attributes (Key-Value Dynamic Panel):** Trong plugin Design Engine, thay thế `customStyle` bằng mảng `htmlAttributes` để rải thuộc tính HTML (`data-*`, `aria-*`, `style`...) vào thẻ wrapper Frontend.
- **Tailwind CSS v4:** Nâng cấp cỗ máy Local JIT từ v3 lên cấu trúc của v4 để hỗ trợ mở rộng động.