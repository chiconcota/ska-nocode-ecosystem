# CHECKPOINT: BÀN GIAO PHIÊN (2026-04-21)

## Trạng Thái (Status)
- Đã giải quyết triệt để lỗi "Block rendered as empty" của khối `ska-organism-ref` bên trong Editor. 
- Nguyên nhân lõi là do cơ chế Output Buffering `ob_start()` của WP > 6.1 khi gọi từ `render.php` trong `block.json`, buộc phải dùng `echo` thay vì `return`.
- Đồng thời fix lỗi REST API 400 Bad Request bằng cách định nghĩa đầy đủ Schema cho các attributes phức tạp (`htmlAttributes`, `logic`).

## Yêu Cầu Của User Cho Phiên Tới
- **Bắt đầu lập trình 2 tính năng chỉnh sửa nội tuyến cho khối "Ska Organism Reference" trên thanh Toolbar/Inspector:**
   1. **Nút "Sửa Mẫu Gốc" (Edit Source):** Nút này khi ấn vào sẽ mở tab chỉnh sửa Organism gốc.
   2. **Nút "Phân Rã Khối" (Detach/Unlink):** Nút này sẽ biến đổi (Transform) khối `ska-organism-ref` thành các khối Gutenberg riêng lẻ (Ska Container, Button, Text...) để người dùng tự do tuỳ biến trên trang hiện tại mà không ảnh hưởng tới Organism gốc.

## Code Path Liên Quan
- `wp-content/plugins/ska-no-code-design/src/ska-organism-ref/edit.js`
- `wp-content/plugins/ska-no-code-design/src/ska-organism-ref/block.json`

## Dặn dò Agent Phiên Sau
- Khi quay lại, đọc `checkpoint.md` này và bắt đầu luôn vào việc implement nút "Edit Source" và "Detach" trên React Block Controls.
- Hãy tham khảo mã nguồn của khối Reusable Block của WP core để làm tính năng "Detach" (Tách inner HTML parse thành blocks raw).
