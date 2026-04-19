# Hướng Dẫn Sử Dụng Hệ Sinh Thái Ska (No-Code App Builder)

**Chào mừng bạn đến với Ska Ecosystem!** Tài liệu này dành riêng cho người thiết kế (End-User). Hệ thống cung cấp cho bạn một công cụ toàn diện từ A-Z để tùy ý kéo thả ra một Website hoặc Web App cực chất mà không yêu cầu bạn phải biết code (Lập trình).

---

## 1. Thiết Kế Trực Quan (Ska No-Code Design)
Chúng tôi trang bị cho bạn không gian Thiết kế kéo thả ngay trong trình Editor mặc định chuẩn.

### 1.1 Khối Nguyên Tử (Atomic Blocks)
Gõ lệnh `/ska` hoặc bấm biểu tượng dấu `[+]` quen thuộc. Bạn sẽ có các "Khối nguyên tử" cơ bản:
*   **Ska Container:** Đóng vai trò như các thùng chứa (Carton). Bạn có thể xếp các Container vuông góc hoặc san sát nhau.
*   **Ska Text & Ska Image:** Dành cho việc hiển thị Văn bản và Hình ảnh.
*   **Ska Button & Ska Icon:** Tạo nút bấm kêu gọi hành động với thư viện 4000+ Icon miễn phí.

### 1.2 "Sơn Màu" bằng Tailwind CSS
Nếu bạn đã biết qua Tailwind, đây là một hệ quyền lực vô địch. Phía bên phải màn hình (Inspector), bạn sẽ thấy phần **Tailwind Classes**. Ở đó bạn có thể điền:
*   `text-red-500` (để tô màu chữ).
*   `bg-slate-900 border border-slate-700` (để làm Box nền đen tối cực ngầu).
*   `rounded-xl shadow-2xl` (tạo góc bo và viền bóng cực nét).
*   `p-6 flex items-center justify-between` (để căn lề thẩm mỹ ngay lập tức).

### 1.3 Sao Chép Giao Diện từ Internet (Ska Bridge)
Bạn lướt Web thấy có 1 mẫu giao diện (Tailwind) cực kỳ đẹp mắt? Rất đơn giản:
*   Dùng chức năng **Copy HTML**.
*   Nhúng block **Ska Import (HTML to Tailwind)** vào trang của bạn.
*   Dán code HTML đó vào. Công cụ tự động bóc và rã mã Code đó thành các khối Ska Container/Text thật cho bạn chỉnh sửa tay ở cấp độ kéo thả! 

---

## 2. Quản Trị Cột Dữ Liệu (Ska Data Pro)
Bạn đang làm web Bất Động Sản? Đừng cố nhồi nhét Căn hộ vào các Bài viết Tin tức (Posts) tĩnh!

### Làm việc như Excel
*   Trên Admin Menu, chọn **App Data**.
*   Bấm **+ Tạo Căn Hộ**. Bạn vừa tự tạo một kho dữ liệu ngầm ở Database, mạnh ngang một hệ quản trị!
*   Thêm tuỳ ý các **Cột Nhập Liệu** (Tên, Hình ảnh, Giá tiền, Bật/Tắt).
*   Lập tức màn hình sẽ hoá thành Giao diện Cột Lưới. Bạn nhấp chuột thẳng vào từng ô để điền chữ, y hệt Google Sheets hoặc Airtable mà không cần tải lại trang.

---

## 3. Tạo Biểu Mẫu Nhập Thông Tin (Ska Logic Engine)
Bạn muốn khách viếng thăm Website điền thông tin Đặt mua hoặc Để lại SĐT liên hệ?

*   **Bước 1:** Kéo một khối **Ska Form** ra màn hình.
*   **Bước 2:** Ném vào bên trong đó các cục **Ska Input** (Ví dụ: đặt tên trường là `ho_ten`) và Nút bấm Submit.
*   **Bước 3 (Ma Thuật!):** Click vào khối Ska Form, tìm phần cấu hình Logic, bật công tắc **Form Dữ Liệu**. Chọn kho Data mà bạn vừa tạo bên Ska Data Pro lúc nãy.
*   **Điều Kỳ Diệu:** Từ nay khách bấm Submit là Data tự chảy vào cái kho Excel đó. Tuyệt đối không cần code kỹ xảo rườm rà. Hệ thống cực kỳ thông minh tự bọc bảo mật cho bạn.

---

## 4. Tầm Nhìn Kế Tiếp (Ska Symbols - Tái Sử Dụng)
Chức năng Tái Sử Dụng đang được triển khai. Sắp tới, khi bạn làm xong một thanh Navigation đẹp mắt, bạn chỉ việc chuột phải dán nhãn *"Lưu thành Ska Symbol"*. Thanh điều hướng đó sẽ về kho lưu trữ đồ chung. Bạn thả nó xuống 200 trang con, khi có lỗi muốn đổi Logo, bạn chỉ cần mở kho sửa đúng 1 chỗ là 200 trang con kia tự cập nhật thay đổi theo thời gian thực!

> **✨ Chúc bạn có trải nghiệm tuyệt vời cùng Ska Ecosystem! Giao diện do bạn vẽ, Dữ liệu do bạn gom - Chào mừng đến với No-code.**
