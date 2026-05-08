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
