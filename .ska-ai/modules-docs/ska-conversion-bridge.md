# MODULE: Ska Conversion Bridge

**Status:** 🟢 Integrated into Core (v1)
**Role:** [ADAPTER] Import (HTML2Tailwind) & Export (JSON-Gen)
**Location:** Đã hội quân vào `ska-builder-core/` (assets + blocks)

## 1. Kiến trúc hiện tại
- **Backend:** Script Parser được enqueue trong `class-core.php` (Design Engine) qua hook `enqueue_block_editor_assets`. Chỉ nạp khi `ska_bridge_enabled` = `yes`.
- **Frontend:** 
  - `ska-builder-core/assets/js/html-to-blocks.js`: Parser thuần JS sử dụng `DOMParser` đệ quy. Bảo toàn context `tagName` và `bodyClass`.
  - Block `ska-builder/html2tailwind` (`src/ska-bridge-import/`): UI nhận mã HTML. Đăng ký có điều kiện trong `blocks/init.php`.
- **Dashboard Toggle:** Bật/tắt module trong Admin Dashboard (System Status table). Lưu trạng thái qua Options API (`ska_bridge_enabled`).

## 2. Tag Mapping (Parser)
| HTML Tag | Block | Ghi chú |
|---|---|---|
| `div, section, article, main, header, footer, aside, nav` | `ska-builder/container` | Container chung (Bảo toàn `tagName`) |
| `h1~h6, p, span` | `ska-builder/text` | Giữ nguyên `innerHTML`. *Smart Text Detection*: Thăng cấp thành `ska-builder/container` nếu chứa icon/button. |
| `img` | `ska-builder/image` | Hỗ trợ `url`, `alt`, `id` mapping |
| `a, button` | `ska-builder/button` | Lấy `href`, nội dung chữ. Nếu có icon `<span>` lồng bên trong, tự động tách icon ra và lưu custom tailwind class của icon vào `iconClasses`. Tự nhận diện nút Submit hoặc ép thành thư mục Link |
| `span.material-symbols-outlined` | `ska-builder/icon` | Tự nhận diện icon font |
| `ul, ol` | `ska-builder/list` | Mapping danh sách |
| `li` | `ska-builder/list-item` | Mapping mục danh sách |

## 3. Đã giải quyết (Cập nhật 2026-03-27)
- [x] **Smart Text Detection:** Khi thẻ `<p>` hoặc `<span>` chứa con là Block-level element (Icon, Button, Image), parser sẽ thăng cấp cha thành Container `div` và parse con thành các block vệ tinh.
- [x] **Whitespace Fix:** Sử dụng `.replace(/\s+/g, ' ').trim()` để xóa bỏ khoảng trắng thừa gây rớt dòng khi convert nút/văn bản.
- [x] **Nested Placement Fix:** Truyền tọa độ `clientId` của khối Import gốc vào hàm chuyển đổi, sử dụng `wp.data.dispatch('core/block-editor').replaceBlocks(clientId, blocks)` thay vì lệnh `insertBlocks` lỗi thời để giúp khối DOM convert xong giữ nguyên được gốc phả hệ nằm ngay vị trí con đang chèn (nested context).
- [x] **Attribute Isolation:** Chuyển đổi mapping từ `className` sang `tailwindClasses` để đồng bộ với kiến trúc cô lập Style mới của dự án.
- [x] **Inline Style Support:** Hỗ trợ trích xuất `style` từ HTML gốc vào thuộc tính `customStyle` của block (Sử dụng `parseStyle` helper).
- [x] Hỗ trợ full document HTML (tự trích xuất `<body>` class cho root wrapper).
- [x] Bảo toàn semantic tags (`tagName` preservation) cho Container.
- [x] Loại bỏ `<script>`, `<style>`, comments khi import nội dung.

## 4. Bugs đã biết (chưa fix)
- `<a>` trong `<nav>` bị map thành Button thay vì link (Cần bổ sung logic trích xuất Context).
- Nhiều class Tailwind hiếm vẫn chưa được JIT compile (xem `design-engine.md` Roadmap).

## 5. Public JS API
- `window.ska.bridge.convert(html, clientId)`: Hàm chính để thực hiện chuyển đổi, tự động đục khoét thay thế HTML trực tiếp vào tọa độ clientId được truyền vào.

---
*Cập nhật lần cuối: 2026-03-27*
