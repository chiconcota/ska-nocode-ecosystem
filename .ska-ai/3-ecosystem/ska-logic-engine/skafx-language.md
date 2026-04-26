# TÀI LIỆU HƯỚNG DẪN SỬ DỤNG NGÔN NGỮ SKAFX (Ska Expression Language)
@version: 1.0.0
@target: Ska Logic Engine & Ska Data Pro

## 1. TỔNG QUAN
**SkaFX** (Ska Expression Language) là ngôn ngữ biểu thức lõi (Domain Specific Language) được thiết kế chuyên biệt cho hệ sinh thái Ska App Builder. Nó được bộ phân giải AST (Abstract Syntax Tree) bằng PHP trong máy chủ xử lý, mang lại tốc độ biên dịch ánh sáng (0ms) và độ an toàn tuyệt đối.

**Ứng dụng thực tiễn của SkaFX:**
1. **Universal Dynamic Binding (Data Hydration):** Bơm dữ liệu từ Database lên Frontend (thay thế thẻ `{{...}}` cũ).
2. **Conditional Block Display:** Quyết định việc một Khối Element (Block UI) có được hiển thị hay bị xóa sổ khỏi giao diện dựa trên trả về `True/False`.
3. **Formula Column (Ska Data Pro):** Xử lý công thức toán học chéo (nhân, chia, cộng, trừ) ảo cho DataGrid.

---

## 2. CÚ PHÁP CƠ BẢN (SYNTAX)

Cú pháp của SkaFX được thiết kế tương đồng với **Excel** và **Airtable**, giúp mọi người dùng No-code làm quen tức thì mà không cần kiến trúc Code phức tạp.

### 2.1. Biến Số Phụ Thuộc (Variables & Fields)
Trong kiến trúc Smart Object, dữ liệu từ Cơ sở dữ liệu (Database MySQL) BẮT BUỘC phải được đặt trong **Dấu Ngoặc Vuông `[]`**.
- **Tuyệt đối địa chỉ (`[app.table.field]`):** Bao gồm Tên App, Tên Bảng, và Tên Cột (VD: `[clinic.doctors.name]`, `[clinic.doctors.rating]`). Điều này giúp phân biệt rõ ràng nếu hệ thống có nhiều khối ứng dụng khác nhau.
- **Tương đối địa chỉ (`[table.field]` hoặc `[field]`):** Nếu tham chiếu ở ngữ cảnh tĩnh thì tự động lấy bảng hiện tại dựa trên Ngữ Cảnh URL (Ví dụ ID record hiện hành).

### 2.2. Kiểu Dữ Liệu Hằng Số (Literals)
- **Chuỗi văn bản (String):** Dùng nháy kép hoặc nháy đơn. VD: `"Xin chào"`, `'ABC'`, `""` (Chuỗi rỗng).
- **Số học (Number):** Viết trực tiếp. VD: `45`, `3.14`
- **Boolean / Null:** `true`, `false`, `null` (Sử dụng trực tiếp trong biểu thức).

### 2.3. Các Phép Toán & So Sánh (Operators)
- **Toán Học:** `+` (Cộng), `-` (Trừ), `*` (Nhân), `/` (Chia)
- **So Sánh:** `==` hoặc `=` (Bằng nhau), `!=` (Khác nhau), `>` (Lớn hơn), `<` (Nhỏ hơn), `>=` (Lớn hơn hoặc bằng), `<=` (Nhỏ hơn hoặc bằng)
- **Logic:** `AND` hoặc `&&`, `OR` hoặc `||`

---

## 3. KHAI BÁO BIẾN CỤC BỘ (STATEMENTS)
Một điểm mạnh vượt trội của SkaFX là khả năng chạy Lập trình kịch bản (Scripting) thay vì chỉ gõ một dòng Formula vô cảm. 

Mọi lệnh khai báo biến bắt đầu bằng từ khóa `var`, kết thúc bởi dấu chẩm phẩy `;`. Dòng cuối cùng không có gán biến sẽ làm Kết Quả Trả Về (Return Value).

```js
var giam_gia = 10;
var gia_goc = [ecommerce.products.price];
var thanh_tien = gia_goc - giam_gia;

// Dòng cuối cùng được AST hiểu là kết quả trả về của biểu thức
thanh_tien > 0;
```

---

## 4. CÁC HÀM TÍCH HỢP SẴN (BUILT-IN FUNCTIONS)

SkaFX cung cấp sẵn các nhóm hàm tiêu chuẩn giống Excel. Tham số truyền vào được phân tách bằng dấu phẩy.

### 4.1. Hàm Logic
* Dùng cho khối Conditional Binding để phán quyết Ẩn/Hiện.

**`IF(điều_kiện, kết_quả_đúng, kết_quả_sai)`**
- Mẫu: `IF( [clinic.doctors.rating] > 4, "Bác sĩ giỏi", "Lưu ý" )`

### 4.2. Hàm Chuỗi Văn Bản
* Dùng để dính chữ, cắt chữ trên Frontend.

**`CONCAT(chuoi_1, chuoi_2, ...)`**
- Mẫu: `CONCAT("Bs. ", [clinic.doctors.name])` -> *Kết quả: Bs. Nguyễn Văn A*

### 4.3. Động Cơ Xóa Dummy Text Bằng Chuỗi Rỗng
* Không dùng hàm, sử dụng tính năng Ngầm (Implicit) của Universal Binding.
* Nếu biểu thức SkaFX tính ra kết quả là **Chuỗi rỗng (`""`)**, hệ thống sẽ lập tức đè chuỗi rỗng này lên văn bản chữ giả (Dummy text) đang hiển thị của Element, làm khối đó mất nội dung và có thể bị co lại bằng 0px.
* Rất hữu hiệu kết hợp với hàm IF:
- Mẫu: `IF([clinic.doctors.rating] < 3, "Có Cảnh Báo", "")` -> *Nếu rating >= 3, Element Text sẽ biến mất bằng chữ rỗng thay vì ném lỗi.*

### 4.4. Các hàm tương lai (Roadmap)
- `ROUND(number, decimals)`
- `SUM(array)` (Tính tổng cho Rollup Relation)

---

## 5. CƠ CHẾ BẢO VỆ "NUỐT LỖI" VÀ SMART FALLBACK
Do SkaFX được Evaluator phân tích ở tầng máy chủ (Backend), nên nếu Tác giả gõ sai cú pháp (Ví dụ thiếu dấu ngoặc: `IF([name] = 2`), bộ máy PHP Cốt lõi của Ska Logic Engine tuyệt đối sẽ:
1. **KHÔNG** làm White Screen of Death (Sập trình duyệt hay crash PHP).
2. **KẾT QUẢ:** Tự động nuốt lỗi vào hư không, trả về Rỗng `""` hoặc `false` không điều kiện để vượt rào (Fallback).

### 5.1. Smart Fallback Cho Văn Bản Tĩnh (Literal String) & Template
Đặc biệt trong các Action Node (như DB Action, Set Data), nếu người dùng Nocode điền một chuỗi văn bản không có nháy kép (VD: `khách lẻ` hoặc `vip`) hoặc chuỗi pha trộn (VD: `Khách hàng [hoten]`), SkaFX sẽ:
1. Đánh giá xem chuỗi đó có hợp lệ là mã SkaFX hay không.
2. Nếu xảy ra lỗi cú pháp (Syntax Error) hoặc trả về `null` do không tìm thấy biến `vip`, bộ máy sẽ tự động kích hoạt **Smart Fallback**.
3. **Smart Fallback** sẽ quét chuỗi để thay thế các tham số trong ngoặc vuông (VD: `[hoten]`) bằng giá trị thực tế từ `$payload`, và giữ nguyên văn bản tĩnh còn lại.
4. Điều này giúp người dùng Nocode có thể gõ trực tiếp nội dung tĩnh vào ô cấu hình mà không bị ép buộc phải hiểu về quy tắc "chuỗi phải nằm trong cặp dấu ngoặc kép" của Lập trình viên.

---

## 6. VÍ DỤ ỨNG DỤNG THỰC TẾ TRONG SKA BUILDER

### Ví dụ 1: Ẩn/Hiện Nút Đặt Lịch (Universal Dynamic Binding)
Người dùng Bật công tắc "Logic Điều kiện" lên Form Đặt lịch, và điền 1 biểu thức:
```js
[booking.events.status] = "Sắp diễn ra" AND [booking.events.slots] > 0
```
*(Chỉ render nút nếu Trạng thái còn hạn và Slot đặt chỗ còn trống).*

### Ví dụ 2: Hiển thị lời chào cá nhân bằng Data Hydration
Lễ tân thả Ska Text Block vào, không gõ chữ vô nghĩa nữa mà điền vào cột Data Source biểu thức:
```js
CONCAT("Chào mừng ", [core.users.fullname], ", bạn đang có ", [core.users.points], " điểm tích lũy.")
```
