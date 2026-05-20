# MINI PROJECT MANAGER: DESIGN WORKFLOW APP PORTAL VIEWS

## MỤC TIÊU DỰ ÁN
Nâng cấp và hoàn thiện lõi `class-ska-portal-generator.php`. Đảm bảo tính năng **One-Click App Generator** tự động sinh ra một giao diện (UI) và trải nghiệm người dùng (UX) hoàn chỉnh, chuẩn mực cho cả `List View` và `Detail View` thay vì chỉ xuất ra dữ liệu thô.

---

## BƯỚC 1: THIẾT KẾ CẤU TRÚC LIST VIEW (APP LAYOUT)
*Xây dựng bộ khung (Skeleton) bọc bên ngoài khối Loop dữ liệu.*

- [x] **Giao diện Tiêu đề (Header):** Sinh ra vùng chứa Tiêu đề App (App Label) và nút **Thêm Bản Ghi Mới** ở góc phải màn hình.
- [x] **Khung hiển thị dữ liệu:** Bọc khối dữ liệu vòng lặp vào trong một container có cấu trúc dạng Bảng (Table) hoặc Grid List hiện đại (tuân thủ Tailwind CSS).
- [ ] **Trạng thái Alpine.js:** Khởi tạo Alpine State `x-data="{ showQuickEdit: false, showDeleteConfirm: false }"` ngay tại thẻ gốc của List View để quản lý trạng thái các Modal tĩnh.

## BƯỚC 2: THIẾT KẾ ROW ITEM (ORGANISM CÓ THỂ CLICK)
*Biến mỗi dòng dữ liệu thành một điểm neo tương tác.*

- [x] **Clickable Row:** Đổi thẻ `div` bọc ngoài của Row thành thẻ `a`.
- [x] **Dynamic Link (Liên kết động):** Cấu hình `href` trỏ thẳng về URL của Detail View: `/{portal_slug}/{{id}}/`.
- [x] **UI Hover State:** Bổ sung hiệu ứng hover đổi màu nền, cùng một icon (Ví dụ: Mũi tên hướng phải) để gợi ý người dùng có thể click vào xem chi tiết.

## BƯỚC 3: THIẾT KẾ MODAL QUICK EDIT (THÊM MỚI NHANH)
*Tối ưu trải nghiệm thao tác nhanh ngay trên màn hình List View.*

- [ ] **Tạo Modal Container:** Tự động sinh ra khối giao diện Modal (Fixed, Overlay mờ) ẩn hiện theo biến `showQuickEdit`.
- [ ] **Lọc Trường Dữ Liệu:** Khi quét Schema của bảng, nhận diện và **lọc bỏ hoàn toàn** các trường có kiểu `long_text` (Mô tả chi tiết, nội dung bài viết, v.v.).
- [ ] **Khởi tạo Form:** Đưa các trường cơ bản (Text, Number, Date, Select) vào một `ska-builder/form`.
- [ ] **Hành vi Hủy (Cancel):** Nút Hủy đóng Modal (`@click="showQuickEdit = false"`).
- [ ] **Hành vi Lưu (Submit):** Cấu hình `formActionId` gọi API Insert. (Cần chốt luồng xử lý Reload danh sách sau khi Lưu thành công).

## BƯỚC 4: THIẾT KẾ DETAIL VIEW (TRANG CHI TIẾT)
*Không gian làm việc đầy đủ dành cho một bản ghi cụ thể.*

- [x] **Breadcrumb / Nút Quay Lại:** Tự động sinh nút điều hướng "← Quay lại danh sách" ở góc trên cùng bên trái.
- [x] **Bố cục Form (Form Layout):** Khởi tạo Form chứa toàn bộ các trường dữ liệu của Schema.
- [x] **Xử lý Long Text (Rich Text):** Đưa các trường `long_text` xuống dưới cùng, trình bày dưới dạng khối soạn thảo (Rich Text Editor/Textarea) mở rộng 100% chiều rộng (col-span full).
- [x] **Nút Hành Động:** Nút "Lưu Thay Đổi" (Update) gắn kèm logic cập nhật lại bản ghi có ID tương ứng.

## BƯỚC 5: KIỂM THỬ THỰC TẾ & KHỚP NỐI SKA LOGIC
- [x] Chạy lại quy trình xóa App Portal và kích hoạt One-Click App Generator.
- [x] Truy cập Frontend, kiểm tra giao diện CSS có bị vỡ hay không.
- [ ] Test luồng Click mở Modal -> Nhập liệu -> Submit. Đảm bảo dữ liệu ghi đúng xuống DB.
- [ ] Test click vào một Row -> Mở trang Detail View -> Chỉnh sửa -> Lưu. Đảm bảo bản ghi cập nhật thành công.

---
*Tài liệu này được sinh ra để quản lý tiến độ cho mini-project thiết kế UI/UX Generator.*
