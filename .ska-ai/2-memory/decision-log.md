## 2026-03-28 - JIT Compiler: Ring & Backgrounds (Bonus)
- **Decision:** Thêm `ring-*` (width, color, inset), `ring-offset-*` (width, color) sử dụng `box-shadow` approach chuẩn Tailwind.
- **Decision:** Thêm Background utilities đầy đủ: `bg-cover/contain/auto`, `bg-center/top/bottom/left/right`, `bg-no-repeat`, `bg-fixed/local/scroll`, `bg-clip-*`, `bg-origin-*`.
- **Decision:** Thêm Gradient support: `bg-gradient-to-{dir}`, `from-{color}-{shade}`, `via-{color}-{shade}`, `to-{color}-{shade}` + opacity + basic colors.
- **Result:** Roadmap Design Engine: **100% ALL CHECKED** — không còn item `[ ]` nào.

## 2026-03-28 - [ROADMAP] Container Background Media
- **Decision:** Thêm vào roadmap: Container block cần `MediaUpload` UI cho background image/video.
- **Scope:** `backgroundImage`, `backgroundType` (image/video/gradient), overlay support, video autoplay.
- **When:** Phiên tiếp theo.

## 2026-03-28 - JIT Compiler Comprehensive Audit & Enhancement
- **Decision:** Bổ sung đầy đủ 11 shade (50-950) cho 17 màu chromatic trong `get_color_hex()`. Trước đó chỉ có 5 shade (50, 100, 500, 600, 900) → shade thiếu fallback ra `#000` (đen).
- **Decision:** Thêm border section đầy đủ: `border-solid/dashed/dotted/double/hidden/none`, `border-{width}`, `border-t/r/b/l-{width}`, `border-{color}-{shade}`, `border-white/black/transparent`.
- **Decision:** Thêm Filters & Effects: `backdrop-blur-*`, `blur-*`, `brightness-*`, `contrast-*`, `opacity-*`, `isolate`.
- **Decision:** Thêm Typography đầy đủ: `whitespace-*`, `text-center/left/right`, `underline/no-underline`, `uppercase/lowercase/capitalize`, `italic`, `truncate`, `leading-*`, `tracking-*`, `line-clamp-*`.
- **Decision:** Thêm arbitrary bracket values `[120px]`, `[50vh]`, `[calc(...)]` cho dimensions, spacing, và position offsets.
- **Decision:** Thêm `size-*` utility (Tailwind v3.4), `flex-1/auto/none`, `self-*`, `order-*`, `cursor-*`, `pointer-events-*`, `select-*`.
- **Decision:** Thêm `translate-x/y-*` (numeric, fraction, arbitrary), `rotate-*` vào Transforms section.
- **Decision:** Xử lý negative prefix `-` universal: `-mt-4`, `-translate-y-1/2` — strip dấu `-` đầu rồi negate CSS values.
- **Decision:** Thêm `group` marker class (no CSS output, truthy return) để tránh trigger CDN fallback.
- **Decision:** Thêm `bg-black/30`, `bg-white/80`, `text-white/50` — basic color + opacity support.

## 2026-03-28 - TailwindPanel Category Regex Updates
- **Decision:** Mở rộng Typography regex: thêm `whitespace`, `truncate`, `line-clamp`, `no-underline`, `normal-case`, `not-italic`, `overline`.
- **Decision:** Mở rộng Layout regex: thêm `inset`, `top-`, `bottom-`, `left-`, `right-`, `z-`, `overflow`, `size-`, `translate`, `rotate`, `skew`.
- **Decision:** Hỗ trợ negative prefix `-?` trong cả Spacing và Layout regex để `-mt-4`, `-translate-y-1/2` phân loại đúng nhóm.

## 2026-03-28 - Kiến trúc Hệ sinh thái "App Builder" (The Core Four)
- **Quyết định:** Chuyển dịch toàn bộ dự án từ một "Page Builder" thành một "No-code App Builder Framework" hoàn chỉnh, độc lập nền tảng (Framework-Agnostic). WordPress chỉ được sử dụng như một "vật chủ" tạm thời để tận dụng Admin UI và Authentication.
- **Cấu trúc "The Core Four" (4 Plugins + 1 Theme):**
  1. **Ska Theme:** Theme cốt lõi siêu nhẹ (Barebone), zero CSS.
  2. **Ska No-code Design (Plugin):** Xử lý toàn bộ UI/UX, đóng gói Atomic Blocks và cỗ máy Tailwind CSS v4 JIT.
  3. **Ska Data Pro (Plugin):** Hệ thống Database tự trị. Tách rời hoàn toàn khỏi `wp_posts` và `wp_postmeta` (EAV). Sử dụng bảng phẳng (Flat Tables: `ska_data_*`). Đi kèm Schema Manager và Query Builder (Filter, Sort, Aggregate, JOIN).
  4. **Ska Logic Engine (Plugin):** Bộ não Workflow (Trigger - Action) điều khiển luồng logic sự kiện và kết ghép UI với Data.
  5. **Ska Bridge (Plugin thứ 4 bổ trợ):** Chuyển đổi HTML và xuất JSON Headless.
- **Lý do:** Khắc phục triệt để điểm yếu chí tử của WordPress (bảng postmeta EAV lề mề) để đạt hiệu năng quy mô Enterprise/App. Tách bạch hoàn toàn UI, Data và Logic. Đảm bảo triết lý Scalability tuyệt đối.

## 2026-03-28 - JIT CSS Reset Scoping (Critical Fix)
- **Problem:** Global CSS resets (`--wp--style--block-gap: 0px`, button reset, link reset) trong JIT output dùng `html body.ska-builder` scope → ảnh hưởng **toàn bộ trang** kể cả blog/archive page dùng theme mặc định.
- **Decision:** Scope lại tất cả resets sang `html body.ska-builder [class*='wp-block-ska-builder']` để chỉ ảnh hưởng nội dung bên trong Ska blocks.
- **Decision:** Thêm global link reset (`a { text-decoration: none; color: inherit; }`) trong scope Ska blocks để chống WordPress `a:where(:not(.wp-element-button))` underline.
- **Future:** Lên kế hoạch tạo **Ska Blank Theme** (tương tự Hello Theme của Elementor) để giải quyết triệt để xung đột CSS với WP themes.

## 2026-03-28 - [ROADMAP] Ska Blank Theme
- **Decision:** Thêm vào roadmap: Tạo Ska Blank Theme — WordPress theme tối giản, Tailwind-first, zero WP defaults CSS.
- **Reason:** Giống mô hình Hello Theme (Elementor) / Thrive Theme. Theme sẽ loại bỏ hoàn toàn xung đột CSS gốc (`a { underline }`, `--wp--style--block-gap`, v.v.) mà không cần scoping workarounds.
- **When:** Sau khi plugin core ổn định (blocks, JIT, bridge).

## 2026-03-27 - Button Link Navigation Fix (TagName Override)
- **Problem:** Khối Ska Button khi chọn Action Type là "Link URL" nhưng không thể click chuyển trang ở Frontend. Editor cũng không hiển thị URL khi hover.
- **Root Cause:** Khi Convert bằng Bridge, một thẻ `<button>` gốc sẽ lưu cứng thuộc tính `tagName = 'button'`. Mặc dù người dùng có chuyển sang "Link", `render.php` vẫn bám lấy `tagName` cũ trong database để in ra thẻ `<button>`, gây mất tính năng điều hướng thư mục.
- **Decision:** Ép kiểu tự động (Force semantic tag) theo thời gian thực dựa hoàn toàn trên `actionType`.
- **Implementation:** 
  - `render.php`: Bỏ qua `tagName` được lưu, tự động gán `$tagName = 'a'` nếu `actionType === 'link'`, ngược lại thì `'button'`.
  - `index.js`: Dùng `const Tag = actionType === 'link' ? 'a' : 'button'` để render trong Editor. Cấp `href={url}` cho editor nhưng chặn click bằng `preventDefault()` để vừa hiện link vừa không làm thoát trang.
  - `html-to-blocks.js`: Tự động nhận diện `<button>` vô danh thành thẻ Link `<a>` luôn từ vòng Parse.

## 2026-03-27 - html2tailwind Convert Placement Bug
- **Problem:** Khi thả khối `html2tailwind` vào bên trong một Container và bấm Convert, các khối DOM kết quả bị văng ra khỏi Container (bay ra gốc văn bản) thay vì thế chỗ khối Import.
- **Root Cause:** Hàm `convert()` trong `html-to-blocks.js` đang sử dụng hàm `insertBlocks(blocks)` mặc định của Gutenberg, khiến khối bị đẩy ra ngoài.
- **Decision:** Áp dụng phương thức `replaceBlocks(clientId, blocks)`.
- **Implementation:** 
  - Truyền biến `clientId` từ `ska-bridge-import/index.js` vào hàm `window.ska.bridge.convert(html, clientId)`.
  - Phân nhánh trong Parser: Nếu có `clientId`, gọi `replaceBlocks`, nếu không thì fallback về `insertBlocks`. Loại bỏ đoạn code `removeBlock` rườm rà dư thừa.

## 2026-03-27 - Custom Tailwind Classes for Ska Button Icons
- **Problem:** Khi convert mảng mã HTML sang Ska Button, các class Tailwind của icon (VD: `text-4xl fill-1`) bị phân tích lỗi và vứt bỏ hoàn toàn. Bên cạnh đó, Block Editor Inspector không có tuỳ chọn để tự thêm class cho thẻ `<span>` của icon.
- **Decision:** Cấp thêm một trường văn bản tĩnh `iconClasses` (TextControl) vào thuộc tính của Block Button thay vì xây dựng UI chỉnh Size/Color truyền thống. Tinh chỉnh `html-to-blocks.js` để tự động bóc tách class của Icon và lưu trữ.
- **Reason:** Đảm bảo triết lý "Tailwind first" và "Clean Slate" của dự án. Người dùng toàn quyền sử dụng Tailwind class hoặc custom class cho icon mà không bị bó hẹp trong các UI control cứng nhắc.

## 2026-03-27 - Ska Button Atomic Action & Icon Support
- **Decision:** Giữ vững cấu trúc Atomic Block cho `ska-builder/button`, từ chối việc chuyển đổi thành Wrapper/InnerBlocks.
- **Decision:** Nâng cấp hệ thống Attributes của Block để hỗ trợ Icon (`hasIcon`, `iconName`, `iconPosition`) và Action Type (`link`, `submit`, `popup`).
- **Reason:** Đảm bảo mã HTML xuất ra luôn sạch sẽ, Flat DOM (`<a>` hoặc `<button>`), và tương thích 100% với định hướng No-Code (ánh xạ sang React Component dễ dàng).
- **Implementation:** 
  - Cập nhật `block.json`, `index.js` và `render.php` của khối Button.
  - Cải tiến `html-to-blocks.js` parser để tự động bóc tách Icon `<span>` ra khỏi `<button>` tag và map đúng `actionType`.

## 2026-03-27 - html2tailwind Insertion Fix
- **Problem:** Không thể thả (drag-drop) block `html2tailwind` vào bên trong block `ska-container` hoặc `ska-list-item` trong giao diện Editor.
- **Root Cause:** Thuộc tính `ALLOWED_BLOCKS` truyền vào `useInnerBlocksProps` của 2 block này đang giới hạn danh sách các block được phép nhúng con, và đã bỏ sót `ska-builder/html2tailwind` sau lần tích hợp Bridge.
- **Fix:** Bổ sung thẳng `'ska-builder/html2tailwind'` vào mảng `ALLOWED_BLOCKS` tại `src/ska-container/index.js` và `src/ska-list-item/index.js`.

## 2026-03-27 - Editor Flex-Col Alignment Fix (Icon Centering Bug)
- **Problem:** Icons (VD: `size-10` Material Icons) trong `flex-col` containers bị căn giữa trong Gutenberg Editor nhưng đúng vị trí (trái) trên Frontend.
- **Root Cause 1:** Ska's own CSS rule `.wp-block-ska-builder-container > .wp-block { width: auto !important }` ghi đè Tailwind `size-10` (width: 2.5rem) vì specificity cao hơn CDN's `!important`.
- **Root Cause 2:** Gutenberg inject `.block-editor-block-list__layout` với `align-items: center` → children bị căn giữa. Frontend không có `.wp-block` wrapper nên không bị ảnh hưởng.
- **Fix 1:** Thêm `:not([class*="size-"])` vào child block width reset selector — bảo vệ Tailwind `size-*` utilities khỏi bị override.
- **Fix 2:** Thêm `align-self: stretch !important` trên `.wp-block` children trong `.flex-col:not([class*="items-"])` containers — override Gutenberg's centering mà không phá layout.
- **Anti-Conflict:** `:not([class*="items-"])` tự động tắt khi user set Tailwind alignment class (items-center, items-start...).
- **Scope:** Chỉ ảnh hưởng Editor CSS (inject vào iframe), không chạm Frontend hay Design Engine pipeline.
- **Rejected Approaches:** CSS-only `align-items: stretch` trên parent (Gutenberg wins specificity), JS MutationObserver (script context issues), `align-self: flex-start` trên children (kéo toàn bộ page về trái).

## 2026-03-26 - TailwindPanel Single Attribute Model
- **Decision:** Loại bỏ `splitTailwindClasses()` bên trong `TailwindPanel.js`. `tailwindClasses` là **Single Source of Truth** duy nhất. `className` luôn được set rỗng.
- **Reason:** Trước đó, TailwindPanel nội bộ gọi `splitTailwindClasses()` để tách layout vs styling classes. Điều này gây mất class khi callback chỉ trả về styling classes, layout classes bị "nuốt" mất.
- **Impact:** Cập nhật callback cho TẤT CẢ 8 blocks: `(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })`.

## 2026-03-26 - useInnerBlocksProps cho Flat DOM trong Editor
- **Decision:** Chuyển Container, List, List-Item từ `<InnerBlocks>` component sang `useInnerBlocksProps` API.
- **Reason:** Gutenberg tự động inject `block-editor-inner-blocks` → `block-editor-block-list__layout` wrapper divs khi dùng `<InnerBlocks>`. Các divs này phá vỡ CSS `absolute` positioning và flex/grid layouts trong editor.
- **Trade-off:** `useInnerBlocksProps` thêm class `block-editor-block-list__layout` lên chính wrapper element → WP CSS `position: relative` trên class này xung đột với Tailwind `absolute`. Giải quyết bằng inline styles + CDN `important: true`.
- **Video block:** KHÔNG áp dụng vì Video không dùng InnerBlocks (render content trực tiếp).

## 2026-03-26 - Tailwind CDN Config Loading Order (Critical Bug Fix)
- **Decision:** Config `tailwind.config` PHẢI set SAU khi CDN script load xong (qua `script.onload`), KHÔNG ĐƯỢC set trước.
- **Root Cause:** `window.tailwind = { config: {...} }` set trước CDN → CDN ghi đè `window.tailwind` khi khởi tạo → config bị mất hoàn toàn → `important: true` không hoạt động → TẤT CẢ Tailwind utilities thiếu `!important` → Gutenberg CSS override mọi thứ.
- **Fix:** `script.onload = function() { doc.defaultView.tailwind.config = { important: true, corePlugins: { preflight: false } }; };`
- **Impact:** Sau fix, TẤT CẢ Tailwind utilities trong editor có `!important`, tự động thắng Gutenberg CSS.

## 2026-03-26 - Inline Positioning Styles trong Container Block
- **Decision:** Container block detect positioning classes (absolute/relative/fixed/sticky, inset-*, top/right/bottom/left-*, z-*) và convert thành inline styles trong `useBlockProps`.
- **Reason:** Ngay cả với `important: true` từ CDN, `block-editor-block-list__layout { position: relative }` trên cùng element (do `useInnerBlocksProps`) vẫn có thể xung đột. Inline styles có highest CSS priority — không stylesheet nào override được.

## 2026-03-26 - Editor CSS: Button Border & Container Border Fixes
- **Decision:** Button border reset dùng `:not(.border)` selector: `.wp-block-ska-builder-button:not(.border) { border: none !important; }`.
- **Reason:** Tailwind CDN `important: true` khiến base reset `border-style: solid !important` áp dụng, tạo viền 1px không mong muốn trên buttons. Nhưng containers có class `border` cần giữ viền.
- **Decision:** Container border fallback: `.wp-block-ska-builder-container.border { border-style: solid !important; }`.
- **Reason:** `preflight: false` có thể không set `border-style: solid` mặc định → class `border` chỉ set width mà thiếu style → viền invisible.

## 2026-03-26 - JIT Compiler: Position Offset Utilities
- **Decision:** Thêm `inset-*`, `inset-x-*`, `inset-y-*`, `top-*`, `right-*`, `bottom-*`, `left-*` vào `class-tailwind-compiler.php`.
- **Reason:** Frontend JIT thiếu hoàn toàn positioning offset utilities. `position: absolute` hoạt động nhưng thiếu tọa độ (inset, top, bottom...) → elements absolute nhưng nằm ở normal flow position.
- **Support:** Numeric values (rem), `full` (100%), `auto`, `px` (1px), arbitrary values `[50%]`.

## 2026-03-22 (Late Evening) - Bug Fix: Tailwind Classes Not Being Applied in Container Block
- **Problem:** Users reported that Tailwind classes added via TailwindPanel were not being applied to Container blocks.
- **Root Cause:** In `src/ska-container/index.js` line 26, the useEffect hook was executing `setAttributes({ className: tailwindClasses })` AFTER TailwindPanel had split classes into `tailwindClasses` (styling) and `className` (layout). This override was undoing the split, causing classes to be mixed incorrectly.
- **Process of Discovery:**
  1. Investigated TailwindPanel component - working correctly ✓
  2. Checked Design Engine compiler - supports all positioning classes ✓
  3. Analyzed Style Manager - attribute filtering working correctly ✓
  4. Reviewed block render.php files - fallback logic correct ✓
  5. **Found Issue:** Container block's useEffect (line 26) was the culprit
- **Solution:**
  - Removed the problematic `setAttributes({ className: tailwindClasses })` line from Container block
  - Kept only the auto-migration logic for backward compatibility: `className → tailwindClasses` conversion
  - Properly documented that TailwindPanel callback handles the class split
- **Implementation:**
  - File: `src/ska-container/index.js` (lines 19-27)
  - Removed line that was overwriting className with tailwindClasses
  - Added comment to explain why this is not needed
- **Result:** TailwindPanel now properly splits classes without interference. Classes added by users are correctly saved and applied.
- **Verification:** Tested that other blocks (Text, Button, Image, Icon, Video, List) use the correct pattern and don't have this issue
- **Deployment:** No build step required - file changes take effect on next page load

## 2026-03-22 (Evening) - Absolute Positioning Context Fix in Editor
- **Problem:** Absolute positioning (`absolute`, `fixed`, `sticky`) classes fail in Gutenberg Editor due to missing positioning context caused by `display: contents` on wrapper divs.
- **Root Cause:** Gutenberg automatically wraps blocks with `block-editor-inner-blocks` and `block-editor-block-list__layout`. These wrappers have `display: contents` to remove layout impact, but this also removes positioning context. Child blocks with `position: absolute` can't find a `position: relative` parent, causing layout to break.
- **Solution:** 
  1. Apply `position: relative !important` to `.block-editor-block-list__layout` for Container, Video, and List blocks
  2. Add comprehensive CSS rules for all positioning types: `.relative`, `.absolute`, `.fixed`, `.sticky`
  3. Support inset utility classes (`inset-0`, `inset-x-0`, etc.) by ensuring `width: auto` and `height: auto` on positioned blocks
- **Implementation:**
  - Updated `assets/js/ska-editor-helper.js` (lines 100-171) with enhanced positioning context rules
  - Added selectors for all three structural blocks: `.wp-block-ska-builder-container`, `.wp-block-ska-builder-video`, `.wp-block-ska-builder-list`
  - Each positioning type has dedicated CSS rules with `!important` to ensure Gutenberg styles don't override
- **Result:** Absolute, fixed, sticky, and relative positioning now work consistently in Editor (visual fidelity) and frontend (production)
- **No Breaking Changes:** All changes are CSS-only, injected via existing `ska-editor-helper.js` mechanism

## 2026-03-22 - Backward Compatibility: Restore className Attribute
4: - **Decision:** Khôi phục thuộc tính `className` trong `block.json` của cả 8 block lõi dưới dạng một attribute tường minh.
5: - **Reason:** Khi `supports.className` đặt là `false`, Gutenberg sẽ không chuyển giá trị `className` từ block comment vào object `attributes` trong Editor nếu nó không được định nghĩa rõ ràng. Điều này gây mất dữ liệu hiển thị trong Editor cho các block cũ (mặc dù frontend vẫn chạy nhờ PHP fallback).
6: - **Decision:** Triển khai cơ chế **Auto-Migration** trong `index.js`.
7: - **Logic:** Editor sẽ ưu tiên hiển thị `tailwindClasses || className || ''`. Khi người dùng thực hiện bất kỳ thay đổi nào qua `TailwindPanel`, giá trị mới sẽ được ghi vào `tailwindClasses` và `className` sẽ được xóa sạch (set empty string) để hoàn tất quá trình chuyển đổi sang hạ tầng mới.
8: - **Result:** Khôi phục hiển thị classes, icon ✨ (debug) và đảm bảo tính toàn vẹn của dữ liệu cũ mà vẫn hướng tới kiến trúc sạch.

## 2026-02-07 - Design Engine Initialization
- **Decision:** Use `Ska\Builder\Design` namespace.
- **Decision:** Implement simple `spl_autoload_register` in `design-engine.php` instead of Composer for now to keep it lightweight.
- **Decision:** Mock `Tailwind_Compiler` logic with a stub that returns a dummy CSS class `.ska-example` until we integrate a real JIT engine (PHP or JS-based).
- **Decision:** Use `wp_enqueue_editor_styles` hook in `Core` class (placeholder).

## 2026-02-09 - Data Engine Implementation
- **Decision:** Use `Ska\Builder\Data` namespace.
- **Decision:** Implement `Context_Manager` with a stack system to support nested loops (essential for the project usage).
- **Decision:** Create `Provider` interface to allow easy extension for SCF, User, Term data later.
- **Decision:** Use `{{key}}` syntax for data binding, compatible with standard mustache/handlebars style but lightweight regex implementation.
- **Decision:** Integrated Data Engine with Blocks via `render.php` calling `bind_data`.
- **Decision:** Created `Ska Text` block as the first atomic block to test dynamic data.
- **Decision:** Implemented Logic Engine (`Logic\Core`) to handle `{{#if}}` and `{{#foreach}}` tags recursively using standard RegEx.
- **Decision:** Logic Engine wraps Data Engine binding, so compiled content is automatically data-bound.
- **Decision:** Implemented vanilla JS `index.js` for Ska Text block to support Gutenberg Editor and fix "Block not supported" error.
- **Decision:** Manually required `interface-provider.php` to fix autoloading race condition.

## 2026-03-10 - Block Implementation & UI/UX Improvements
- **Decision:** Sử dụng JSX/React cho toàn bộ các block mới (`Ska Image`, `Ska Icon`) để đồng bộ với chuẩn Gutenberg hiện đại.
- **Decision:** Triển khai Icon Picker tìm kiếm được, sử dụng Material Icons cho block `Ska Icon`.
- **Decision:** Enqueue trực tiếp font `material-icons-outlined` vào Block Editor và inject vào iframe để đảm bảo icon hiển thị trực quan (Visual Rendering).
- **Decision:** Sử dụng block versioning (1.0.1) và timestamp cho script enqueue để ép trình duyệt làm mới cache cho các asset của editor.
- **Decision:** Cập nhật `Tailwind_Compiler` hỗ trợ nhận diện các responsive prefixes (`sm:`, `md:`, `lg:`) để tránh lỗi layout bị sập trên frontend.

## 2026-03-13 - Video Block Refinement & Atomic Standards
- **Decision:** Loại bỏ toàn bộ `default` classes trong `block.json` để tuân thủ nguyên lý Atomic. Blocks phải bắt đầu "sạch" để tránh lỗi attribute leaking.
- **Decision:** Đồng bộ hóa thủ công thư mục `build/` và `src/` cho plugin metadata vì WordPress đăng ký block từ `build/`.
- **Decision:** Thay thế việc ép `overflow: hidden !important` bằng `isolation: isolate !important` trong editor helper. Điều này tạo Stacking Context chuẩn để việc cắt góc (clipping) iframe hoạt động mượt mà khi người dùng tự thêm class `overflow-hidden`.
- **Decision:** Sử dụng `MutationObserver` bên trong iframe để đảm bảo style được inject ngay khi dynamic blocks (như Video) xuất hiện.

## 2026-03-14 - Ska List & html2tailwind preparation
- **Decision:** Chốt cấu trúc block `Ska List` làm bước đệm cho tính năng `html2tailwind`. Phải có cấu trúc list chuẩn HTML trước để mapping.
- **Decision:** Kiến trúc 2 block độc lập kết hợp qua `InnerBlocks`: `ska-builder/list` (máy chủ `<ol>/<ul>`) và `ska-builder/list-item` (`<li>`). Đảm bảo Tailwind có thể control ở cấp cha (grouping) và cấp con (item specific) đồng thời tuân thủ atomic defaults.

## 2026-03-14 - Atomic CSS Standardization
- **Decision:** Tiến hành tổng rà soát và dọn dẹp toàn bộ Inline CSS cứng (hardcoded class & style) trong các file PHP Render của Block lõi (Container, List, List Item, Video).
- **Decision:** Tôn trọng tuyệt đối triết lý "Clean Slate" (Mâm xôi) của dự án Ska. Các class Tailwind phải do người dùng chủ động kiểm soát.
- **Decision:** Sửa lỗi trùng lặp class Tailwind do cơ chế tự nối chuỗi của hàm `get_block_wrapper_attributes()` kết hợp với biến `$user_className`.
- **Decision:** Xử lý lỗi UI `Ska Image` bị tràn ngang trong `Flexbox` bằng cách thiết lập class responsive mặc định `max-w-full h-auto object-cover` lên thẳng thẻ `<img>` con, tránh áp đặt `!important` làm xung đột JIT.
- **Decision (JIT Refactor):** Chốt phương án loại bỏ 100% cờ `!important` khỏi hệ thống Biên dịch JIT (`class-tailwind-compiler.php`) và CDN Fallback (`class-core.php`). Thay vì dùng `!important` để chống lại CSS rác của WP Themes, quyết định chuyển sang dùng CSS Specificity Scope Selector (`body .ska-builder`) để bảo toàn tính sạch sẽ (Cascading) của CSS, dọn đường cho tính năng Headless (Next.js/React html2tailwind) trong tương lai.
## 2026-03-15 - UI Refinement: Icon Indicator
- **Decision:** Thay thế "Dot Indicator" (chấm tròn) bằng "Icon Indicator" (sử dụng icon `auto_awesome` từ Material Icons) cho các trạng thái Active của panel.
- **Decision:** Lý do: Chấm tròn CSS dễ bị biến dạng (squashed) do layout Flexbox/Sidebar của WordPress. Icon font đảm bảo độ ổn định và tính thẩm mỹ cao cấp (Premium logic).
- **Decision:** Áp dụng màu xanh Emerald (#10b981) và hiệu ứng glow nhẹ cho icon khi active.

## 2026-03-18 - Bridge Integration & Admin Dashboard Toggle
- **Decision:** Hội quân module **Conversion Bridge** vào **Ska Builder Core**. Toàn bộ logic Parser JS được chuyển về Core assets.
- **Decision:** Sử dụng Options API (`ska_bridge_enabled`) để kiểm soát việc nạp script Parser và đăng ký block `html2tailwind`.
- **Decision:** Tích hợp Toggle UI vào bảng "System Status" trong Admin Dashboard của Ska để người dùng tự do bật/tắt module Bridge.
- **Decision:** Chuẩn hóa attribute mapping: Chuyển từ `tailwindClasses` sang `className` trong Parser để đồng bộ với cơ chế render mặc định của Ska Blocks, đảm bảo style Tailwind được áp dụng ngay sau khi convert.
- **Decision (Parser Enhancement):** Nâng cấp Parser JS hỗ trợ xử lý toàn bộ tài liệu HTML (tự động trích xuất `<body>`), mở rộng mapping cho `A`, `BUTTON` -> `ska-builder/button` và `SPAN` -> `ska-builder/icon` khi có các class Material.
- **Decision (Attribute Fallback):** Bổ sung cơ chế fallback `data-alt` cho hình ảnh để tương thích với các công cụ generate code hiện đại thường dùng data-attributes.
## 2026-03-19 - html2tailwind: TagName & Body Preservation
- **Decision:** Thiết lập thuộc tính `tagName` cho `Ska Container` block dựa trên tag gốc của HTML (section, article, etc.) thay vì ép về `div`.
- **Decision:** Tự động trích xuất `className` của thẻ `<body>` trong tài liệu HTML đầy đủ để áp dụng vào block Container bao ngoài cùng (Root Wrapper).
- **Decision:** Sử dụng React `CustomTag` (dynamic rendering) trong `index.js` của block Container để đồng bộ hiển thị tag (section, header...) ngay trong Block Editor thay vì chỉ ở frontend.
- **Decision:** Tuyệt đối không sử dụng `!important` trong JIT Compiler. Thay vào đó, sử dụng tổ hợp `body.ska-builder` và đẩy độ ưu tiên inject CSS lên mức 999 tại `wp_head` để ghi đè CSS Theme một cách hợp lệ.

## 2026-03-19 - Final Layout & Icon Stabilization
- **Decision:** Sử dụng **"Class Doubling"** (`.class.class`) trong Compiler. Lý do: Gaps và Spacing thường bị Themes đè rất mạnh, doubling là cách an toàn và mạnh mẽ nhất để "thắng" mà vẫn giữ CSS sạch chuẩn Tailwind.
- **Decision:** Áp dụng `number_format( $val, 3, '.', '' )` cho toàn bộ CSS output. Lý do: Ngăn chặn lỗi CSS bị trình duyệt từ chối khi server chạy ở môi trường locale dùng dấu phẩy (như Việt Nam) gây lỗi mất khoảng cách (gap).
- **Decision:** Gỡ bỏ `w-full` khỏi Whitelist mặc định của `Style_Manager`. Lý do: Tuân thủ triết lý "Mâm xôi" (Clean Slate). Nếu block không cần `w-full`, hệ thống không nên tự sinh CSS cho nó để tránh phình to file style và gây xung đột layout mặc định.
- **Decision:** Đồng bộ hóa thủ công (Local Sync) thư mục `build/` cho PHP files. Lý do: WordPress đăng ký block từ `build/`, nhưng `wp-scripts` không tự copy các file `.render.php`. Việc sửa ở `src/` mà không copy sang `build/` dẫn đến nạp code cũ gây lỗi layout "ma" cực kỳ khó debug.
- **Decision:** Sử dụng `html_entity_decode` kết hợp `wp_kses` với whitelist mở rộng (`aria-hidden`, `style`) cho block `Ska Text`. Lý do: Hỗ trợ render icons Material chính xác ngay cả khi nội dung bị thoát chuỗi bởi các trình editor/parser khác nhau.
- **Decision:** Chốt phương án quy trình **Build Sync** sử dụng script Node.js (`npm run sync`).
- **Workflow:** AI phải luôn hỏi ý kiến người dùng trước khi thực hiện đồng bộ PHP từ `src/` sang `build/`.
- **Reason:** Giải quyết triệt để lỗi "Ghost Bug" (nạp code PHP cũ) mà vẫn đảm bảo quyền kiểm soát tuyệt đối của người dùng đối với mã nguồn.
## 2026-03-20 - Bug Fix: Hybrid Source Architecture & Bridge Mapping
- **Decision:** Mở rộng bảng màu Tailwind nội bộ lên đầy đủ các palette chính (Slate, Zinc, etc.) và hỗ trợ `color/opacity` cho palette chuẩn qua Regex.
- **Decision:** Bổ sung cơ chế quét "Deep Attribute" trong `Style_Manager` để trích xuất class từ nội dung HTML trong attribute `content`.
- **Decision:** Cập nhật Bridge Parser sang `className` để đồng bộ hoàn toàn với Core blocks.
- **Decision:** **Hybrid Source Architecture**: Thay vì phụ thuộc hoàn toàn vào Tailwind JIT (JS) trong Editor, sử dụng PHP Core (Source of Truth) để nảy sinh CSS tĩnh cho các màu thương hiệu cố định. Điều này đảm bảo tính ổn định tuyệt đối, đúng triết lý "Load từ Plugin Source".
- **Decision:** Gỡ bỏ cấu hình `darkMode: 'class'` trong Editor để đồng bộ với quyết định tạm hoãn Dark Mode.
- **Decision:** **Inline Style Support (Issue #3)**: Thêm thuộc tính `customStyle` (string) cho core blocks để bảo toàn các style đặc thù (như `background-image: url()`) từ HTML gốc. 
- **Decision:** Tích hợp bộ giải mã `parseStyle` trong JS của block để hỗ trợ preview 1:1 các style này ngay trong Gutenberg.
- **Decision:** **Infrastructure-Level Layout Fix**: Thay vì ép class `relative` vào block, quyết định xử lý hiển thị `absolute` thông qua `ska-editor-helper.js`. Điều này giúp hạ tầng Editor tự "hiểu" và hỗ trợ các class positioning của người dùng mà không làm bẩn (clutter) code của block, tuyệt đối tuân thủ nguyên tắc **"Clean Slate"**.

## 2026-03-21 - html2tailwind Whitespace & Hover Animation
- **Decision:** Cập nhật Bridge Parser (`html-to-blocks.js` tại `ska-conversion-bridge` và `ska-builder-core`) để sử dụng `.replace(/\s+/g, ' ').trim()` xử lý triệt để lỗi sinh khoảng trắng thừa và rớt dòng khi convert `DOMParser` node sang chuỗi Text/Button tại Editor.
- **Decision:** Tái cấu trúc `class-tailwind-compiler.php` (Design Engine) để nhận diện và tách các pseudo-classes (`hover:`, `focus:`, `group-hover:`) trước khi đưa vào bảng biến dịch. Rút khỏi thẻ `$unresolved` nhằm sửa hoàn toàn lỗi không tạo được Animation Hover.
- **Decision:** Bổ sung Rules biên dịch CSS tĩnh cho thuộc tính `transition-*` (như `transition-transform`) và `scale-*` (hỗ trợ Arbitrary values như `scale-[1.02]`). Các rule mở rộng này được giải quyết ở tầng Backend, không yêu cầu npm build.

## 2026-03-21 - Structural Isolation & Attribute Migration
- **Decision:** Thực hiện cuộc đại tu kiến trúc: Chuyển đổi tên thuộc tính từ `className` sang `tailwindClasses` cho tất cả 8 block lõi.
- **Decision:** **Tại sao?** Để ngăn chặn Gutenberg tự động ghép class vào wrapper `div`. Giúp tách biệt hoàn toàn Logic CSS của Ska khỏi hạ tầng của WordPress (khắc phục lỗi "Red Box" khi hover).
- **Decision:** **Inner Element Isolation (Frontend)**: Cập nhật `render.php` cho `Ska Button` và `Ska Image` để di chuyển `get_block_wrapper_attributes()` sang thẻ con (`<a>`, `<img>`). 
- **Decision:** Cung cấp cơ chế Fallback thuộc tính mạnh mẽ cho các block cũ (`$tailwindClasses ?? $className ?? ''`) để đảm bảo tính tương thích ngược.
- **Decision:** Tích hợp **Scoped CSS Reset** cho thẻ `button` bên trong môi trường `.ska-builder` tại `class-tailwind-compiler.php`. Mục tiêu: Loại bỏ viền 1px và nền xám mặc định của trình duyệt mà không ảnh hưởng đến phần còn lại của WordPress Admin.
- **Decision:** Sử dụng `npm run sync` để dập tắt triệt để các "Ghost Bug" (lỗi nạp code PHP cũ) sau khi thay đổi logic render.

## 2026-03-21 - Tailwind Panel Animation Support
- **Decision:** Tạo phân loại mới **"Transitions & Animation"** trong `TailwindPanel.js`.
- **Decision:** Tích hợp regex trong Panel để nhận diện và hiển thị giá trị cho `transition`, `duration`, `ease`, `scale`.
- **Decision:** Mở rộng JIT Compiler hỗ trợ `duration-` (ms) và `ease-` (cubic-bezier mapping).

## 2026-03-21 - html2tailwind: Attribute Mapping Update
- **Decision:** Cập nhật Bridge Parser để map toàn bộ class trích xuất được vào thuộc tính `tailwindClasses` instead of `className`.
- **Decision:** Sửa lỗi khoảng trắng thừa (`trim()`) khi convert nội dung `button` từ HTML sang block.

## 2026-03-27 - Icon Library & Search Fix
- **Decision:** Tách icon data ra file riêng `src/utils/material-icons.js` (4207 icons từ Google Material Symbols codepoints).
- **Decision:** Export 2 arrays: `POPULAR_ICONS` (~60 icon phổ biến cho grid mặc định) và `ALL_ICONS` (toàn bộ 4207 icons cho search).
- **Decision:** Thay thế sidebar grid nhỏ bằng Modal popup (680px, `@wordpress/components` Modal) với search + grid responsive (`auto-fill, minmax(75px, 1fr)`).
- **Decision:** Thêm TextControl "Icon Name" cho phép nhập trực tiếp tên icon bất kỳ — future-proof.

## 2026-03-27 - Smart Text Detection (Parser Fix)
- **Decision:** Bổ sung "Smart Text Detection" trong `html-to-blocks.js`: khi text-mapped tag (`<p>`, `<span>`, `<h1-h6>`) chứa child elements là icon (`material-symbols-outlined/material-icons-outlined`), `<img>`, hoặc `<button>`, promote tag thành `ska-builder/container` thay vì `ska-builder/text`.
- **Reason:** `<p class="flex items-center gap-1"><span class="material-symbols-outlined">trending_up</span> 25% YoY</p>` — icon bị mất khi parser giữ innerHTML thô thay vì convert children thành inner blocks.
- **Decision:** Thêm `listType` attribute mapping cho `ska-builder/list` blocks (`ul` hoặc `ol`).

## 2026-03-27 - [ROADMAP] RichText Inline Formatting
- **Decision:** Chọn Option 1 (RichText) cho bài toán inline styled `<span>` trong `<p>`. Ưu tiên: TRUNG HẠN.
- **Scope:** Refactor `ska-builder/text` dùng `<RichText>` component thay vì raw `<span>`. Hỗ trợ bold, italic, link, và custom Tailwind format toolbar.
- **Reason:** WordPress native RichText đã tối ưu cho inline formatting. Tạo custom format cho phép wrap text bằng `<span class="text-green-500">` trực tiếp trong editor.
- **Status:** DEFERRED — thực hiện khi rảnh, không blocking.

## 2026-03-27 - [Fix] Icon Font Migration & UI Rendering
- **Problem:** Nút "Ska Tailwind" hiển thị text `auto_awesome` thay vì icon lấp lánh do chưa migrate triệt để.
- **Decision:** Thay đổi toàn bộ string `material-icons-outlined` còn sót lại thành `material-symbols-outlined` trong bộ UI inspector (`TailwindPanel.js`, `ska-image/index.js`). Sửa cứng thuộc tính `fontFamily` CSS trong file editor helper.

## 2026-03-27 - [Architecture] Flat DOM Text Parsing Behavior
- **Problem:** User nhầm tưởng đoạn text trần (TextNode) nằm cạnh Icon bên trong một HTML `<p>` bị mất class Tailwind khi block-conversion.
- **Decision:** Giữ nguyên logic DOM Tree Mapping: thẻ `<p>` cha (được thăng cấp thành Container div) NHẬN toàn bộ class Tailwind (`flex items-center text-green-500`), còn Text child node CHỈ kế thừa CSS (hiển thị màu xanh nhưng không có thuộc tính tailwindClasses trong inspector).
- **Reason:** Đảm bảo "Clean Slate Framework", bảo toàn cấu trúc mâm xôi thuần túy của mã HTML. Text node không ôm class nếu HTML gốc nó không có class.
