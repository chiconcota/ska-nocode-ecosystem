# FEATURE: HTML2Tailwind Parser & Import System
*Module này đã được di chuyển từ Skaaa Bridge cũ sang làm một thành phần cốt lõi của **Skaaa No-Code Design**.*

**Status:** 🟢 Integrated into Design Engine (v1)
**Role:** [PARSER] Dịch ngược từ mã HTML/CSS thô sang cấu trúc Gutenberg Atomic Blocks bọc lớp class Tailwind CSS.

## 1. Kiến trúc vận hành
- **Backend:** Bộ Parser JS được đăng ký và nạp trong editor qua hook `enqueue_block_editor_assets` của plugin `skaaa-no-code-design`.
- **Frontend File**: `assets/js/html-to-blocks.js` (parser đệ quy sử dụng DOMParser).
- **Import Block**: Block `skaaa-builder/html2tailwind` cung cấp UI để người dùng copy-paste mã HTML thô, tự động convert thành các khối nguyên tử tại tọa độ chèn.

## 2. Bản đồ ánh xạ phần tử (Tag Mapping)
| HTML Tag | Target Skaaa Block | Logic xử lý / Metadata mapping |
|---|---|---|
| `div, section, article, main, header, footer, aside, nav` | `skaaa-builder/container` | Container chính. Bảo toàn thuộc tính semantic `tagName`. |
| `h1~h6, p, span` | `skaaa-builder/text` | Giữ nguyên `innerHTML`. *Smart Text Detection*: Tự động thăng cấp thành `container` nếu phần tử con chứa ảnh, nút bấm hoặc icon. |
| `img` | `skaaa-builder/image` | Trích xuất `url`, `alt`, `id` sang attributes của block. |
| `a, button` | `skaaa-builder/button` | Lấy `href`, nội dung text. Nếu có icon lồng bên trong, tự động bóc tách và lưu class của icon vào `iconClasses`. |
| `span.material-symbols-outlined` | `skaaa-builder/icon` | Nhận diện tự động icon font. |
| `ul, ol` | `skaaa-builder/list` | Ánh xạ khối danh sách. |
| `li` | `skaaa-builder/list-item` | Ánh xạ mục danh sách. |

## 3. Các cải tiến cốt lõi (Cập nhật 2026-04-02)
- **SVG ClassName Crash Fix:** Tránh crash DOM Parser khi copy SVG bằng cách thay thế `node.className` bằng `node.getAttribute('class') || ''` (vì SVGAnimatedString không phải là string thông thường).
- **Smart Text Detection:** Khi thẻ `<p>` hoặc `<span>` chứa con là Block-level element, tự động thay thế cha thành Container `div` để tránh lỗi vỡ cấu trúc HTML5.
- **Nested Placement:** Sử dụng API `replaceBlocks(clientId, blocks)` của Gutenberg thay vì `insertBlocks` để các khối convert xong nằm đúng phả hệ dòng cha của khối Import.
