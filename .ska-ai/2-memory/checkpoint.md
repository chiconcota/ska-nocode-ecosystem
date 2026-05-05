# SYSTEM CHECKPOINT

**Thời điểm lưu:** 2026-05-04 (End Session - Kiến Trúc Smart Virtual Wrapper)

## 1. Trạng thái hiện tại
- **[NGHIÊN CỨU] Kiến trúc Theme Builder vs App Builder:** Phân tích các rủi ro của Output Buffering, xung đột FSE. Thống nhất định hướng "Smart Virtual Wrapper" (Tách rời lũy tiến).
- **[TÀI LIỆU HÓA]** Đã lưu trữ các "tử huyệt" kỹ thuật và hướng đi vào file `.ska-ai/2-memory/chua-quyet-dinh-duoc.md`.

## 2. Nhiệm vụ phiên tiếp theo (Handover)
- **Cài đặt virtual-wrapper.php:** Bắt đầu cài đặt mã nguồn cho `Smart Virtual Wrapper` và nâng cấp `Ska_Template_Router` nếu người dùng quyết định chốt hướng đi App Builder/Theme Builder lai.
- **Kiểm tra tương thích Blocks:** Test các Blocks cũ xem có bị lệch Layout khi nằm trong Ska Query Loop (với kiến trúc Structural Container mới).
- **Phát triển Skapine Engine:** Bắt đầu cơ chế Mocking cho Alpine.js bên trong Iframe của Editor.

## 3. Các files liên đới dự kiến (Phiên sau)
- `wp-content/plugins/ska-no-code-design/src/ska-loop/` (Testing frontend & editor)
- `wp-content/plugins/ska-no-code-design/inc/template-router/virtual-wrapper.php` (Triển khai Smart Virtual Wrapper)
- `wp-content/plugins/ska-no-code-design/assets/js/ska-editor-helper.js` (Skapine Preview Engine)
