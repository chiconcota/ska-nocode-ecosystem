# MINI PROJECT MANAGER: DESIGN WORKFLOW APP PORTAL VIEWS

## MỤC TIÊU DỰ ÁN
Nâng cấp và hoàn thiện lõi `class-skaaa-portal-generator.php`. Đảm bảo tính năng **One-Click App Generator** tự động sinh ra một giao diện (UI) và trải nghiệm người dùng (UX) hoàn chỉnh, chuẩn mực cho cả `List View` và `Detail View` thay vì chỉ xuất ra dữ liệu thô.

---

## BƯỚC 1: THIẾT KẾ CẤU TRÚC LIST VIEW (APP LAYOUT)
*Xây dựng bộ khung (Skeleton) bọc bên ngoài khối Loop dữ liệu.*

- [x] **Giao diện Tiêu đề (Header):** Sinh ra vùng chứa Tiêu đề App (App Label) và nút **Thêm Bản Ghi Mới** ở góc phải màn hình.
- [x] **Khung hiển thị dữ liệu:** Bọc khối dữ liệu vòng lặp vào trong một container có cấu trúc dạng Bảng (Table) hoặc Grid List hiện đại (tuân thủ Tailwind CSS).
- [x] **Trạng thái Alpine.js:** Khởi tạo Alpine State `x-data="{ showQuickEdit: false, showDeleteConfirm: false }"` ngay tại thẻ gốc của List View để quản lý trạng thái các Modal tĩnh. (Hủy bỏ Quick Edit, dùng trang tạo mới riêng)

## BƯỚC 2: THIẾT KẾ ROW ITEM (ORGANISM CÓ THỂ CLICK)
*Biến mỗi dòng dữ liệu thành một điểm neo tương tác.*

- [x] **Clickable Row:** Đổi thẻ `div` bọc ngoài của Row thành thẻ `a`.
- [x] **Dynamic Link (Liên kết động):** Cấu hình `href` trỏ thẳng về URL của Detail View: `/{portal_slug}/{{id}}/`.
- [x] **UI Hover State:** Bổ sung hiệu ứng hover đổi màu nền, cùng một icon (Ví dụ: Mũi tên hướng phải) để gợi ý người dùng có thể click vào xem chi tiết.

## BƯỚC 3: [ĐÃ HỦY] THIẾT KẾ MODAL QUICK EDIT
*Quyết định ngày 21/05: Hủy bỏ thiết kế Modal ẩn trong DOM để tránh làm phình (bloat) bộ nhớ trình duyệt khi bảng dữ liệu có nhiều dòng. Tuân thủ tuyệt đối triết lý Dedicated Page (Zero-Trash).*
- [x] Chuyển đổi nút "Thêm Bản Ghi Mới" tại List View thành một liên kết điều hướng đơn giản (`<a href="/{portal_slug}/create">`) trỏ sang một trang Dedicated Create View ở giai đoạn sau.

## BƯỚC 4: THIẾT KẾ DETAIL VIEW (TRANG CHI TIẾT)
*Không gian làm việc đầy đủ dành cho một bản ghi cụ thể.*

- [x] **Breadcrumb / Nút Quay Lại:** Tự động sinh nút điều hướng "← Quay lại danh sách" ở góc trên cùng bên trái.
- [x] **Bố cục Form (Form Layout):** Khởi tạo Form chứa toàn bộ các trường dữ liệu của Schema.
- [x] **Xử lý Long Text (Rich Text):** Đưa các trường `long_text` xuống dưới cùng, trình bày dưới dạng khối soạn thảo (Rich Text Editor/Textarea) mở rộng 100% chiều rộng (col-span full).
- [x] **Nút Hành Động:** Nút "Lưu Thay Đổi" (Update) gắn kèm logic cập nhật lại bản ghi có ID tương ứng.

## BƯỚC 5: KIỂM THỬ THỰC TẾ & KHỚP NỐI SKAAA LOGIC
- [x] Chạy lại quy trình xóa App Portal và kích hoạt One-Click App Generator.
- [x] Truy cập Frontend, kiểm tra giao diện CSS có bị vỡ hay không.
- [x] Test luồng Click nút Thêm Mới -> Chuyển sang Create View -> Nhập liệu -> Submit. Đảm bảo dữ liệu ghi đúng xuống DB.
- [x] Test click vào một Row -> Mở trang Detail View -> Chỉnh sửa -> Lưu. Đảm bảo bản ghi cập nhật thành công.
- [x] Tích hợp và kiểm thử tính năng Xóa Dòng (Row Deletion) an toàn trực tiếp trên List View (tránh lỗi Nesting Tag, có hiệu ứng transition mượt mà khi xóa record).


---
*Tài liệu này được sinh ra để quản lý tiến độ cho mini-project thiết kế UI/UX Generator.*
