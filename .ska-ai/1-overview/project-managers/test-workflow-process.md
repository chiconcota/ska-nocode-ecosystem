# Workflow Kiểm thử E2E: Ska Link Engine (Milestone 4)

> [!NOTE]
> Tài liệu này cung cấp quy trình và kịch bản chuẩn để kiểm thử chất lượng (QA) hệ thống Ska Link Engine trên cả 2 diện: Block-Level (Container, Image, Button) và Inline (RichText format), đặc biệt tập trung vào khả năng hoạt động trơn tru với **Ska Loop Engine**.

## Mục tiêu Kiểm thử (Objectives)
1. **SEO Compliance:** Đảm bảo mọi Link đều xuất ra thẻ `<a href="...">` nguyên bản ở quá trình Server-Side Rendering (SSR). Không dùng JS redirect.
2. **Flat DOM Integrity:** Đảm bảo kỹ thuật "Morphing" (biến đổi tagName thành `<a>` cho Container/Button) hoạt động đúng, không sinh ra div bọc dư thừa.
3. **Decoupled Hydration:** Đảm bảo cấu trúc Mustache `{{key}}` xuất ra từ Link Engine được Loop Engine giải mã (hydrate) chính xác ở Frontend mà không dính N+1 Queries.

---

## 🧪 Các Kịch Bản Kiểm Thử (Test Cases)

### Test Case 1: Static Link trên Image & Container
**Trạng thái:** `[ ] Chờ kiểm thử`

**Bước thực hiện (Gutenberg Editor):**
1. Kéo thả block `ska-builder/image` vào trình soạn thảo và chọn một hình ảnh.
2. Mở tab **Link Settings** ở cột Inspector bên phải, nhập URL tĩnh (vd: `https://wp.org`), chọn tuỳ chọn `Open in new tab`.
3. Kéo thả block `ska-builder/container`.
4. Trong Container, mở tab **Link Settings**, nhập URL tĩnh (vd: `/contact`), để target mặc định (`_self`).
5. Nhấn Lưu/Update trang.

**Kỳ vọng tại Frontend (Check qua Inspect Element):**
- **Image:** Thẻ `<img>` gốc phải được bọc trong một thẻ `<a href="https://wp.org" target="_blank" rel="noopener noreferrer">`.
- **Container:** Thẻ gốc của container (ví dụ thẻ `<div>` hoặc `<section>`) phải tự động chuyển đổi thành thẻ `<a href="/contact" target="_self">` nhưng vẫn giữ nguyên tất cả các class Tailwind gốc (Flat DOM được bảo toàn).

---

### Test Case 2: System Dynamic Link trên Button
**Trạng thái:** `[ ] Chờ kiểm thử`

**Bước thực hiện (Gutenberg Editor):**
1. Kéo thả block `ska-builder/button`.
2. Mở tab **Link Settings**, gạt nút kích hoạt tính năng **Dynamic Link**.
3. Chọn nguồn (Source) là `System` và chọn Key là `Home URL`.
4. Nhấn Lưu/Update trang.

**Kỳ vọng tại Frontend (Check qua Inspect Element):**
- Khối button không render ra thẻ `<button>`. Nó phải được render thành thẻ `<a>` chứa các class Tailwind của nút, với thuộc tính `href` chứa đúng đường dẫn trang chủ của website (ví dụ: `https://your-domain.local/`).
- Các thuộc tính không liên quan của dynamic link (như `data-dynamic-source`) phải bị gỡ bỏ ở HTML output.

---

### Test Case 3: Loop Dynamic Link trên Inline Text & Block-Level (Advanced)
**Trạng thái:** `[ ] Chờ kiểm thử`

**Bước thực hiện (Gutenberg Editor):**
1. Mở một môi trường có hỗ trợ **Ska Loop Engine** (Ví dụ: danh sách bài viết hoặc block `ska-builder/list`).
2. **Kiểm thử Inline:** Thêm một block `ska-builder/text` bên trong vòng lặp.
   - Bôi đen một cụm từ bất kỳ, nhấn icon Link (Ska Dynamic Link) trên thanh Toolbar nổi.
   - Bật **Dynamic Link**, chọn nguồn `Loop` và thiết lập Key là `post_url` (hoặc biến chứa link tương ứng).
3. **Kiểm thử Block-Level:** Thêm một block `ska-builder/container` (hoặc image) cũng nằm bên trong vòng lặp.
   - Mở tab Link Settings của khối này, gán URL qua chế độ Dynamic Link -> Nguồn `Loop` -> Key `post_url`.
4. Nhấn Lưu/Update trang.

**Kỳ vọng tại Frontend (Check qua Inspect Element):**
- HTML SSR trả về từ PHP Render (nếu chưa qua vòng lặp) sẽ có dạng `<a href="{{post_url}}">`.
- Tuy nhiên, sau khi đi qua cơ chế Hydration của Loop Engine, chuỗi `{{post_url}}` phải được nội suy thành đường link thật của từng đối tượng trong vòng lặp (vd: `/bai-viet-1/`, `/bai-viet-2/`).
- Quá trình xuất thẻ link này không làm gia tăng Query Database (Check bằng Query Monitor để đảm bảo Zero N+1 Queries).

---

## 🛠 Hướng dẫn Gỡ lỗi (Troubleshooting)

| Dấu hiệu | Nguyên nhân có thể | Cách khắc phục |
| :--- | :--- | :--- |
| **Container bị lồng 2 thẻ `<a href>`** | Logic morphing của `tagName` xung đột với việc bọc wrapper. | Kiểm tra `render.php` của `ska-container`, đảm bảo chỉ render 1 thẻ gốc duy nhất và chỉ đổi `$tagName = 'a'` thay vì bọc ngoài. |
| **Thẻ `<a>` inline xuất hiện thuộc tính rác (`data-dynamic-source`)** | Regex parser trong `resolve_inline_links` bỏ sót việc xóa Attribute. | Mở `class-dynamic-data.php`, cập nhật logic `preg_replace` để loại bỏ toàn bộ các chuỗi `data-dynamic-*="..."` sau khi đã thay thế `href`. |
| **Dynamic link vòng lặp in ra màn hình chuỗi `{{post_url}}`** | Chuỗi link sinh ra muộn hơn thời điểm Loop Engine thực hiện Hydration. | Kiểm tra thứ tự các Hook Filters liên quan đến `the_content` hoặc xem xét gọi lại Regex nội suy trên Output cuối cùng của Loop. |

---
*Ghi chú: Workflow này được tạo ra để đảm bảo chất lượng hệ thống (QA Phase). Khi thực hiện kiểm thử thành công, hãy đánh dấu check `[x]` vào các kịch bản trên và tiếp tục sang việc phát triển Ska Molecules thuộc Phase 4.*

---

# Workflow Kiểm thử E2E: Ska Theme Builder (Milestone 5)

> [!NOTE]
> Tài liệu này cung cấp quy trình kiểm thử hệ thống Theme Builder, bao gồm môi trường Isolated Iframe, cơ chế Dual-Table Storage, và khả năng đánh chặn luồng Render bằng Smart Virtual Wrapper.

## Mục tiêu Kiểm thử (Objectives)
1. **Isolated Editor & Dual-Table:** Đảm bảo việc tạo/chỉnh sửa template diễn ra độc lập trong Iframe và dữ liệu được phân luồng chính xác (Metadata lưu ở `ska_data_sys_theme_templates`, HTML lưu ở `ska_data_sys_organisms`). Không ghi rác vào `wp_posts`.
2. **Smart Virtual Wrapper:** Đảm bảo hệ thống bắt đúng URL hiện tại để thay thế Header/Footer/Body của Theme mặc định bằng thiết kế của Builder.
3. **Display Conditions (Rule Builder):** Đảm bảo backend parser đánh giá đúng các Rule Include/Exclude đối với môi trường thực tế (is_front_page, is_single...).

---

## 🧪 Các Kịch Bản Kiểm Thử (Test Cases)

### Test Case 1: Tạo mới & Chỉnh sửa Template trong Iframe
**Trạng thái:** `[x] Chờ kiểm thử`

**Bước thực hiện:**
1. Truy cập WP Admin -> Ska Builder -> Theme Builder.
2. Bấm "Tạo Template Mới", đặt tên "Header Test", Location: `Header`, Conditions: Để trống (Mặc định toàn trang). Bấm Lưu.
3. Bấm vào nút "Mở Editor" của template vừa tạo. Trình duyệt phải tải Iframe toàn màn hình.
4. Kéo thả các block vào Editor (ví dụ: Container, Heading, Image), bấm Lưu trên thanh công cụ của Iframe.
5. Kiểm tra Database (bảng `ska_data_sys_theme_templates` và `ska_data_sys_organisms`) xem ID có khớp và data có được ghi đúng không. Đảm bảo `wp_posts` không bị thêm record thừa nào ngoài những Draft tạm thời đã bị xóa (nếu có).

**Kỳ vọng:**
- Không lỗi JS Console trong quá trình PostMessage giữa cha và con.
- `organism_id` trong bảng Template trỏ đúng vào record chứa HTML vừa thiết kế trong bảng Organism.

---

### Test Case 2: Đánh chặn giao diện bằng Smart Virtual Wrapper
**Trạng thái:** `[x] Chờ kiểm thử`

**Bước thực hiện:**
1. Mở trang chủ (Front Page) của website ở giao diện Frontend (khi chưa đăng nhập).
2. Kiểm tra Header/Footer hiện tại (nó sẽ là của theme Twenty Twenty-Four hoặc theme đang active).
3. Đảm bảo Template "Header Test" (vừa tạo ở Test Case 1) đang ở trạng thái **Active**.
4. Tải lại trang chủ.

**Kỳ vọng:**
- Header mặc định của Theme biến mất. Thay vào đó là thiết kế Header do bạn vừa kéo thả.
- Footer hoặc Content (nếu chưa có Template tương ứng) vẫn render bình thường hoặc dùng fallback content.
- Bật Query Monitor để đảm bảo Hook `template_include` (Priority 99) đang chạy và không bị lỗi.

---

### Test Case 3: Kiểm thử Rule Builder (Điều kiện hiển thị phức tạp)
**Trạng thái:** `[X] Chờ kiểm thử`

**Bước thực hiện:**
1. Truy cập Theme Builder, tạo một Template mới tên "Promo Banner", Location: `Header`.
2. Mở "Sửa Settings" của Template này. Bấm Thêm Rule.
3. Tạo 2 rules:
   - `Include` -> `Trang chủ (Front Page)`
   - `Exclude` -> `Kết quả tìm kiếm (Search)`
4. Lưu Settings, mở Editor và thiết kế nội dung nổi bật cho template (ví dụ: Bảng chữ đỏ chót).
5. Kích hoạt Template.

**Kỳ vọng tại Frontend:**
- Mở Trang chủ: Template "Promo Banner" xuất hiện.
- Mở một Bài viết cụ thể (Single Post): "Promo Banner" **không** xuất hiện.
- Thực hiện Search (VD: `/?s=test`): Mặc dù trang Search có thể là một dạng archive, nhưng vì có luật Exclude, banner **không** được xuất hiện.

---

## 🛠 Hướng dẫn Gỡ lỗi (Troubleshooting)

| Dấu hiệu | Nguyên nhân có thể | Cách khắc phục |
| :--- | :--- | :--- |
| **Giao diện không đè được Theme cũ** | Theme hiện tại không dùng các hàm chuẩn của WP. | Đảm bảo Theme cũ vẫn đang gọi chuẩn xác `wp_head()`, `wp_footer()`, và sử dụng file `index.php` hoặc page templates chuẩn theo Hierarchy. Kiểm tra mảng `template_include` có bị plugin khác override ở priority > 99 không. |
| **Condition hoạt động sai ở Trang chủ** | Nhầm lẫn giữa `is_front_page()` và `is_home()`. | Backend đã fix gom chung `is_front_page() OR is_home()` cho Rule Trang chủ. Tuy nhiên nếu dùng cấu hình static page, hãy kiểm tra lại Settings > Reading của WP. |
| **Lưu xong Editor trắng trang** | JIT Compiler không lấy được HTML Cache. | Kiểm tra bảng `ska_data_sys_organisms`, trường `html_content` phải có dữ liệu. Check POST payload trong Network Tab của DevTools lúc bấm Lưu. |

---

# Workflow Kiểm thử E2E: Ska Dark Mode Engine (Phase 4.4)

> [!NOTE]
> Tài liệu này cung cấp quy trình kiểm thử hệ thống Dark Mode Engine. Mục tiêu là đảm bảo khả năng chuyển đổi giao diện mượt mà (Alpine.js State), biên dịch CSS chính xác (Tailwind JIT), ghi nhớ trạng thái (localStorage) và đặc biệt là không bị nháy giao diện khi tải trang (Zero FOUC).

## Mục tiêu Kiểm thử (Objectives)
1. **State & CSS Compilation:** Nút Toggle thay đổi được state `$store.skaTheme.isDark` và JIT Compiler sinh đúng CSS cho class `dark:`.
2. **Persistence & Anti-FOUC:** Trạng thái Dark Mode được lưu vào localStorage và phục hồi tức thì khi F5 trang mà không bị chớp sáng (FOUC).
3. **Reactive UI:** Các thành phần UI có thể phản ứng (reactive) với state Dark Mode (ví dụ thay đổi icon sáng/tối).

---

## 🧪 Các Kịch Bản Kiểm Thử (Test Cases)

### Test Case 1: Chuyển đổi Dark Mode cơ bản (Toggle & JIT CSS)
**Trạng thái:** `[x] Đã kiểm thử thành công (Fixed FOUC Bug)`

**Bước thực hiện (Gutenberg Editor):**
1. Kéo thả một `ska-builder/container`. Thêm class Tailwind: `bg-white dark:bg-slate-900 transition-colors duration-300`.
2. Bên trong container, thả một `ska-builder/text`. Thêm class: `text-slate-900 dark:text-white`. Nhập text "Chế độ nền tối".
3. Thả thêm một `ska-builder/button` vào trong container. 
4. Tại Inspector của Button, chọn tuỳ chọn hành động (Action) là **Toggle Dark Mode**.
5. Nhấn Lưu/Update trang.
6. Ra ngoài Frontend xem kết quả.

**Kỳ vọng tại Frontend:**
- Mặc định nền là trắng, chữ đen.
- Nhấn vào nút Button, nền chuyển sang màu tối (`bg-slate-900`) và chữ chuyển sang màu trắng.
- Kiểm tra mã nguồn (Inspect Element) thẻ `<html>` sẽ được tự động thêm/bớt class `dark`.
- Thẻ `<style id='ska-jit-styles'>` phải chứa định nghĩa CSS cho `.dark .dark\:bg-slate-900` và `.dark .dark\:text-white`.

---

### Test Case 2: Lưu trữ LocalStorage & Chống FOUC (Anti-FOUC)
**Trạng thái:** `[x] Đã kiểm thử thành công (Fixed FOUC Script Logic)`

**Bước thực hiện:**
1. Tiếp tục từ Test Case 1, đảm bảo giao diện đang ở trạng thái **Nền tối (Dark Mode = ON)**.
2. Nhấn **F5 (Reload trang)**.
3. Chú ý kỹ vào khoảnh khắc trang vừa tải xong (trước khi hình ảnh kịp load hết).

**Kỳ vọng tại Frontend:**
- Giao diện phải **hiển thị nền tối ngay lập tức** mà không có bất kỳ hiện tượng "nháy màn hình trắng" (FOUC - Flash of Unstyled Content) nào.
- Mở DevTools -> Application -> Local Storage. Phải tồn tại biến `ska_dark_mode` với giá trị là `dark` hoặc `true`.
- Kiểm tra mã nguồn (View Page Source): Phải thấy đoạn Script nội tuyến chống FOUC nằm ngay đầu thẻ `<head>`.

---

### Test Case 3: Nâng cao - Giao diện phản hồi (Conditional Rendering Icon)
**Trạng thái:** `[x] Đã kiểm thử thành công (Verified by Browser Subagent)`

**Bước thực hiện (Gutenberg Editor):**
1. Thêm 2 khối `ska-builder/icon` vào giao diện:
   - Icon 1 (Mặt Trời): Thêm HTML Attribute (Alpine) `x-show` với giá trị `!$store.skaTheme.isDark`.
   - Icon 2 (Mặt Trăng): Thêm HTML Attribute (Alpine) `x-show` với giá trị `$store.skaTheme.isDark`.
2. Lưu và ra Frontend kiểm tra.

**Kỳ vọng tại Frontend:**
- Ở chế độ Light Mode, chỉ có Icon Mặt Trời hiển thị.
- Khi nhấn nút Toggle sang Dark Mode, Icon Mặt Trời biến mất và Icon Mặt Trăng xuất hiện ngay lập tức mà không cần load lại trang.

---

## 🛠 Hướng dẫn Gỡ lỗi (Troubleshooting)

| Dấu hiệu | Nguyên nhân có thể | Cách khắc phục |
| :--- | :--- | :--- |
| **Bấm nút Toggle không có tác dụng** | JS Core hoặc Alpine Store chưa load đúng. | Kiểm tra console xem có lỗi `Alpine is not defined` hoặc biến `$store.skaTheme` bị undefined không. Chắc chắn script `ska-frontend.js` đã được nạp. |
| **Thẻ `<html>` có class `dark` nhưng không đổi màu** | Tailwind JIT chưa quét và xuất mã CSS cho `dark:` | Kiểm tra lại Regex trong `class-style-manager.php`, đảm bảo nó hỗ trợ modifier có dấu hai chấm như `dark:bg-red-500`. |
| **Bị nháy màn hình (FOUC) khi tải lại trang** | Inline Script chưa được đưa lên đủ cao trong thẻ `<head>`. | Kiểm tra `add_action('wp_head', ..., 0)` trong PHP để đảm bảo priority = 0 (xuất hiện sớm nhất). |
