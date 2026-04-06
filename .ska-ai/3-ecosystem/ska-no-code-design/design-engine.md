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
- **CSS Specificity (Frontend):** Áp dụng cơ chế **Class Doubling** (`.class.class`) kết hợp tiền tố `html body.ska-builder` để thắng CSS Theme mà không dùng `!important`.
- **CSS Block Reset Specificity (2026-04-06):** Ứng dụng pseudo-class `:where()` vào các logic Mâm Xôi (Reset). Ví dụ: `button:not(:where(.components-button))` nhằm hạ điểm Specificity từ 33 xuống 23. Từ đó trải thảm cho các class của tiện ích Tailwind (đang mang điểm 32) tuỳ ý ghi đè mà không lo bị chặn đứng.
- **Form Preflight & Native Reset (2026-04-06):** Thiết lập Reset cấp độ Structural dài dòng (VD: `.editor-styles-wrapper .block-editor-block-list__block.wp-block-ska-builder-input`) mang Specificity cao (30) nhưng KHÔNG có `!important` để dọn sạch CSS của WP Admin. Đồng bộ viền mặc định `border-color: #e5e7eb` với Frontend để xoá nhòa khoảng cách Parity.
- **Editor Specificity Fix (2026-03-29):** Gỡ bỏ cờ `margin: 0 !important;` quá khích khỏi `ska-editor-helper.js` trên `.wp-block`, trả lại quyền điều khiển `margin` (mt-*, mb-*) cho cấu hình JIT Reset (`[class*='wp-block-ska-builder']`) và lệnh Tailwind CDN.
- **Fractional Resolving (2026-03-29):** Bổ sung Regex tính toán thập phân (`1/2` = `50%`, `1/3` = `33.333333%`) cho JIT (`w-*`, `h-*`), đảm bảo tính nhất quán với thiết lập CDN trên Editor giúp sửa lỗi fallback `w-full`.
- **Atomic Button Reset (2026-03-29):** Ấn định `.ska-button-block` luôn hiển thị kiểu `inline-flex` ở cấp Design Engine Reset thay cho thẻ `<a>/<button>` `inline` khô cứng, giúp hệ thống margin/spacing hoạt động chính xác.
- **Locale CSS Safety:** Toàn bộ CSS output cho spacing/dimensions được xử lý qua `number_format` với dấu chấm thập phân (`.`) để đảm bảo tính hợp lệ trên mọi môi trường server.

## 2. Luồng hoạt động (Architecture Flow)
1. **Input:** User nhập class `p-4 text-red-500 shadow-xl` vào Ska Block.
2. **Process:**
   - `Style_Manager::scan_post_classes()` quét toàn bộ `post_content`, ưu tiên trích xuất từ thuộc tính `tailwindClasses` (mới), sau đó là `className` (fallback), `customStyle` và các string attributes khác.
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
- **Link Reset (2026-03-28):** `a { text-decoration: none; color: inherit; }` trong scope Ska blocks để chống WordPress default underline.
- **Negative Prefix (2026-03-28):** `compile_classes()` tự động strip dấu `-` đầu, gọi `resolve_class()`, rồi negate CSS values qua regex.
- **Performance:** Frontend sử dụng CSS nén từ local compiler. CDN chỉ là fallback.
- **Editor Context (2026-03-26):** Tailwind CDN config set qua `script.onload` callback: `doc.defaultView.tailwind.config = { important: true, corePlugins: { preflight: false } }`. Config PHẢI set SAU CDN load vì CDN ghi đè `window.tailwind`.
- **Editor CSS Overrides (2026-03-26):** `ska-editor-helper.js` chứa: button border reset (`:not(.border)`), container border fallback (`border-style: solid`), positioning overrides trực tiếp trên element (`.wp-block-ska-builder-container.absolute`), child block width reset.
- **Editor Clean Specificity:** Dùng `:not([class*="max-w-"]):not(.container)` để tự động thoát khỏi giới hạn 600px của WordPress.
- **Hybrid Source Architecture:** Sử dụng PHP Core để pre-generate CSS cho brand colors.
- **Inline Style Support:** Thuộc tính `customStyle` cho phép bảo toàn và hiển thị các style "cứng" (background-image).
- **Locale Independence:** Sử dụng `number_format( $val, 3, '.', '' )` trong PHP.
- **Style Scanning:** `Style_Manager` ưu tiên quét từ `tailwindClasses`, sau đó là `className`, `customStyle` và nội dung HTML.
- **🔧 Planned Refactor:** Tách file compiler (>500 dòng) thành: `compiler` + `config` + `color-registry`.
