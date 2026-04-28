# MODULE: BLOCKS SYSTEM
> **Path:** `ska-builder-core/blocks/`

## 1. Nhiệm vụ (Responsibility)
Hệ thống Blocks (Gutenberg) cốt lõi của Ska Builder. Cung cấp các atomic block để xây dựng giao diện.

## 2. Blocks đã triển khai (Cập nhật 2026-03-26)

### A. Quy tắc chung (Global Rules)
- **Single Attribute Model (2026-03-26):** `tailwindClasses` là **Single Source of Truth** duy nhất. `className` luôn được set rỗng. `TailwindPanel` callback đơn giản: `(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })`.
- **useInnerBlocksProps (2026-03-26):** Các block có InnerBlocks (Container, List, List-Item) sử dụng `useInnerBlocksProps` thay vì `<InnerBlocks>` component. API này merge inner blocks trực tiếp vào wrapper, loại bỏ intermediate divs (`block-editor-inner-blocks`, `block-editor-block-list__layout`) gây hỏng positioning.
- **Inline Positioning (Editor, 2026-03-26):** Container block detect các positioning classes (absolute/relative/fixed/sticky, inset-*, top/right/bottom/left-*, z-*) và convert thành inline styles trong `useBlockProps`. Inline styles có highest CSS priority, không có stylesheet nào override được.
- **Flat DOM Principle:** Đối với các block cấu trúc, tuyệt đối KHÔNG thêm div bọc ngoài không cần thiết. `get_block_wrapper_attributes()` phải được áp dụng lên thẻ ROOT.
- **Robust Fallback:** Tất cả file `render.php` sử dụng logic: `! empty( $attributes['tailwindClasses'] ) ? $attributes['tailwindClasses'] : ( $attributes['className'] ?? '' )`.

### B. Danh sách Block

#### 1. Ska Text (`ska-builder/text`)
- **Attributes:** `content`, `tagName`, `tailwindClasses`, `customStyle`, `dynamic`, `logic`.
- **Render:** Áp dụng trực tiếp vào thẻ root (`p`, `h1`...).

#### 2. Ska Container (`ska-builder/container`)
- **Attributes:** `tagName`, `tailwindClasses`, `customStyle`, `logic`, `templateLock`.
- **Render:** Sử dụng `get_block_wrapper_attributes()` trên thẻ wrapper chính.
- **Editor (2026-03-26):** Sử dụng `useInnerBlocksProps` + `getPositionStyles()` helper để inject positioning inline styles. Đây là block đầu tiên và quan trọng nhất áp dụng pattern này.
- **Molecule Base (2026-04-16):** Trở thành Root Element cho hệ Ska Molecules. Hỗ trợ thuộc tính `templateLock` (như 'all') truyền vào `useInnerBlocksProps` để Khóa Xương Sống cấu trúc đối với các Variation phức tạp (Multi-Step Form, Tabs, Accordion) nhằm tránh gãy Layout khi người dùng thao tác. Thẻ `tagName` có thể linh hoạt chuyển hóa (vd: `form` cho Quiz/Wizard).

#### 3. Ska Image (`ska-builder/image`)
- **Attributes:** `url`, `alt`, `id`, `tailwindClasses`, `customStyle`, `dynamic`.
- **Isolation Fix (2026-03-21):** Di chuyển `get_block_wrapper_attributes()` từ lớp vỏ `div` vào thẳng thẻ `<img>`. Giúp cô lập hoàn toàn hiệu ứng hover/rounded lên ảnh.

#### 4. Ska Icon (`ska-builder/icon`)
- **Attributes:** `iconName`, `tailwindClasses`, `customStyle`.
- **Render (2026-03-27):** Áp dụng vào thẻ `<span>` với class `material-symbols-outlined`. Tích hợp thư viện 4207 icons (Modal Search UI + Quick Grid).

#### 5. Ska Button (`ska-builder/button`)
- **Attributes:** `text`, `url`, `tagName`, `tailwindClasses`, `customStyle`, `dynamic`, `logic`.
- **Action & Icon (2026-04-27):** Rút gọn `actionType` còn `link`, `submit`, và `logic_api` (đã hợp nhất tính năng gọi Popup vào Logic Workflow thông qua Client Response Node). Nhóm Icon vẫn giữ nguyên.
- **Alpine.js Local Event (2026-04-28):** Khi người dùng muốn kích hoạt sự kiện cục bộ thông qua Alpine.store (ví dụ: `@click="$store.appState.openModal()"`), nút bấm BẮT BUỘC phải được gắn thêm thuộc tính `x-data` để Alpine v3 nhận diện là một component và parse directives.
- **Render:** Ép kiểu Semantic Tag (`<a>` hoặc `<button>`) phụ thuộc thời gian thực vào biến `actionType`. Hỗ trợ tự động tiêm class `ska-action-[workflow_id]` nếu nút sử dụng hành động Logic Workflow, mở đường kết nối ngầm với Logic Engine Event Bus.
- **Isolation Fix (2026-03-21):** Di chuyển `get_block_wrapper_attributes()` từ lớp vỏ `div` vào thẻ `<a>` hoặc `<button>`. Xóa bỏ lỗi "Red Box" khi hover tràn khung.
- **CSS Specificity (2026-03-27):** Gỡ bỏ class `wp-element-button` mang nợ CSS mặc định của theme WP (như nền đen). Tự tạo file `index.css` áp dụng lệnh `a.ska-button-block { text-decoration: none; }` có độ ưu tiên `0,1,1` để ép tắt dòng gạch chân mặc định của WP, nhưng khéo léo dùng bộ chọn để cho phép Tailwind override hoàn hảo các class dạng `underline` và `hover:underline`.

#### 6. Ska Video (`ska-builder/video`)
- **Attributes:** `url`, `videoType`, `aspectRatio`, `tailwindClasses`, `customStyle`, `dynamic`, `logic`.
- **Render:** Tailwind classes được áp dụng vào video wrapper để xử lý aspect-ratio và corner clipping (`isolate`).
- **2026-03-22 Fix:** Khắc phục mismatch attribute `videoUrl`/`url` trong `render.php` giúp URL load đúng nội dung và không bật video placeholder khi có nguồn phát.

#### 7. Ska List (`ska-builder/list`) & Ska List Item (`ska-builder/list-item`)
- **Attributes:** `listType` (UL/OL), `tailwindClasses`, `customStyle`.
- **Render:** Flat DOM, hỗ trợ đầy đủ `flex`/`grid` layout trên thẻ `<ul>`/`<li>`.
- **Editor (2026-03-26):** Cả hai đều sử dụng `useInnerBlocksProps` để loại bỏ intermediate wrapper divs.

#### 8. Ska Form (`ska-builder/form`)
- **Attributes:** `formId`, `tailwindClasses`, `customStyle`, `actionType`...
- **Render:** Flat DOM thẻ `<form>`, sử dụng `InnerBlocks.Content` trong `save()` để render khối con, đảm bảo giao diện Frontend/Editor đồng nhất (2026-04-05).
- **Trạng thái:** Không bọc form mặc định, tuân thủ nguyên lý `ALLOW_BLOCKS` cho phép nhúng Container làm layout grid.
- **Pivot (2026-04-14):** Form được giao nhiệm vụ tự động hóa thu thập dữ liệu (payload theo thẻ `name`) và bắn AJAX về Engine thay vì ép người dùng Nocode viết mã JS bằng AlpineJS `x-on:click`.
- **Alpine Form Integration (2026-04-23):** Hoàn thiện liên kết hai chiều giữa Cấu trúc Khối (HTML) và Logic Backend (Ska Logic Engine) thông qua Controller `skaForm('workflow_id')`. Các trường State UI (`fields.*`, `status.*`) được chuẩn hóa toàn bộ, cho phép Form tiếp nhận, validation, và submit dữ liệu siêu mượt mà không cần code JS thêm. Script Dependency của `alpine.min.js` cũng được fix đảm bảo nó luôn load sau `ska-frontend.js` để Controller được sẵn sàng trước khi DOM quét Alpine State.

#### 9. Ska Input (`ska-builder/input`) & Ska Select (`ska-builder/select`)
- **Attributes:** `fieldName`, `inputType`, `placeholder`, `isRequired`, `tailwindClasses`, `customStyle`.
- **Styling Architecture (2026-04-05):** Mọi viền, nền, outline của WP Admin bị gỡ bỏ ở Backend. Hệ thống áp dụng Global Tailwind Preflight Form Reset (`border-color: #e5e7eb;` và `appearance: none;`) trực tiếp thông qua JIT Code (Class `class-tailwind-config.php`), đảm bảo UI 100% tàng hình và giống hệt như Front-End nếu không có Tailwind classes nào.
- **Select Dynamic Binding (2026-04-23):** Tích hợp React Inspector UI cho block Select giúp thay thế việc gõ thủ công cú pháp Mustache. Dropdown tự động hiển thị các cột kiểu `select/radio/checkbox` có khai báo thuộc tính `options` từ `ska_data_dictionary` của `Ska Data Pro` (gỡ bỏ check `__table_info` nghiêm ngặt). Editor bảo vệ UX bằng cách loại bỏ các trường Số/Chữ không thể sinh danh sách tuỳ chọn.

#### 10. Ska Symbols & Molecules (Phase 4 Foundation)
- **Ska Symbols (Reusable Organisms):** Khối `ska-builder/organism-ref` hoạt động như một Ghost Block (Khối tham chiếu). Nó chỉ chứa duy nhất một thuộc tính `organismId`. Tại Editor, cơ chế Zero-Query được áp dụng cực trị bằng cách load trực tiếp `window.skaOrganismsCache` (qua `wp_localize_script`) để truyền dữ liệu tĩnh tức thời (0ms delay) vào bảng Inspector Controls. Quá trình render thực tế ở Editor dùng `ServerSideRender` (Đã fix 2026-04-21: Cập nhật `echo` output buffering cho `render.php` do behavior của WP 6.1+). Quá trình render ở Frontend bốc cấu trúc gốc từ bảng phẳng `ska_data_sys_organisms` thay vì nhân bản HTML phình to. Đảm bảo Single Source of Truth tuyệt đối. Cơ chế **Data Injection** (2026-04-22) tự động tiêm Symbol mới lưu vào `window.skaOrganismsCache` bằng Javascript giúp tính năng "Phân rã (Detach)" hoặc hiển thị Tên Symbol ở Dropdown hoạt động ngay lập tức (Real-time SPA) mà không cần tải lại trang.
- **Ska Molecules (UI Components):** Cấu trúc Khóa Xương Sống (`templateLock`). Áp dụng kết hợp Alpine.js nhằm tạo ra các UI tĩnh có tương tác mượt mà như:
  - **Tabs:** Quản lý state hiển thị qua `x-data="{ activeTab: 1 }"`.
  - **Accordion:** Quản lý collapse bằng chiều cao biến thiên (`max-height`).
  - **Logic Modal:** Modal Popup độc lập xử lý state bật / tắt với hiệu ứng `x-transition`.

#### 11. Ska Loop (`ska-builder/loop`)
- **Role:** Vòng lặp hiển thị dữ liệu động (Data Loop) với Zero N+1 Queries.
- **Backend Architecture (2026-04-22):** Sử dụng cơ chế Bulk Loading thông qua `Organisms_API::get_bulk_html` để load hàng loạt template HTML của Organism. Áp dụng Hydration Engine tốc độ cao bằng `preg_replace_callback` để đắp Data (Flat Tables) vào cặp thẻ Mustache `{{key}}`. Không được query SQL bên trong vòng lặp.
- **Condition Matching:** Sử dụng `SkaLogicEngine::evaluate` (SkaFX) để quét và quyết định Slot hiển thị. Hỗ trợ biến hệ thống có tiền tố `$` như `$index`, `$first`, `$last` thông qua bộ từ vựng SkaFX Lexer đã cập nhật.

## 3. Cấu trúc thư mục
- `init.php`: Đăng ký các block.
- `[block-name]/`: Thư mục chứa code của block.
  - `block.json`: Metadata (Bật `supports.className: true` để đồng bộ chuẩn Gutenberg).
  - `index.js`: Editor logic (Dùng `tailwindClasses` làm primary, Sync sang `className`).
  - `render.php`: Frontend logic (Hỗ trợ "Non-empty Fallback" & Flat DOM).

## 4. Ghi chú phát triển quan trọng
- **Inner Element Isolation:** Với các block atomic (Button, Image), luôn ưu tiên render class Tailwind vào thẻ nội dung bên trong thay vì lớp vỏ Gutenberg bên ngoài.
- **Build Sync:** Bắt buộc chạy `npm run sync` sau mỗi lần sửa logic PHP trong `src/` để cập nhật file sang `build/`.
- **TailwindPanel:** Luôn truyền `tailwindClasses` vào component `TailwindPanel`. Callback chỉ nhận 1 param (allClasses).
- **useInnerBlocksProps Pattern (2026-03-26):** Khi block cần InnerBlocks, sử dụng `useInnerBlocksProps(blockProps, { allowedBlocks, ... })` thay vì `<InnerBlocks>`. Pattern: `<Tag {...innerBlocksProps} />` thay vì `<Tag {...blockProps}><InnerBlocks .../></Tag>`.
