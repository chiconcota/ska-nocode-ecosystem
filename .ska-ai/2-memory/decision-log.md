## 2026-04-06 (Post-MVP): Chuyển đổi Kiến trúc JIT Compiler (REST JIT)
- **Problem:** Môi trường Editor đang phụ thuộc vào `Tailwind CDN (V3)` lạc hậu, gây ra các khó khăn lớn khi phải thủ công cấu hình Polyfill (CSS gốc) cho các tính năng của `Tailwind V4` (như `:has`, `indeterminate`). Hơn nữa việc chạy song song 2 hệ thống dịch CSS riêng biệt (CDN cho Backend, PHP cho Frontend) rủi ro sai lệch Parity rất cao.
- **Decision:** Đưa phương án sử dụng `REST JIT / AJAX Compiler` vào lộ trình đại tu sau khi dự án thoát khỏi giai đoạn MVP. Phương án này sẽ: Định kỳ quét các block properties qua `wp.data.subscribe`, truyền list Tailwind Class lên Server qua REST API, đưa vào `Tailwind_Compiler` (PHP) và tiêm trực tiếp CSS trả về ngược lại Iframe Editor. 
- **Reason:** Khai tử CDN triệt để. Tạo thành Single Source of Truth cho CSS Editor và Frontend. Phương án này KHÔNG làm gián đoạn luồng làm việc thực thi MVP hiện tại nên được trì hoãn một cách chiến lược.

## 2026-04-06 - Đại tu JIT Compiler: V4 Pseudo-classes & Dynamic Group States
- **Problem:** Khi Import các cụm Checkbox/Radio có thiết kế Custom phức tạp của Tailwind V4, người dùng gặp hiện tượng toàn bộ Textbox biến thành màu xanh lè (Kể cả khi KHÔNG TÍCH CHỌN) và Icon SVG không hiện lên. Nguyên nhân do JIT Compiler của Ska mất khả năng phân tích các từ khóa `indeterminate` (Nó thả rông class màu sắc vô điều kiện) và hoàn toàn bị mù trước cú pháp Group State đời mới `group-has-checked` (Nó vứt bỏ class làm Icon mất hiệu ứng).
- **Decision:** Tiến hành đại tu bộ khớp nối (Regex/Parser) của `class-tailwind-compiler.php`. Bổ sung một loạt Pseudo-class vắng mặt (`indeterminate`, `required`, `autofill`, v.v.) vào bộ lọc tiêu chuẩn. Nâng cấp các đoạn IF kiểm tra cứng nhắc cũ (`group-hover`, `peer-checked`) thành hệ thống Parse động bằng `strpos()`, hỗ trợ dịch thuật tự động bất kỳ trạng thái nào dạng `group-has-[state]` thành `.group:has(:[state]) `. Đồng thời, hỗ trợ cả `has-[state]` (như `has-checked:bg-blue`).
- **Reason:** Đảm bảo Design Engine theo kịp tốc độ tiến hóa điên rồ của Tailwind V4 (Sử dụng ngụy lớp `:has` để làm Custom Pseudo-elements thay vì dùng CSS hack truyền thống), trả lại quyền năng Pixel-Perfect thực thụ cho các thẻ `<input>` được convert theo chuẩn Atomic của Ska.

## 2026-04-06 - Đại tu JIT Compiler phần 2: Tương thích Component Phức Tạp (Flowbite)
- **Problem:** Khi triển khai các UI phức tạp như Toggle Slider của Flowbite, JIT Compiler thất bại trong việc thiết kế Pseudo Element vì thiếu hàng loạt các class nâng cao: `sr-only`, Logical Properties (`start-*`, `end-*`, `ms-*`, `pe-*`), và Arbitrary pseudo-content (`after:content-['']`).
- **Decision:** Bổ sung regex hỗ trợ nhận diện toàn bộ họ `start/end` thay vì bị giới hạn ở `left/right/top/bottom`, đồng nghĩa với việc mở khóa thiết kế RTL/LTR linh hoạt. Thêm hỗ trợ chuyển mã `content-[val]` thành `content: val;`. Tích hợp `sr-only` và `not-sr-only`. 
- **Reason:** Đảm bảo Ska JIT Compiler mạnh mẽ tương đương hệ thống CDN, vượt ngoài ranh giới của Utility cơ bản, sẵn sàng biên dịch các cấu trúc UI phức tạp nhất giới Web Design hiện tại.

## 2026-04-06 - Giải cứu Tailwind V4 Editor Parity với Proxy Mutation & Polyfills
- **Problem:** (1) Thư viện `Tailwind CDN v3` khi chạy bên trong Iframe của Gutenberg Editor luôn bỏ qua lệnh cấu hình `important: true` nếu script khởi tạo bị dính Race Condition. Kết quả là mọi class liên quan đến Form Reset (border, outline) bị Gutenberg đè chết; (2) Frontend đang dùng cú pháp layout Form chuẩn Tailwind v4 (VD: `-outline-offset-1`, `focus:-outline-offset-2`), nhưng Editor rỗng CDN v3 hoàn toàn mù tịt về các class âm này, khiến hiệu ứng thụt viền chớp sáng không hiện lên.
- **Decision:** Thay vì tạo đuôi nhúng config dễ crash, áp dụng mô hình **Proxy Mutation**: Đợi CDN load xong (`script.onload`), sau đó nhảy trực tiếp vào bắt con Proxy `doc.defaultView.tailwind.config` ép biến `important = true`. Tiếp theo, cài đặt (Polyfill) cứng mã CSS cho 4 class Layout âm của V4 trực tiếp vào lớp Reset `#ska-editor-fixes`.
- **Reason:** Ép cấu hình Runtime Mutation sẽ kích hoạt chức năng theo dõi Object Proxy bên trong bộ Core CDN của Tailwind, buộc nó tự Re-build toàn bộ AST và xuất mã CSS kèm cờ `!important` chuẩn xác, kết thúc vĩnh viễn cuộc chiến Specificity Editor/Frontend. Việc Polyfill thủ công class Layout thay vì viết Tailwind Plugin JS JS giúp ngăn chặn hiện tượng treo (crash) Editor của CDN.

## 2026-04-06 - Sửa lỗi hiệu ứng Viền (Outline) tàng hình trong Gutenberg Editor
## 2026-04-06 - Nâng cấp Ska Bridge Import giữ nguyên vẹn mã thẻ SVG
- **Problem:** Công cụ `html2tailwind` tự động ánh xạ mọi thẻ HTML không xác định thành `Ska Container`. Điều này dẫn đến sự cố nghiêm trọng khi Import các Form xịn chứa thẻ Icon `<svg>` và `<path>`, biến chúng thành Container gây rách nát giao diện vì Gutenberg không hiểu thẻ `<path>`.
- **Decision:** Bổ sung ngoại lệ trực tiếp vào `html-to-blocks.js` chặn Parser không được mổ bụng thẻ `SVG` mà phải ném nguyên cả chuỗi `outerHTML` vào khối `core/html` (Custom HTML của Gutenberg).
- **Reason:** Đảm bảo SVG được hiển thị và Render an toàn tuyệt đối mà không bị các cơ chế Atomic Blocks của Ska can thiệp làm hỏng đồ hoạ bên trong.

## 2026-04-06 - Nâng cấp JIT Compiler hỗ trợ Outline Utilities và Focus-within
- **Problem:** Khi Import Template tĩnh từ Tailwind UI (Ví dụ Form inputs), Tailwind sử dụng các class như `outline-1`, `-outline-offset-1`, `focus-within:outline-2` để vẽ khung viền (thay vì dùng `ring`). Tuy nhiên JIT Compiler của Ska chưa được dạy bộ từ vựng Outline và cũng mất luôn mảng Pseudo-class `focus-within`, `focus-visible` nên viền không thể hiển thị.
- **Decision:** Cập nhật `class-tailwind-compiler.php`, bổ sung Regex parsing cho toàn bộ hệ sinh thái Outline (`outline-none`, `outline-width`, `outline-offset`, `-outline-offset`, `outline-color`) và thêm `focus-within`, `focus-visible` vào danh sách móc nối Pseudo-class.
- **Reason:** Đảm bảo khả năng tương thích 100% với các component form đời mới nhất của Tailwind UI (Tailwind v3+ sử dụng Outline thay vì Ring cho Form Elements mặc định).

## 2026-04-06 - Sửa lỗi thẻ Import HTML bị đánh chặn trong Ska Form
- **Problem:** Công cụ `html2tailwind` (Ska Bridge Import) không thể kéo thả vào bên trong thẻ `Ska Form`. Nguyên nhân do khối Ska Form giới hạn `allowedBlocks` nhằm ngăn rác, nhưng lại quên mất việc cho phép thẻ Import chạy. Điều này cản trở Use-case: Người dùng dán toàn bộ mã HTML tĩnh của một Form chà bá vào trong Container để Converter chạy thẳng một lần.
- **Decision:** Bổ sung `ska-builder/html2tailwind` vào danh sách `ALLOWED_BLOCKS` của file `src/ska-form/index.js`.
- **Reason:** Bridge Import là thẻ trung gian (bùng nổ rồi tự sát nhường chổ cho Atomic Blocks), việc đứng hợp pháp trong Form Container là bắt buộc để hỗ trợ luồng No-Code Builder dán HTML nguyên khối.

## 2026-04-06 - Nâng cấp Ska Button hỗ trợ Multi-action Form
- **Problem:** Khối Button cũ có mục "Action Type: Submit Form" nhưng lại bị sót 2 phần tử sống còn cho form là thuộc tính `name` và `value`. Điều này cản trở việc tạo Form nhiều luồng action (Ví dụ: Nút "Save Draft" và "Publish" cùng đăng một nội dung, Backend không định vị được hành vi).
- **Decision:** Bổ sung 2 tham số `fieldName` và `fieldValue` vào thẻ Ska Button. Viết code Editor Logic (`index.js`) để 2 dòng cấu hình này chỉ xuất hiện khi Action Type là `Submit Form`. Cập nhật `render.php` đẩy thẳng ra HTML Output.
- **Reason:** Đảm bảo khả năng scale logic cho giai đoạn kết nối Ska Logic Engine (Phase 3). Chuẩn hóa HTML Semantic Validation của w3c.

## 2026-04-06 - Giải quyết triệt để Xung đột Specificity (Tailwind vs Reset)
- **Problem:** Khi người dùng thêm class bg-gray, border-blue vào các block Input, màu sắc không hiển thị ở Frontend. Nguyên nhân là các luật Reset Form của Ska (như `background-color: transparent`, `border-width: 0`) có Specificity quá cao (4 class, 3 thẻ = 0,0,4,3) đè chết Specificity của Tailwind Utility Class (3 class, 2 thẻ = 0,0,3,2).
- **Decision:** Nâng cấp hàm `$build_rule` trong file `class-tailwind-config.php`, bọc toàn bộ thẻ đích HTML vào selector CSS `:where()`.
- **Reason:** `:where()` có độ ưu tiên Specificity luôn bằng 0. Khi bọc toàn bộ lõi Reset vào `:where()`, Specificity của Reset rơi thẳng đứng xuống còn `0,0,1,2`. Nhờ vậy, mọi Class Tailwind do người dùng định nghĩa ở Editor (`0,0,3,2`) sẽ luôn luôn thắng thế tuyệt đối và đè được Form Reset một cách mượt mà.

## 2026-04-06 - Đại tu Card Option 2: Ska Choice Component (Thay vì Ska Select)
- **Decision:** Đổi định hướng thiết kế Block `ska-builder/select`. Biến nó từ 1 khối Dropdown tĩnh thành 1 khối xử lý "Danh sách Tùy chọn" tĩnh đa giao diện (Dropdown, Multi-select, Radio Group, Checkbox Group).
- **Architecture:** 
  - Bổ sung `isMultiple` (boolean) và `displayStyle` (dropdown | radio | checkbox).
  - Tự động gán mảng `[]` vào thuộc tính Form Name (name) nếu bật tính năng Chọn Multiple.
  - Phân nhánh Frontend PHP (`render.php`) xuất HTML dạng `<select multiple>` hoặc bộ `<label><input/></label>` tương ứng mà không làm vỡ các thiết kế trước đó.
- **Reason:** Việc nặn một cụm Checkbox hay Radio dọc dài bằng Atomic Blocks khiến UX của người dùng Build-Form bị quá tải. Thiết kế mới đem lại cả sự tự do Atomic (với thẻ `ska-builder/input`) và sự tiện lợi tuyệt đối (chỉ cần chép Text List) với thẻ `ska-builder/select` cải tiến. Phù hợp trọn vẹn với Model Checkbox/Multi-select của *Ska Data Pro*.

## 2026-04-06 - Nâng cấp Kiến trúc Atomic cho Form Items (Ska Input)
- **Decision:** Mở rộng quyền lực cho khối `ska-builder/input` thành thẻ gốc đa năng thay vì tạo block `ska-checkbox` mới. Bổ sung các chuẩn: `checkbox`, `radio`, `date`, `time`, `file`.
- **Feature:** Thêm thuộc tính `fieldValue` (Static Value) vào Inspector Controls (dùng làm default value cho Checkbox / Radio).
- **Reason:** Đảm bảo triết lý Atomic Design và Clean Slate. Tránh hardcode cấu trúc `<label><input/></label>` làm mất tự do dàn layout của người dùng (như Grid / Flex). Thay vào đó, người dùng tự kết hợp `Ska Input` và `Ska Text` thành nhóm bằng cách bỏ vào trong `Ska Container`.
- **Bridge:** Cập nhật công cụ `html-to-blocks.js` để tự động parse đủ 9 loại input type và sao chép luôn giá trị `value="..."` từ mã HTML dán vào.

## 2026-04-06 - Hoàn thiện Clean Slate: Form Elements Font Override
- **Problem:** Tùy chọn thả xuống (`<option>`) và bản thân thẻ `<select>` hay `<input>` vẫn bị hiển thị font mặc định của trình duyệt (thường là Arial hoặc Times New Roman) thay vì font chuẩn của Tailwind do thiếu thuộc tính kế thừa.
- **Decision:** Bổ sung các luật reset font (`font-family: inherit`, `font-size: 100%`, `color: inherit`, `line-height: inherit`) vào nhóm Form Resets (`input`, `select`, `textarea`, `option`, `optgroup`) trong hàm phân giải CSS `get_core_reset_css` (file `class-tailwind-config.php`).
- **Reason:** Đảm bảo Tailwind Preflight Parity tuyệt đối, ép mọi thuộc tính chữ của Form Element phải chảy theo quy luật Font mẹ (thường là Tailwind `ui-sans-serif, system-ui`).

## 2026-04-06 - Nâng cấp UI/UX Options List (Ska Select Block)
- **Decision:** Thay thế ô nhập liệu `TextareaControl` dạng thô bằng một Custom Component trực quan (`OptionsBuilderControl`) cho phép người dùng Thêm/Sửa/Xóa từng tùy chọn dạng Cặp `Label - Value` cho thẻ `<select>` ngay bên trong mục Inspector. 
- **Reason:** Tăng trải nghiệm kéo thả chuyên nghiệp (No-code Ux) lên ngang tầm Elementor/Webflow. Lưu ý vẫn duy trì cơ chế "Single Source of Truth" là biến chuỗi String tĩnh `optionsText` ở Database để đảm bảo tương thích ngược 100% với form cũ, component chỉ làm cầu nối giao diện.

## 2026-04-06 - Giải cứu CSS Specificity: Ska Select Width & HTML2Tailwind Form Mappings
- **Decision (Editor Helper Fix):** Bổ sung loại trừ nhóm class quy định chiều rộng (`:not([class*="w-"])`) vào đoạn hack CSS của `ska-editor-helper.js` đối với thẻ `.wp-block`.
- **Reason:** Trước đây, Editor tiêm lệnh `width: auto !important` vào mọi khối con, đè bẹp hoàn toàn class `w-full` của Ska Select. Cập nhật này sẽ tha cho các class nhóm `w-*` (như `w-full`, `w-1/2`), giúp thẻ `<select>` khôi phục khả năng full-width 100% trong Block Editor.
- **Decision (HTML2Tailwind Fix):** Tích hợp thêm các Thẻ HTML `<form>`, `<input>`, `<select>`, và `<textarea>` vào cỗ máy Parser `html-to-blocks.js` của Conversion Bridge.
- **Reason:** Khắc phục tình trạng khi người dùng paste code HTML chứa Form (Login / Register...) thì Form bị xóa sổ hoàn toàn. Từ nay `HTML2Tailwind` đã ánh xạ chuẩn xác sang các atomic blocks `ska-builder/form`, `ska-builder/input` và `ska-builder/select`. Hỗ trợ bóc tách luôn Option List `Value:Label` cho Select.

## 2026-04-06 - Giải cứu CSS Specificity: Button Reset Bug (Frontend)
- **Problem:** Khối Button (nút `<button>`) trên Frontend không nhận diện (hoặc bị ghi đè) các class Tailwind như Padding (`px-4`), Background (`bg-blue-500`), Margin... Mặc dù class CSS của tiện ích JIT Compiler sinh ra đầy đủ (`.bg-blue-500.bg-blue-500`) với điểm specificity là 32.
- **Root Cause:** Ở bản cập nhật trước, việc tái kích hoạt Button Reset (Xóa viền đen, xóa xám) của WordPress đã dùng selector `button:not(.components-button)`. Bản thân selector `:not(.components-button)` gia tăng +10 điểm specificity, đẩy điểm của toàn bộ luật Reset Reset lên 33. Kết quả: Điểm 33 của thiết lập Reset "Mâm xôi" đánh bại khối JIT Tailwind (32), xóa sổ màu nền và đệm viền.
- **Decision (Fix):** Bọc selector `.components-button` bên trong ngụy lớp kháng can thiệp `:where()`. Selector mới: `button:not(:where(.components-button))`. 
- **Reason:** `:where()` có điểm specificity bằng 0. Điểm của bộ Button Reset rớt từ 33 xuống chỉ còn 23. Lập tức trả quyền trượng cho JIT Tailwind Utilities (32) ghi đè tuyệt đối mà không cần dùng vũ lực `!important`. Fix triệt để bug mất Front-end Button CSS.

## 2026-04-05 - Kiên Định Kiến Trúc Mâm Xôi (Clean Slate) & Tailwind Preflight Parity cho Form

## 2026-04-04 - Đại Tu Kiến Trúc Frontend JS (The Great Refactor) & Packaging Ska Data Pro
- **Decision (Architecture):** Tách file `admin-datagrid.js` monolithic (>1200 dòng) thành kiến trúc ES6 Modules đặt tại `assets/js/src/`. Sử dụng `Vite` làm công cụ bundler (tạo ra `admin-datagrid.bundle.js`).
- **Reason:** Cải thiện khả năng bảo trì. Gỡ khối lượng logic rác ra khỏi Global Window Interface. Tuân thủ nguyên tắc Modular hóa Enterprise-Level. Dung lượng build mới cực kỳ tối ưu: 18.82 kB (gzip: 5.22 kB).
- **Decision (Pattern):** Áp dụng **Strategy Pattern** cho Cell Engine qua `CellRegistry`. Các Type Input khác nhau (Boolean, Media, Select, Text) tách biệt thành các Class xử lý UI/API riêng biệt kế thừa qua `BaseCell`.
- **Reason:** Đảm bảo triết lý Open-Closed Principle (OCP). Tương lai khi làm các Type như Màu Sắc, Rich Text, File Upload, chỉ cần làm một `abcCell.js` rồi cắm vào Registry mà không phải đụng tới Event Loop lõi.
- **Decision (Packaging):** Xây dựng hệ thống Đóng Gói qua kịch bản `build-zip.js` (dùng `archiver`). Cài đặt `npm run build:zip` tạo gói `ska-data-pro.zip` để sẵn sàng cho khách tải xuống.
- **Status:** Kế hoạch MVP của `Ska Data Pro` (Core Engine) chính thức hoàn thành và đóng gói thành công.
- **Decision (Hotfixes):** Cấu trúc lại giao diện Rollup Cascading (nâng cấp sang API Fetch Promise của ES6 thay vì jQuery Ajax). Áp dụng tính năng tự động Anti-Caching bằng Timestamp trên URL Output. Mọi nỗ lực nhắm vào UX tuyệt đối cho người dùng!

## 2026-04-03 - Nâng cấp Heuristic Filter & Rollup Bugfix
- **Decision (Data Filter):** Tích hợp bộ lọc Heuristic `NOT LIKE` trực tiếp vào SQL Query của DataGrid khi truy xuất Meta Keys từ `wp_postmeta` (loại bỏ `_wp_%`, `_edit_%`, `_oembed_%`, `_pingme`, `_encloseme`) và `wp_usermeta` (loại bỏ `session_%`, `closedpostboxes_%`, `metaboxhidden_%`, `wp_dashboard_%`, `nav_menu_%`).
- **Reason:** Cứu vãn UI trải nghiệm (UX) khỏi bãi rác khổng lồ (hàng trăm meta key hệ thống ngầm) do WordPress wp_core tự đẻ ra, chỉ giữ lại những meta data thực sự hữu ích (ACF, SCF, WooCommerce `_price`, `_sku`, `_thumbnail_id`).
- **Decision (Frontend Behavior):** Chuyển đổi trạng thái xử lý sau khi lưu (Save Reference) của Cột Relation từ việc chỉ Replace DOM hiện tại sang Reload trang toàn cục (`window.location.reload()`).
- **Reason:** Đảm bảo triệt tiêu lỗi Stale Computed Data (Rollup không update sau khi nối khóa ngoại) cực kỳ kinh điển trong Flat Table Model. Máy bơm PHP sẽ tự động Refresh mọi cột công thức phụ thuộc tức thời.

## 2026-04-03 - Nâng cấp Cỗ máy Rollup (Lookup Virtualization) & Xử lý Async Race Condition
- **Decision (Architecture):** Xây dựng hệ thống giải quyết tham chiếu chéo (Rollup) hoàn toàn VIRTUAL. Cột Rollup lưu trữ dưới DB là `NULL` để tránh dư thừa (No Data Redundancy). Tại điểm Fetcher, sử dụng thuật toán Gom mảng IDs (Batching) để truy vấn bảng đích thông qua 1 câu `SQL IN (...)` trọn gói, sau đó cấy (Enrich) kết quả ảo ngược lên Payload thành định dạng chuỗi phân tách mảng.
- **Reason:** Tối ưu hóa tuyệt đối tốc độ Ghi/Cập nhật (Write Speed). Tránh việc phải Update hàng ngàn dòng con khi Data ở bảng mẹ thay đổi.
- **Decision (DataGrid UX Bugfix):** Đại tu bộ nạp Cascading Dropdown (chọn Cột nguồn -> load Cột đích) của Rollup. Hủy bỏ cơ chế dùng `window.skaGlobalDict` (Từ điển mềm) thay bằng AJAX trực tiếp chọc cấu trúc MySQL Vật lý `DESCRIBE` để ngăn chặn lỗi mất đồng bộ Từ điển khi Schema bị tháo lắp từ nguồn khác.
- **Decision (Race Condition Fix):** Loại bỏ chiến thuật đợi `setTimeout(50ms)` (Gây lỗi trống lựa chọn Cột do AJAX chưa load kịp) khi mở form Chỉnh sửa Rollup. Triển khai phương pháp gắn cờ trạng thái `data-selected-val` vào thẻ HTML để hứng giá trị an toàn sau khi AJAX đã resolve.

## 2026-04-02 - Hoạch định Kiến trúc Ecosystem (Master Plugin) & Renaming
- **Decision:** Đổi tên thư mục gốc và file lõi của phần mềm thiết kế từ `ska-builder-core` thành `ska-no-code-design`.
- **Reason:** Chuẩn hóa tên gọi đúng với chức năng (Cung cấp Atomic Blocks và Tailwind JIT Engine). Khái niệm "Core" sẽ được quy hoạch thành một Master Plugin độc lập trong tương lai với tên gọi `ska-no-code-home` để đóng vai trò làm Bộ điều khiển Trung tâm (Ecosystem Manager).
- **Decision:** Di dời toàn bộ tài liệu AI của mảng Design Engine sang `.ska-ai/3-ecosystem/ska-no-code-design/` và xóa folder rác cũ.

## 2026-04-02 - Frontend Parser: SVG ClassName Fix (Bug Fix)
- **Problem:** Công cụ html2tailwind bị crash (`classes.replace is not a function`) khi copy HTML chứa thẻ SVG.
- **Root Cause:** Thuộc tính `className` của thẻ SVG trong DOM trả về một object `SVGAnimatedString` thay vì chuỗi Text, khiến hàm xử lý regex bị sụp đổ.
- **Fix:** Thay thế gọi ngầm `node.className` bằng hàm lấy thuộc tính hệ thống `node.getAttribute('class') || ''` nhằm ép kiểu dữ liệu luôn luôn trả về một mảng String cho Parser.

## 2026-04-01 - Tích hợp WP Core (Posts & Users) vào Relation Column
- **Decision:** Chấp thuận việc móc trực tiếp bảng vật lý của WordPress (`wp_posts` và `wp_users`) vào Cột "Tham Chiếu Nối Bảng" (Relation) của Ska Data Pro. Đặt chế độ Bypass bảo mật tĩnh cho 2 bảng này tại `class-admin-ajax.php` và `class-data-fetcher.php`.
- **Reason:** Việc đi vòng qua Data Providers (Adapter) ở Layer hiển thị (như ban đầu đã test `Test_Provider`) là KHÔNG ĐỦ hiệu năng để giải bài toán `WHERE IN` của CSDL Quan Hệ. SQL Native là con đường duy nhất để giải quyết N+1 Queries khi User lọc/gộp (Sort/Group/Filter) cột Liên kết.
- **Data Hydration Strategy:** Các cột Rollup (Ví dụ lấy Slug, Lấy Ảnh) từ các posts/users này sẽ được xử lý ở Layer phía trên (Logic Engine Frontend hoặc Cột Tra Cứu của Grid) thay vì lưu chết vào Flat tables để giữ đúng triết lý Decoupled Data.

## 2026-04-01 - Chốt Kiến trúc Lõi: Application-Level Relation & Formula Engine (Brainstorm)
- **Decision (Core Architecture):** Tiếp tục duy trì hệ sinh thái CSDL "Bảng Phẳng" (Flat Tables) kết hợp với Cơ chế "Quan hệ mềm ở tầng Ứng dụng" (Application-Level Relation) thay vì Foreign Key cứng của MySQL hoặc EAV Postmeta. Lưu ID dưới dạng CSV thô (ví dụ: `15, 20`).
- **Reason:** Đảm bảo khả năng Drop Column/Table an toàn tuyệt đối từ Giao diện Admin mà không kích hoạt Database Locking của SQL. Khai thác tốc độ đọc 1-chạm cực lẹ của cấu trúc Bảng Rộng (Wide Tables) để tối ưu hệ thống App-Scale của Ska App Builder.
- **Decision (Formula Engine Compatibility):** Cách duy nhất để Formula Engine có thể thực hiện phép tính chéo bảng (như `SUM(Đơn hàng)`) trên kiến trúc này là dời luồng **Phân giải Quan hệ (Resolution)** xuống tận đáy của Data Engine (Hàm `Data_Fetcher::get_table_data`).
- **Data Flow:** PHP Query Builder xuất mảng Flat -> Tự động đánh hơi cột `relation` -> Fetch ngay dữ liệu Bảng Đích bằng 1 lệnh `WHERE IN` -> Cấy (Enrich) dữ liệu vào Payload (biến IDs thành `[{id:15, label:"Tên"}]`) -> Bắn qua Hook `apply_filters('ska_data_query')` -> Các tầng trên như Admin UI, Vòng lặp Giao diện Web, và Cỗ máy Formula cứ việc lấy xài, không cần lo nghĩ về Logic.

## 2026-04-01 - Extensibility POC (Data Providers) & Logic Engine Root Hooking
- **Decision (Plugin Load Order fix):** Ràng buộc tiến trình nạp Adapter (vd: `class-ska-provider.php` và `class-test-provider.php`) vào giai đoạn Event Loop `plugins_loaded`.
- **Reason:** Trước đây, do WordPress nạp file `ska-data-pro.php` (A-Z) trước khi cấu trúc xương sống `ska-builder-core` hoàn thiện, chốt chặn Fatal Error `interface_exists` gạt bỏ hoàn toàn bộ sậu Provider, làm vòng lặp Data Backend trở nên vô dụng bấy lâu nay. Việc đổi order nạp sẽ khắc phục triệt để và vững chắc mô hình Extensible Data Adapter (Chuẩn bị làm móng cho WordPress Rest User và WooCommerce Products List).

## 2026-04-01 - Pivot Chiến Lược: Tạm hoãn Formula & Nâng cấp DataGrid Controls
- **Decision:** Tạm hoãn hệ thống Formula (Virtual Column) do độ phức tạp cao, ưu tiên chuyển hỏa lực phát triển sang các công cụ điều khiển Bảng Dữ Liệu cốt lõi (DataGrid Controls): Lọc (Filter), Sắp Xếp (Sort), Nhóm (Group).
- **Reason:** Đảm bảo trải nghiệm xương sống của một nền tảng dữ liệu "Airtable-like". Nếu Data cứng không thể Lọc và Nhóm thì các tính năng râu ria khác vô nghĩa.
- **Decision (Architecture):** Toàn bộ trạng thái Lọc, Xếp, Nhóm đều được mã hóa (Driven) lên URL Parameters (GET requests: `?filter_field=...&orderby=...`). Điều này cho phép user Bookmark link, tạo ra các "Views" tĩnh dễ dàng chia sẻ nội bộ mà không đòi hỏi thêm cơ sở dữ liệu State Management ở Frontend JS.
- **Decision (Group Rendering):** Thay vì dùng PHP để lồng ghép và parse mảng JSON mệt nhọc ở Backend, Tính năng "Gộp Nhóm (Group)" thực thi theo triết lý nhẹ: MySQL chỉ việc SORT vật lý theo cột. Tầng Frontend PHP nhận dữ liệu, chèn vào các Dòng Divider ngang (Group Headers) mỗi khi phát hiện bước nhảy đổi giá trị ở dòng kế tiếp. Cách tiếp cận này hiệu suất vượt trội và UI Code mỏng đi đáng kể.

## 2026-03-31 - Cơ sở dữ liệu Quan Hệ (Relational DB) & Cột Tính Toán (Formula)
- **Decision:** Tích hợp tính năng Nối Bảng (Reference) vào DataGrid đại diện cho Database Quan Hệ. Áp dụng chuẩn Flat Table: không dùng FOREIGN KEY constraint cứng để tránh Locking DB, dùng cột dạng `TEXT` lưu list ID cách nhau bởi dấu phẩy để hỗ trợ cả quan hệ 1-N (Multi-reference). UI sẽ là Popover Live Search AJAX.
- **Decision:** Chấp thuận thiết kế Cột Công Thức (Formula). Không lưu thẳng thành cột dạng `GENERATED ALWAYS AS` của MySQL (vì khó tương thích chéo version), mà Render On-the-fly (Tính toán bằng Data Engine PHP Backend) lúc Frontend đọc dữ liệu.
- **Decision:** Kế hoạch này được đẩy sang Session tiếp theo.

## 2026-03-31 - DataGrid UX/UI & System Auto-Prefix
- **Decision (Boolean Toggle):** Loại bỏ Checkbox thuần tẻ nhạt, xây dựng Component CSS Toggle Switch dựa vào Tailwind. Chỉ sử dụng hàm API 1-Click (Gạt -> Gọi AJAX -> Lưu ngầm) để loại bỏ Nút "Lưu" thừa thãi.
- **Decision (WP Media Z-index Collision):** Xử lý triệt để bug Popover của Datagrid bị xóa tự động khi người dùng nhấp vào khung Modal Chọn Ảnh `wp.media` (do Focus events). Áp dụng cờ `isMediaOpen` và check `.media-modal` cực kỳ bulletproof.
- **Decision (Dev UX Auto-Prefix):** Cỗ máy Query Builder tự động gắn tiền tố `ska_data_` nếu Dev gõ thiếu (ví dụ gõ `products` thay vì `ska_data_products`). Đảm bảo an toàn bảo mật (Ngăn chặn đọc trộm `wp_users` trái phép) mà vẫn đem lại trải nghiệm lập trình thân thiện, nhàn rỗi.

## 2026-03-30 - Hệ tư tưởng: Frontend App Portals & Sub-Admin Dashboards (Unified Canvas Paradigm)
- **Reason (Unified Canvas):** Mọi giao diện (bất kể của Học viên hay Quản lý) bản chất đều là "Giao diện người dùng" được xây dựng bằng cùng một bộ công cụ kéo thả duy nhất (`ska-builder-core` kết hợp Design Engine). Điểm khác biệt duy nhất làm nên một "Trang Admin" là Data Context và phân quyền RBAC (Role-Based Access Control) thông qua `ska-logic-engine`.
- **Implementation Strategy:** 
  1. TUYỆT ĐỐI KHÔNG sinh thêm một Plugin Builder mới chỉ để làm giao diện Admin để tránh gây nhũng nhiễu (Bloatware).
  2. Dùng chính `ska-builder-core` để đăng ký một Custom Post Type (Ví dụ: `ska_portal` hoặc `App Portals`).
  3. UI/UX (Layout tĩnh) do `ska-builder-core` và thẻ Tailwind tạo ra sẽ được lưu vào `post_content` của CPT này.
  4. Data động đổ vào bảng/danh sách nằm chung Dashboard sẽ được kéo từ Khóa Ngoại (Foreign Keys) của Flat Tables trong `Ska Data Pro`.
  5. CPT này sẽ mang cờ `publicly_queryable = false` để ẩn hoàn toàn khỏi Google Search Index (SEO Protection) và bảo vệ an ninh ở tầng `template_redirect` bằng phân quyền Role.

## 2026-03-30 - Table Categories & I18n UI Strategy
- **Decision:** Đổi mới mô hình Table CRUD: Cung cấp tính năng "Thuộc Hệ Sinh Thái (Nhóm)" thông qua thuộc tính `__table_info['group']`.
- **Reason:** Cấu trúc tổ chức Dữ liệu của Admin không bị vứt hỗn độn vào một rổ "Tùy Biến". Những Table rác tạo mới có thể được gom nhóm vào (Ví dụ: nhóm "booking" cùng các bảng Lịch khám gốc) để tiện API. Frontend Model Modal Dropdown cũng được Render Tự động quét các Group đang hoạt động.
- **Decision (Data Schema Core):** Bản thân Physical database table sẽ giữ nguyên tên slug hệ thống `ska_data_*`. Tính năng Rename đổi Ký danh, Biểu tượng được thực hiện thông qua JSON map của `ska_data_dictionary` thay vì `ALTER TABLE` nhằm duy trì 100% tính toàn vẹn câu lệnh SQL Query.
- **Decision (Internationalization - i18n):** Tạm hoãn hệ thống GNU Gettext `__()` ở Giai đoạn MVP.
- **Reason:** Quyết tâm giữ nguyên Code Base = Hardcoded Vietnamese để tối đa hóa "Time to value" và sự tường minh Code. Giai đoạn sau (Vận hành), chạy Scripts Regex quét và bọc i18n, kết hợp AI generate `.po`, `.mo` phục vụ mục đích Pitching (Gọi Vốn) & Bán Global Plugin. Lời giải này giải phóng nguồn lực của Coder.

## 2026-03-29 - Ska Data Pro Initialization & UX Strategy
- **Decision (UX):** Áp dụng mô hình UX "Airtable-like". Giấu toàn bộ logic phức tạp (SQL, Hooks) xuống Backend. Giao diện quản lý Schema phải trực quan dạng "Tạo bộ dữ liệu".
- **Decision (Data Templates Feature):** Phát triển tính năng "Pre-built Data Templates". Cung cấp sẵn các bộ Schema (Bảng & Cột) chuẩn cho các mô hình: E-Commerce, Học trực tuyến (LMS), Đặt lịch (Booking), Bất động sản (Real Estate).
- **Automation:** Khi cài đặt Template, hệ thống sẽ tự động cấu hình Table và bơm sẵn Dummy Data (Sample Data).
- **Reason:** Đập tan rào cản với user không rành công nghệ, tạo hiệu ứng "Wow" tức thì vì có Data thật để lấy ra thiết kế ngay (Time-to-Value).

## 2026-03-29 - Hardening Ska Builder CSS Engine & Editor Parity (WYSIWYG Fidelity)
- **Decision:** Nâng cấp Tailwind JIT `resolve_dimension` bằng regex hỗ trợ phân số (VD: `1/2`, `1/3`) với độ chính xác chuẩn Tailwind (`33.333333%`), khắc phục lỗi class responsive (như `w-1/2`) không sinh ra CSS ở Backend, dẫn đến mất tác dụng trước `w-full`.
- **Decision:** Bổ sung `margin: 0;` vào hàm `get_core_reset_css()` nhắm thẳng vào `[class*='wp-block-ska-builder']` trên cả Frontend và Editor để tiêu diệt triệt để cơ chế tự động Center của Gutenberg.
- **Decision:** Điều chỉnh thuộc tính CSS `display: inline-flex; align-items: center; justify-content: center;` mặc định cho class `.ska-button-block` (Ska Button) trong JIT Compiler Reset. Việc này giải phóng thẻ `<a>` và `<button>` khỏi giới hạn của Inline element gốc, giúp chúng tiếp nhận hoàn hảo các lệnh `margin` (-mt-10), padding, width, height mà không bắt buộc người dùng phải gõ thêm `inline-flex`.
- **Decision:** Xóa cờ `margin: 0 !important;` quá khích trong `ska-editor-helper.js` đối với các khối con của `.wp-block-ska-builder-container`. Điều này trả lại sự tự do (Specificity behavior) cho các class do Tailwind CDN sinh ra như `-mt-10` trong môi trường Editor.
- **Decision:** Áp dụng chặt chẽ "Clean Slate Rule": Từ chối hỗ trợ các thông số ngụy tạo ngoài hệ đo lường chuẩn của Tailwind (như `-mt-50`). Người dùng bắt buộc phải dùng hệ quy chiếu chuẩn (như `-mt-48`, `-mt-52`) hoặc Arbitrary values (`-mt-[50px]`) để bảo vệ tính đồng nhất của Design Engine và giữ nguyên giá trị giáo dục (Selling Point) của hệ thống.

## 2026-03-29 - Deployment & Build Ecosystem Standardization
- **Decision:** Thống nhất sử dụng thư viện `node-archiver` (JS) làm công cụ đóng gói (Zip) duy nhất khi xuất bản Plugin thay cho các công cụ có sẵn của hệ điều hành (như `tar` hay `Compress-Archive` của Windows). Mọi thao tác đóng gói tệp tin sẽ được thực hiện thông qua script `build-zip.js`.
- **Reason:** Các công cụ nén mặc định của Windows 10/11 thường sinh ra Metadata dị biệt, hoặc tạo cấu trúc root folder không liền mạch khiến hàm `unzip_file()` của WordPress (trên máy chủ Linux) thất bại trong việc định vị file kích hoạt plugin (gây ra lỗi "Tập tin của plugin không tồn tại"). `node-archiver` giúp thiết lập Stream tệp nén theo chuẩn công nghiệp, bảo đảm độ chính xác 100% khi PHP ZipArchive đọc vào.

## 2026-03-29 - Ska Canvas Theme Initialization & Architecture
- **Decision:** Khởi tạo `ska-canvas` (Ska Blank Theme) làm vật chủ siêu nhẹ, loại bỏ hoàn toàn WP default CSS (wp-block-library, global-styles).
- **Decision:** Tắt hoàn toàn FSE (Full Site Editing) qua `theme.json` để ngăn WordPress ép CSS layout.
- **Decision:** Cập nhật JIT Compiler (Plugin): Phát hiện nguyên nhân thực sự khiến `text-primary` bị gạch ngang và biến thành màu trắng trong Editor. Sự cố KHÔNG PHẢI do Specificity War, mà là do JIT Compiler đã **bỏ qua hoàn toàn class này** vì Options `ska_custom_colors` trong Database bị rỗng, khiến nó không tạo ra mã CSS. WordPress Core Editor mặc định chèn `color: #fff` cho thẻ Button khiến nút bị trắng.
- **Fix:** Code Fallback mặc định cho Brand Colors (`primary`, `secondary`, `background-light`, `background-dark`) bên trong `Tailwind_Color_Registry` để JIT Compiler luôn hoạt động kể cả khi chưa lưu cấu hình thiết kế từ UI. Đồng thời rút lại kĩ thuật `:where()` để duy trì sức mạnh `2,1` cho Custom CSS Reset, đè chết WP Core CSS mặc định.
- **Decision:** Giữ Brand Styles (Primary Colors/Typography) ở lại Plugin (Ska Design Engine) thay vì đưa vào Theme, nhằm ngăn chặn hiện tượng "Theme Lock-in" - giúp người dùng có thể đổi Theme mà không mất thiết kế.
- **Decision:** Trong tương lai, **Ska Theme Builder** (Header/Footer/Archive Design) sẽ được phát triển như một tính năng bên trong `ska-builder-core` thay vì tách thành một Plugin riêng biệt để giới hạn lượng Plugin phải cài đặt.
- **Decision:** File `index.php` của Ska Canvas chỉ chứa mã cơ sở và các Hook rỗng (`ska_theme_header`, `ska_theme_footer`) để Plugin Theme Builder tương lai có thể cắm thiết kế vào một cách tự nhiên.

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
