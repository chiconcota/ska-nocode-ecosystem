# MODULE: DESIGN ENGINE
> **Namespace:** `Ska\Builder\Design`
> **Path:** `ska-builder-core/inc/design-engine/`

## 1. Nhiệm vụ (Responsibility)
Module này đóng vai trò là "Kiến trúc sư trưởng" về giao diện của hệ thống.
- **Local JIT Compiler:** Tự động biên dịch class Tailwind thành CSS thuần phía server (PHP) để tối ưu Frontend.
- **Hybrid Fallback:** Tự động nhận diện class không hỗ trợ và kích hoạt Tailwind CDN làm phương án dự phòng an toàn.
- **Editor Real-time:** Hỗ trợ render style ngay lập tức trong Gutenberg Iframe thông qua `assets/js/ska-editor-helper.js`. Sử dụng `MutationObserver` để tự động kích hoạt injection khi block mới xuất hiện. Tích hợp chỉ báo trạng thái hoạt động (Active State) bằng icon ✨ (`auto_awesome`).
- **Layout Fix (2026-03-26):** Áp dụng `useInnerBlocksProps` cho Container/List/List-Item để loại bỏ wrapper divs. Video blocks vẫn dùng `display: contents` (không dùng InnerBlocks). Editor CSS overrides cho positioning, border fallback cho containers.
- **Tailwind CDN Proxy Mutation (2026-04-06):** Thay vì set config trước khi load hoặc gắn một khối script JSON tĩnh, Config `important: true` phải được gán SAU KHI CDN script load xong (`script.onload`) HOẶC qua việc Mutation Object `doc.defaultView.tailwind.config`. Cơ chế Proxy của Tailwind v3 sẽ tự bắt dính thay đổi và kích hoạt re-build. Đây là giải pháp chốt để giải quyết Race Condition của Iframe. Đi kèm Polyfill thủ công cho Layout V4 (`-outline-offset-*`) và CSS Reset Parity.
- **V4 Group States & Pseudo Elements (2026-04-06):** PHP JIT nâng cấp mạnh mẽ để biên dịch các directive V4 Form (gồm `:indeterminate`, `group-has-*`, `peer-has-*`, `has-*`, v.v.). Hỗ trợ thuộc tính chèn Object giả (như `before:/after:content-['']`).
- **Logical Direction Spacing (2026-04-06):** PHP JIT được trang bị khả năng dịch LTR/RTL layout tự động thông qua Logical Directions: `start-*`, `end-*`, `ms-*` (margin-start), `pe-*` (padding-end). Dùng để render các UI phức tạp như Flex Toggle / Switch UI.
- **Native Dark Mode (2026-05-14):** Tích hợp hệ thống Dark Mode dựa trên JIT Compiler (hỗ trợ modifier `dark:` sinh CSS rule với scope `html.dark`). Quản lý state toàn cục qua Alpine.js Store (`skaTheme`). Cung cấp Action `Toggle Dark Mode` trực tiếp trên block `ska-button`, tự động dọn dẹp các attribute thừa thãi của Alpine khi đổi loại action khác. Chống FOUC bằng Inline Script tiêm cực sớm ở `wp_head`. Fix hoàn toàn lỗi bất đồng bộ màu sắc giữa Editor và Frontend bằng cách tiêm CSS Selector ưu tiên cao (`!important` + `.editor-styles-wrapper`) trực tiếp vào Design Engine để ép Tailwind CDN tuân thủ Source of Truth.
- **SkaWind JS (Định hướng Phase 6):** Kế hoạch xây dựng bộ biên dịch JIT thuần Vanilla JS chạy trên Editor thay thế hoàn toàn Tailwind CDN, nhằm đồng nhất 100% logic tráo mảng màu (Array Swapping) với PHP Backend mà không cần các trick CSS Specificity.
- **JIT Loop Scanner & Zero CDN Policy (2026-05-10):** Nâng cấp `Style_Manager` đệ quy an toàn vào `slots` của `ska-builder/loop`. Mở rộng cơ chế quét toàn bộ bài viết trên trang Archive/Home (`$wp_query->posts`) thay vì chỉ lấy bài viết hiện tại. Chấm dứt sử dụng Tailwind CDN trên Frontend để tối ưu Core Web Vitals, chuyển 100% class sang Static CSS.
- **CSS Specificity (Frontend):** Áp dụng cơ chế **Class Doubling** (`.class.class`) kết hợp tiền tố `html body.ska-builder` để thắng CSS Theme mà không dùng `!important`.
- **CSS Block Reset Specificity (2026-04-06):** Ứng dụng pseudo-class `:where()` vào các logic Mâm Xôi (Reset). Ví dụ: `button:not(:where(.components-button))` nhằm hạ điểm Specificity từ 33 xuống 23. Từ đó trải thảm cho các class của tiện ích Tailwind (đang mang điểm 32) tuỳ ý ghi đè mà không lo bị chặn đứng.
- **Form Preflight & Native Reset (2026-04-06):** Thiết lập Reset cấp độ Structural dài dòng (VD: `.editor-styles-wrapper .block-editor-block-list__block.wp-block-ska-builder-input`) mang Specificity cao (30) nhưng KHÔNG có `!important` để dọn sạch CSS của WP Admin. Đồng bộ viền mặc định `border-color: #e5e7eb` với Frontend để xoá nhòa khoảng cách Parity.
- **Editor Specificity Fix (2026-03-29):** Gỡ bỏ cờ `margin: 0 !important;` quá khích khỏi `ska-editor-helper.js` trên `.wp-block`, trả lại quyền điều khiển `margin` (mt-*, mb-*) cho cấu hình JIT Reset (`[class*='wp-block-ska-builder']`) và lệnh Tailwind CDN.
- **Fractional Resolving (2026-03-29):** Bổ sung Regex tính toán thập phân (`1/2` = `50%`, `1/3` = `33.333333%`) cho JIT (`w-*`, `h-*`), đảm bảo tính nhất quán với thiết lập CDN trên Editor giúp sửa lỗi fallback `w-full`.
- **Strict Modifier Validation (2026-04-06):** PHP JIT Compiler được vá lỗ hổng "lọt sàng" (leak). Các class có chứa modifier lạ (không khai báo, ví dụ `dark:`, `sida:`, `hoaqua:`) sẽ bị hệ thống nhận diện là "không hợp lệ" (invalid modifier) và lập tức từ chối biên dịch, thay vì cố gắng tách đuôi và xuất ra CSS áp dụng toàn cục làm vỡ thiết kế.
- **Atomic Button Reset (2026-03-29):** Ấn định `.ska-button-block` luôn hiển thị kiểu `inline-flex` ở cấp Design Engine Reset thay cho thẻ `<a>/<button>` `inline` khô cứng, giúp hệ thống margin/spacing hoạt động chính xác.
- **Locale CSS Safety:** Toàn bộ CSS output cho spacing/dimensions được xử lý qua `number_format` với dấu chấm thập phân (`.`) để đảm bảo tính hợp lệ trên mọi môi trường server.
- **Underscore Visibility Bug (2026-06-07 - Đã giải quyết):** Phát hiện font `Outfit` (secondary font) khi chạy trên hệ điều hành Linux gặp lỗi glyph làm ký tự gạch dưới `_` hiển thị như khoảng trắng trong Gutenberg Settings Sidebar. Giải quyết triệt để bằng cách bổ sung hook `admin_body_class` vào `class-core.php` để chèn class `ska-builder` vào body của WP Admin. CSS override font-family hệ thống (`-apple-system...`) và font-size 14px cho sidebar inputs nhờ đó được kích hoạt thành công, giúp dấu gạch dưới `_` hiển thị rõ nét trên toàn bộ các input/textarea của sidebar.


## 2. Luồng hoạt động (Architecture Flow)
1. **Input:** User nhập class `p-4 text-red-500 shadow-xl` vào Ska Block.
2. **Process:**
   - `Style_Manager::scan_post_classes()` quét toàn bộ `post_content`, trích xuất class từ thuộc tính `tailwindClasses`, `className` và các string attributes khác. Đặc biệt **hỗ trợ trích xuất tự động class từ mảng `htmlAttributes`** (ví dụ: các class nằm trong `x-transition:*` của Alpine.js) giúp xoá bỏ hoàn toàn thủ thuật cấu hình mồi "snail-trails".
   - `Tailwind_Compiler::compile_classes()` biên dịch:
     - Xử lý các tiền tố Responsive (VD: `sm:`, `md:`, `lg:`...) và tạo `@media` queries.
     - Dịch các class cơ bản: `p-4`, `text-red-500`, `grid-cols-2`, `mx-auto` -> Trả về CSS chuẩn (không dùng `!important`).
     - Tự động tách và xử lý các pseudo-classes (`hover:`, `focus:`, `group-hover:`) để hỗ trợ animation.
3. **Output:** 
   - Inject thẻ `<style>` chứa CSS đã compile vào Header, được bao quanh bởi selector `body.ska-builder`.
   - **Atomic Reset:** Tự động chèn CSS Reset cho thẻ `button` trong phạm vi `.ska-builder` để xóa viền/nền mặc định của trình duyệt.
   - Nếu có `unresolved` -> Inject thêm CDN Script làm fallback (`window.tailwind = { config: ... }`).
   - Trong Editor: Kích hoạt `ska-editor-helper.js` để đồng bộ Iframe.

## 3. Giao diện (Interface Contracts)

### A. Actions (Hooks)
| Hook Name | Tham số | Mô tả |
| :--- | :--- | :--- |
| `enqueue_block_assets` | `none` | Đăng ký CSS/JS cho cả Frontend và Editor. |
| `wp_head` / `admin_head` | `none` | Inject trực tiếp styles/scripts ưu tiên cao. |

### B. Filters
| Filter Name | Input | Output | Mô tả |
| :--- | :--- | :--- | :--- |
| `ska_compile_tailwind` | `string` | `array` | Trả về `['css' => $css, 'unresolved' => $array]`. |
| `ska_get_tailwind_config` | `none` | `array` | Lấy cấu hình Tailwind. |

## 4. Lộ trình tích hợp (Roadmap)
- [x] **Responsive**: `sm:`, `md:`, `lg:`, `xl:`, `2xl:`, `max-sm:` ~ `max-2xl:`.
- [x] **Grid Layout**: `grid-cols-*`, `grid-rows-*`, `col-span-*`, `gap-*`, `gap-x-*`, `gap-y-*`.
- [x] **Spacing & Layout**: `p-*`, `m-*`, `px-*`, `py-*`, `mx-auto`, `space-x-*`, `space-y-*`, `max-w-*`, `container`.
- [x] **Typography**: `text-xs` ~ `text-9xl`, `font-thin` ~ `font-black`, `leading-*`, `tracking-*`, `text-center/left/right/justify`, `italic/not-italic`, `uppercase/lowercase/capitalize/normal-case`, `underline/no-underline/overline/line-through`, `whitespace-*`, `truncate`, `line-clamp-*`.
- [x] **Colors**: `text-{color}-{shade}`, `bg-{color}-{shade}`, full 11 shade (50-950) cho 22 palettes. Basic color + opacity: `bg-black/30`, `text-white/80`.
- [x] **Custom Color Registry**: `bg-{name}`, `text-{name}`, `border-{name}`, `ring-{name}`, `shadow-{name}`, `from-{name}`, `to-{name}` + opacity modifier.
- [x] **Dimensions**: `w-*`, `h-*`, `min-w-*`, `max-w-*`, `min-h-*`, `max-h-*`, `size-*` (v3.4).
- [x] **Object Fit**: `object-cover`, `object-contain`, `object-fill`, `object-none`.
- [x] **Aspect Ratio**: `aspect-video`, `aspect-square`, `aspect-auto`.
- [x] **Shadows**: `shadow-sm` đến `shadow-2xl`, `shadow-none`. (Hỗ trợ shadow colors qua `shadow-{name}/{opacity}`).
- [x] **Z-Index**: `z-0` ~ `z-50`, `z-auto`.
- [x] **Display**: `hidden`, `block`, `inline-block`, `flex`, `inline-flex`, `grid`, `inline-grid`.
- [x] **Flexbox**: `flex-col`, `flex-row`, `flex-wrap`, `items-*`, `justify-*`, `flex-1/auto/initial/none`, `flex-shrink/grow`, `self-*`, `order-*`.
- [x] **Overflow**: `overflow-hidden`, `overflow-auto`, `overflow-x-*`, `overflow-y-*`.
- [x] **Position**: `relative`, `absolute`, `fixed`, `sticky`, `static`.
- [x] **Position Offsets**: `inset-*`, `inset-x-*`, `inset-y-*`, `top-*`, `right-*`, `bottom-*`, `left-*` (numeric, full, auto, px, arbitrary).
- [x] **Rounded**: `rounded` đến `rounded-3xl`, `rounded-full`, `rounded-none`.
- [x] **Border**: `border`, `border-{width}`, `border-t/r/b/l-{width}`, `border-solid/dashed/dotted/double/hidden/none`, `border-{color}-{shade}`, `border-white/black/transparent`.
- [x] **Pseudo-class Prefix**: `hover:`, `focus:`, `active:`, `disabled:`, `group-hover:`.
- [x] **Transitions**: `transition-*`, `duration-*`, `ease-*`.
- [x] **Transforms**: `scale-*`, `translate-x/y-*` (numeric, fraction, arbitrary), `rotate-*`.
- [x] **Negative Prefix**: `-mt-4`, `-translate-y-1/2`, `-inset-x-4` — universal handling.
- [x] **Filters & Effects**: `backdrop-blur-*`, `blur-*`, `brightness-*`, `contrast-*`, `opacity-*`, `isolate`.
- [x] **Arbitrary Values**: `w-[200px]`, `min-w-[120px]`, `p-[1.5rem]`, `mt-[calc(100%-2rem)]`, `translate-x-[50%]`, `rotate-[30deg]`, `content-['']`.
- [x] **Interactivity & Display**: `cursor-*`, `pointer-events-*`, `select-*`, `sr-only`, `not-sr-only`.
- [x] **Slate/Amber/Green... Palette**: Full Tailwind palette (22 colors × 11 shades).
- [x] **Backgrounds**: `bg-cover/contain/auto`, `bg-center/top/bottom/left/right`, `bg-no-repeat/repeat-x/repeat-y`, `bg-fixed/local/scroll`, `bg-clip-*`, `bg-gradient-to-*`, `from-*`, `via-*`, `to-*`.
- [x] **Ring**: `ring-*`, `ring-{color}-{shade}`, `ring-offset-*`, `ring-offset-{color}`, `ring-inset`.

## 5. Ghi chú triển khai
- **Attribute Migration (2026-03-21):** Chuyển đổi toàn bộ Block sang sử dụng `tailwindClasses` thay vì `className`. Điều này ngăn chặn Gutenberg tự ý can thiệp vào CSS của block thông qua thẻ wrapper.
- **Atomic Button Reset (2026-03-21):** Tự động inject CSS Reset cho thẻ `button` trong vùng `.ska-builder` để xóa border và background mặc định của trình duyệt.
- **CSS Reset Scoping (2026-03-28 Critical):** TẤT CẢ JIT resets (block-gap, button, link) phải scope bằng `[class*='wp-block-ska-builder']` — KHÔNG ĐƯỢC dùng `body.ska-builder` trực tiếp vì sẽ phá layout blog/archive page dùng theme mặc định.
- **Link Reset (2026-03-28):** `a { text-decoration: none; color: inherit; }` trong scope Ska blocks để chống WordPress defau- **Hybrid Source Architecture:** Sử dụng PHP Core để pre-generate CSS cho brand colors.
- **Inline Style Support:** Thuộc tính `customStyle` cho phép bảo toàn và hiển thị các style "cứng" (background-image).
- **Locale Independence:** Sử dụng `number_format( $val, 3, '.', '' )` trong PHP.
- **Style Scanning:** `Style_Manager` ưu tiên quét từ `tailwindClasses`, sau đó là `className`, `customStyle` và nội dung HTML.
- **🔧 Planned Refactor:** Tách file compiler (>500 dòng) thành: `compiler` + `config` + `color-registry`.

## 6. Lộ trình Skapine Engine (Live Preview Ecosystem - Phase 4)
- **Kiến trúc DOM Áo (Virtual DOM)**: Skapine Engine đóng vai trò "Monkey-patching" bên trong Iframe của Gutenberg Editor. Nó sẽ mô phỏng lại các hoạt động của Alpine.js (như giả lập click mở Tab, xổ Accordion, trigger hover) mà không cần tải thư viện Alpine thuần chủng. Điều này bảo vệ Editor không bị crash do re-render xung đột.
- **Global State Control**: Mọi trạng thái dữ liệu tĩnh/động sẽ được gom về Single Source of Truth: `Alpine.store('skaBuilder')`.
- **Attribute UI Mapping**: Thay vì bắt người dùng gõ tay `x-data`, Skapine sẽ có bảng Panel riêng cung cấp sẵn các nút gạt thông minh (Smart Defaults) phục vụ các hiệu ứng hover, transition cơ bản nhất.

## 7. Quản trị Design Tokens & Brand Identity (Ska Smart Objects - Phase 4)
- **Kiến trúc Dữ liệu Phẳng**: Dữ liệu Design Tokens cấu hình từ giao diện "Brand & Colors" không còn lưu tại `wp_options` rác, mà chuyển sang lưu an toàn trong bảng phẳng chuyên biệt `ska_data_sys_presets` của Ska Data Pro qua column `json_content`. Bao gồm 2 preset chính: `token_color` và `token_brand` (chứa logo và nhận diện thương hiệu).
- **Đồng bộ Dữ Liệu (camelCase Protocol):** Cấu trúc Schema của Design Tokens API (`class-design-tokens-api.php`), Tailwind Compiler, và File JSON tĩnh bắt buộc sử dụng chuẩn `camelCase` (ví dụ: `containerWidth`) để khớp 1:1 với state schema của Alpine.js trên Frontend. 
- **Mảng Màu Động (Dynamic Color Array):** Giao diện quản lý màu sắc đã được chuyển đổi từ Object tĩnh sang mảng đối tượng động (`[{key: 'primary', value: '#...'}, ...]`). Thiết kế này cho phép người dùng tùy ý thêm, xóa và đổi tên các biến màu, đảm bảo sự linh hoạt tối đa cho hệ thống Design System.
- **Hệ thống Bộ đệm Physical JSON (Physical Caching)**: Để tăng tốc truy xuất và đáp ứng trực tiếp hệ thống JIT Tailwind Editor/Frontend, Plugin tự động dịch toàn bộ Payload Database (Colors, Brand Logo, Typography) sang trạng thái tệp vật lý `.json` lưu tại `wp-content/uploads/ska-data/tokens.json`. Điều này giải phóng CPU Database khỏi việc gọi data cho mỗi truy vấn Render Stylesheet của Tailwind. Hệ thống UI Frontend sử dụng `tokens.json` làm Single Source of Truth.
- **Quốc tế hóa (i18n) & Tránh rò rỉ bộ nhớ (Uploader Caching):** Toàn bộ nhãn tĩnh và cấu trúc tabs trên giao diện Design Tokens đã được đóng gói i18n chuẩn. Các hàm xử lý `wp.media` (Logo và Custom Font Upload) đã được cải tiến để lưu cache instance trực tiếp trên Alpine component state (`logoUploaderInstance`, `fontUploaderInstance`) thay vì tạo mới ở local scope mỗi lần click, giải quyết triệt để rủi ro rò rỉ bộ nhớ trình duyệt.

## 8. Kiến trúc Ska Theme Builder (Phase 4.2)
Chính thức loại bỏ FSE (Full Site Editing) để giải quyết xung đột mâu thuẫn "App vs Website Builder". Sử dụng cơ chế: **Gutenberg as a Component Engine** kết hợp **Smart Virtual Wrapper**.
- **Ska Theme Panel:** Quản lý danh sách Template (Header, Footer, Single...) qua trang Admin tự code bằng Alpine.js + Tailwind CSS. Dữ liệu áp dụng kiến trúc **Dual-Table**: `ska_data_sys_theme_templates` (lưu Trạng thái, Logic điều kiện, và ID Vị trí) và `ska_data_sys_organisms` (lưu HTML/JSON Design). TUYỆT ĐỐI không dùng Custom Post Type (CPT) để tránh rác `wp_posts`.
- **App Categorization (Virtual Folders):** Không tạo taxonomy. Các trang Theme Template (`ska_theme_builder`) được nhóm lại theo từng ứng dụng (LMS, CRM...) thông qua cấu trúc "Thư mục ảo" (dựa trên metadata của template), giúp giao diện UI gọn gàng và tránh rác Database.
- **Organism Categorization & Folder Management (Phase 1.0.4 / 1.0.5):** Tích hợp phân loại và quản lý thư mục ảo cho các Symbols/Organisms. Dữ liệu danh mục được lưu trực tiếp trên cột phẳng `category` của bảng phẳng `wp_ska_data_sys_organisms` (tránh wp_postmeta). Toàn bộ danh sách danh mục lưu trữ tại Option `ska_organism_categories` và quản lý tập trung ở Sidebar bên trái của Workspace Panel (CRUD category, real-time count badges, safe cascading delete chuyển symbol về Uncategorized). Physical JSON Cache và bộ nhớ JS Editor (`window.skaOrganismsCache`) tự động đồng bộ tức thì. Trong Gutenberg Editor, block dropdown selector tự động gom nhóm symbol thành các `<optgroup>` trực quan theo category.
- **Dedicated Page Routing:** Kết hợp với Ska App Router để định tuyến tới các Template (Detail, List, Create, Edit) dưới dạng một trang độc lập thông qua Dynamic URL (`/app-slug/{id}`) thay vì dùng SPA `x-show`.
- **Isolated Editor (Gutenberg Iframe):** Mở Iframe cách ly toàn màn hình để thiết kế các Organism phục vụ cho Theme Template. Không dính rác FSE. Mở rộng UI Inspector với `portal-visibility.js` nhưng bị khoanh vùng (Scope Isolation) để chỉ hiển thị trong môi trường này.
- **Smart Virtual Wrapper (Frontend):** Dùng hook `template_include` (Priority 99) để đánh chặn. Wrapper giả lập trang qua file `virtual-template.php`, đánh dấu trạng thái hiển thị của các khối như Header/Footer qua các hook mở rộng (`ska_theme_header`, `ska_theme_footer`) mà không bị ràng buộc bởi FSE. Hỗ trợ Fallback về Classic Theme hoàn hảo.
- **Active Organism CSS Injection:** Khi Virtual Wrapper kích hoạt Template, Tailwind JIT Compiler (lắng nghe ở `wp_head`) sẽ tự động được cung cấp danh sách ID của các Organism đang hoạt động trên trang thông qua Global State. Style_Manager sẽ parse HTML nội bộ của các Organism này để quét và Inject CSS kịp thời ra Frontend mà không bị sót CSS.

## 9. One-Click App Generator & Shadow Scratchpad (Phase 4.6)
Tích hợp khả năng tự động sinh mã (Code Generator) và xử lý Rich Text an toàn cho Frontend.
- **Atomic Portal Assets Generation (`Ska_Portal_Generator`)**: Tự động sinh `List View`, `Detail View` (Theme Templates) và các `Organisms` (như Row Data, Form Layout) hoàn chỉnh, tương thích 100% với Ska Theme Builder. Cung cấp API (thông qua hook `ska_design_generate_portal_assets`) để Data Engine gọi sau khi khởi tạo Schema Bảng phẳng. Các Asset sinh ra vẫn là các Block Atomic chuẩn, cho phép tuỳ biến tự do sau khi sinh.
  - *Nâng cấp Giao diện Detail View (2026-05-20):* Giao diện Detail View tự động sinh giờ đây mang phong cách Premium với Breadcrumb/Back link quay lại danh sách ở đầu trang, bố cục CSS Grid 2 cột tự động phân tách và gom nhóm các trường nhập liệu ngắn (Text, Number, Date, Select, Relation) đồng thời giữ nguyên các trường dài (Rich Text) ở dạng full-width, thanh tác vụ Form Actions (Hủy / Lưu Thay Đổi) chuyên nghiệp ở chân form, đi kèm style input/select bo tròn góc lớn (`rounded-xl`) và hiệu ứng focus tinh tế.
  - *Sửa lỗi Định tuyến và Giao diện hiển thị (2026-05-20):* Đồng bộ hóa biến `portal_slug` (URL Frontend) thay thế cho `table_slug` (Tên DB) trong toàn bộ cấu trúc điều kiện hiển thị (Display Conditions) và các đường liên kết Hủy bỏ/Trở về. Đồng thời cập nhật UI của Ska Theme Builder (bổ sung `specific_portal_list` và `specific_portal_detail` vào AlpineJS `ruleOptions`) để hỗ trợ nhập liệu và xử lý chính xác định tuyến Virtual Router trên Frontend mà không bị lỗi `404 Not Found`.
  - *Sửa lỗi Tương thích Data Fetcher (2026-05-20):* Sửa lỗi trắng List View do truyền sai tên bảng trong `sourceTable` của block `ska-builder/loop`. Auto Generator được cấu hình luôn sử dụng tên bảng đầy đủ (bao gồm tiền tố `wp_ska_data_`) thay vì slug rút gọn, đảm bảo vượt qua tường lửa bảo mật của `Data_Fetcher`.
  - *Thuật toán Cột Động & Bản vá Compiler (2026-05-21):* Phát triển hệ thống Smart Column Picker tự động đếm các trường dữ liệu phù hợp để sinh ra thuật toán CSS Grid động (VD: `grid-cols-[2fr_repeat(3,minmax(0,1fr))_40px]`). Đồng thời, vá lõi Tailwind JIT Compiler: bổ sung Regex nhận diện Arbitrary Values `grid-cols-[...]`, loại bỏ thuộc tính `display: grid;` ép buộc để tương thích với class `hidden`, và vá lỗi Escape Selector cho các ký tự đặc biệt như `(`, `)`, `,` sinh ra từ các hàm CSS (như `repeat`, `minmax`), đảm bảo JIT Compiler xuất ra mã CSS hoàn toàn hợp lệ. Bổ sung Auto-Flush Cache (export JSON) sau khi Generator chạy.
  - *Sửa lỗi cuộn kép (double scrollbar) frontend & cắt xén block appender (+) editor (2026-05-21):* Hủy bỏ hoàn toàn các lớp `h-screen overflow-hidden` trên container `<form>` và `overflow-y-auto` trên `<main>` trong hàm `build_form_layout()` của `Ska_Portal_Generator`. Thay vào đó sử dụng `min-h-screen` trên `<form>` và `flex-1 bg-slate-50` trên `<main>`. Thay đổi này cho phép trang cuộn tự nhiên theo trình duyệt (sử dụng sticky header hoạt động tối ưu) để triệt tiêu scrollbar kép ở frontend, đồng thời giải phóng giới hạn chiều cao giúp Gutenberg không còn bị cắt xén các outline và nút chèn block (`+` appender) ở chế độ Editor.
  - *Hỗ trợ Xóa Dòng trực tiếp trên List View (2026-05-22):* Thay đổi container ngoài cùng của Row Item từ thẻ `<a>` sang `<div>` để tránh lỗi lồng thẻ HTML (Nesting Tag Conflict). Bổ sung cột Hành động chứa nút Xóa (thẻ `button` với class `action-btn` và `@click.stop` để ngăn chặn nổi bọt sự kiện). Sử dụng sự kiện `@click` trên container ngoài cùng để chuyển hướng trang chi tiết nhưng loại trừ khi click trúng các phần tử con có class `.action-btn`. Inject đoạn JS helper `deleteRow` qua `wp_footer` để thực hiện gọi API DELETE, làm mờ dòng và ẩn dòng mượt mà.
- **Garbage Collection (Lắng nghe `ska_data_table_deleted`)**: Mọi Asset sinh ra được ghim lại với ID bảng dữ liệu. Khi bảng bị xoá, Design Engine tự động "dọn rác" (xoá Templates, Organisms) sạch sẽ để trả lại hệ thống nguyên trạng, không phình to Database.
- **Shadow Scratchpad Architecture**: 
  - Thay vì dùng Full Site Editor cồng kềnh cho thẻ nhập liệu `long_text` ở Frontend Portal, hệ thống dùng **Iframe cách ly (Shadow Scratchpad)** tải trình soạn thảo Gutenberg tiêu chuẩn của WordPress.
  - Mỗi khi click "Sửa văn bản", API (`/scratchpad/create`) tạo ra một Custom Post Type **tạm thời** (`ska_scratchpad`). Khi hoàn tất, nội dung (HTML) được trích xuất chuyển vào AlpineJS model ở Frontend, đồng thời CPT tạm thời này bị **tiêu huỷ ngay lập tức** (`/scratchpad/destroy`).
  - Đảm bảo Zero-Bloat (không phình to CPT `wp_posts` rác), chống xung đột Data Concurrency (mỗi Scratchpad chỉ tồn tại trong vòng đời của 1 phiên chỉnh sửa duy nhất qua ID phái sinh ngắn hạn).

## 10. Định hướng Tương lai: Lõi Design Engine 3 Lớp (Professional Design Engine)
Thay vì chắp vá Theme Options bằng cách hardcode class, kiến trúc đã được pivot sang 3 lớp:
1. **Lớp 1 (Tokens Registry):** Nguồn chuẩn sự thật (Single Source of Truth). Tách biệt hoàn toàn giá trị của Token (như primary, secondary, base spacing) và render ra CSS Variables ở `:root`. Hỗ trợ nhập liệu từ Markdown/JSON chuẩn.
2. **Lớp 2 (Semantic Base Styling):** Tiêm tự động (Global CSS Injection). Khi người dùng định nghĩa H1, JIT sinh sẵn `h1 { @apply ... }`. Người dùng không cần phải chọn class Tailwind thủ công cho các thẻ cơ bản.
3. **Lớp 3 (Visual Tailwind Browser & Hybrid UX):** Cung cấp giao diện Style Drawer (Popover) trực quan thay vì chỉ gõ tay (Legacy Text Input). 
   - Tích hợp **Theme Colors** động trực tiếp từ Data Pro.
   - Hỗ trợ **Fallback (Standard Colors)** nếu chưa định nghĩa Theme Colors.
   - Cơ chế Overwrite cho Presets và Toggle cho utility classes.
   - Tính năng **Custom Class Injection:** Cho phép gõ và add trực tiếp các class Arbitrary (như `h-48`) ngay từ thanh Search của Visual Browser, giảm thiểu thao tác đóng/mở panel liên tục.
