# Hướng Dẫn Nâng Cao: Tương Tác & Logic (Alpine Store & SkaFX)

Chào mừng bạn đến với bộ kỹ năng Nâng cao của Ska Ecosystem! Ở khu vực này, chúng ta sẽ học cách làm cho trang Web "sống động" hơn (ví dụ: bấm nút mở Menu phụ, tính toán công thức ẩn/hiện khối) mà vẫn giữ nguyên tiêu chí: **Tuyệt đối không cần học Code Lập Trình**.

---

## 1. Điều Khiển Xuyên Không Gian (Biến Toàn Cục Alpine Store)

Trong thiết kế web, bạn rất hay gặp trường hợp: Nút bấm Burger Menu nằm tít trên đỉnh mây (Header), còn cái Thanh Menu Phụ đổ xuống (Offcanvas) lại nằm dưới đáy biển (Footer). Cả hai khối này chẳng liên quan gì nhau. Làm sao để bấm nút ở trên mà thanh ở dưới chạy ra? Đáp án là dùng **Biến Toàn Cục (Store)**.

### Cách thiết lập 3 bước cực dễ:

**Bước 1: Khởi tạo biến nhớ**
Chọn khối ngoài cùng nhất của trang (Ví dụ khối lớn bao quanh cả Header). Nhìn qua Cột phải (Inspector), mở mục HTML Attributes, thêm dòng sau:
- Tên thuộc tính: `x-init`
- Giá trị: `if (!window.Alpine.store('app')) window.Alpine.store('app', { menuMo: false })`
*(Ý nghĩa: Khai báo ngay lập tức một cái kho tên là 'app', trong đó có biến cất giữ tên 'menuMo' mang giá trị ban đầu là Đóng/False)*

**Bước 2: Gắn công tắc cho Nút Bấm**
Chọn khối Nút bấm Menu ở Header. Thêm HTML Attributes:
- Tên thuộc tính: `@click`
- Giá trị: `$store.app.menuMo = true`
*(Ý nghĩa: Khi chạm vào nút này, chuyển biến menuMo sang Bật/True)*

**Bước 3: Cho Menu phụ "Nghe lệnh"**
Chọn khối Thanh Menu Phụ ở dưới cùng. Thêm HTML Attributes:
- Tên thuộc tính: `x-show`
- Giá trị: `$store.app.menuMo`
*(Ý nghĩa: Thanh này vô hình. Nó chỉ chịu hiện ra khi thằng menuMo là True)*

> **💡 Trải nghiệm Live Preview:** Mở mắt to chiêm ngưỡng sự thần kỳ! Ngay khi bạn vừa gõ xong, bạn có thể Click thẳng vào nút bấm trên màn hình Kéo thả (Gutenberg). Skapine Engine của chúng tôi sẽ cho Menu chạy ra tự động y như Web thật. Đỉnh cao No-code!

---

## 2. Ngôn Ngữ Công Thức SkaFX (Giống Hệt Excel)

Đừng hoảng sợ cụm từ "Ngôn ngữ biểu thức". SkaFX được thiết kế đơn giản hệt như lúc bạn gõ tính tiền trong Excel vậy! Nó dùng để làm 2 việc thần thánh: Ẩn/Hiện một Khối có Điều Kiện, hoặc Bơm một dòng chữ dữ liệu siêu linh động.

### 2.1 Quy tắc gõ cú pháp
- Dữ liệu kéo từ Kho Bảng (Ví dụ lấy từ Ska Data Pro) bắt buộc phải nằm trong ngoặc vuông: `[ten_bang.ten_cot]`
- Dữ liệu so sánh quen thuộc: Lớn hơn `>`, Nhỏ hơn `<`, Bằng dấu `=`, Khác nhau `!=`.
- Các câu chữ tĩnh ghép thêm thì luôn kẹp trong dấy nháy: `"Xin chào"`.

### 2.2 Ứng Dụng 1: Tự biến mất khi hết chỗ (Conditional)
Giả sử bạn làm một trang Đặt Lịch Khám. Bạn chỉ muốn hiện chữ "Đăng Ký Ngay" nếu Phòng Khám còn slot trống.
- Kéo khối Ska Text (Văn bản) ra. Bên tay phải, bật công tắc **"Logic Ẩn Hiện"**.
- Nhập công thức: `[dat_kham.so_cho_trong] > 0`
- Xong! Nút sẽ chỉ hiện nếu rạp còn chỗ. Hết vé? Tự nó tàng hình.

### 2.3 Ứng Dụng 2: Tạo Lời Chào Tự Động (Data Hydration)
Trang web cá nhân hóa không nên ghi sáo rỗng là "Xin chào quý khách", mà phải gọi đúng tên thật.
- Bật công tắc **"Dữ Liệu Động"**.
- Nhập công thức: `CONCAT("Xin chào anh/chị ", [khach_hang.ho_ten])`
- Ngay lập tức trên giao diện, Ska Text sẽ tự đọc Table và thay đổi thành *"Xin chào anh/chị Trần Bình Trọng"*.

### 2.4 Một số hàm tiện lợi (Bê nguyên xi từ Excel)
- `IF(điều kiện, đúng, sai)` : Rẽ nhánh thông minh. 
  - *Ví dụ: `IF([bac_si.diem] > 4, "Bác sĩ giỏi xuất sắc", "Bác sĩ thực tập")`*
- `AND` (Và), `OR` (Hoặc) : Ghép nhiều điều kiện cùng yêu cầu. 
  - *Ví dụ muốn xét khách được nhận quà ưu đãi 1: `[hoa_don.gia] > 100 AND [hoa_don.gia] < 500`*

> **🛡️ Miễn dịch Lỗi 100%:** Điểm đẳng cấp nhất của SkaFX là lỡ bạn dở buồn ngủ gõ sai công thức `IF(abc` tự dưng quên đóng ngoặc, hệ thống sẽ tự động nén nỗi đau vào hư không và hiển thị một cụm rỗng `""`. Website doanh nghiệp của bạn vĩnh viễn không bao giờ bị "Màn hình Trắng Chết Chóc" (Fatal Error) như các nền tảng lập trình cổ điển (VD: mã C++ / Code chay PHP). Thoải mái thử nghiệm, thả ga sáng tạo!
