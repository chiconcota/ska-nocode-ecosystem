# E2E Test Workflow: Pure Render Template Node & Testing Sandbox (v1.1.6)

> [!NOTE]
> Tài liệu này cung cấp các kịch bản kiểm thử E2E (End-to-End) chi tiết để bạn tự tay xác minh hoạt động của tính năng Pure Render Template Node cùng bộ sandbox Live Testing & Preview vừa tích hợp trên giao diện thiết kế DAG Workflow.

---

## 🧪 Các ca kiểm thử (Test Cases)

### Ca 1: Kiểm thử Giao diện & Live Preview Sandbox trên UI Canvas

**Các bước thực hiện:**
1. Truy cập trang quản trị DAG Workflow của **Ska Logic Engine** (Ví dụ: `WP Admin -> Ska Builder -> Workflows`).
2. Kéo thả một node **Render Template** từ Sidebar bên trái vào màn hình Canvas.
3. Click chuột trái vào node **Render Template** vừa tạo để mở bảng cấu hình **Settings Panel** ở phía bên phải.
4. Tại ô nhập **Template HTML / Variable**, hãy điền một đoạn HTML mẫu có chứa các tag Mustache (SkaFX):
   ```html
   <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
     <h3 class="text-blue-700 font-bold">Hello {{ payload.user.name }}!</h3>
     <p class="text-sm">Email: {{ payload.user.email }}</p>
     <p class="text-xs text-slate-500 mt-2">Promo: {{ payload.promo_code }}</p>
   </div>
   ```
5. Tại ô **Result Variable**, giữ nguyên giá trị mặc định: `payload.rendered_template`.
6. Tại phân hệ **Live Testing & Preview** ở phía dưới:
   - Sửa đổi dữ liệu JSON trong ô **Mock Payload (JSON)** như sau:
     ```json
     {
       "payload": {
         "user": {
           "name": "Nguyen Van A",
           "email": "a@example.com"
         },
         "promo_code": "SKANOCODE"
       }
     }
     ```
   - Hãy thử sửa `"name": "Nguyen Van A"` thành `"name": "Lý Tất Thành"`.
7. Cố ý xóa một dấu ngoặc kép hoặc gõ sai định dạng JSON trong Mock Payload.
8. Click chuyển đổi sang tab **HTML Output** ở phần Preview.

**1. Điều kiện vượt qua bài test (Pass Criteria):**
- Bảng Settings Panel tự động nới rộng chiều rộng khi chọn node Render Template.
- Khung preview hiển thị nội dung trực quan và phản hồi tức thì với thay đổi trong Mock Payload.
- Trình biên dịch không bị crash và hiển thị thông báo lỗi chi tiết khi JSON sai cú pháp.

**2. Kết quả kỳ vọng (Expected Results):**
- Bảng sidebar mở rộng từ `w-80` (320px) sang `w-[400px]` (400px) một cách mượt mà thông qua hiệu ứng transition CSS.
- Khung **Visual Preview** hiển thị giao diện xanh dương cùng tên đã được cá nhân hóa: "Hello Nguyen Van A!" (hoặc "Hello Lý Tất Thành!" sau khi sửa).
- Nhập sai JSON hiển thị thông báo lỗi màu đỏ ngay dưới khung nhập: `⚠️ Lỗi JSON: Unexpected token ...`.
- Tab **HTML Output** hiển thị chính xác chuỗi HTML thô đã được nội suy.

**3. Lỗi thao tác người dùng & Cách fix nhanh:**
- *Triệu chứng*: Khung **Visual Preview** bị méo mó, vỡ layout hoặc hiển thị trắng xóa.
  - *Nguyên nhân*: Người dùng gõ sai cú pháp HTML (ví dụ: mở thẻ div `<div>` nhưng quên đóng `</div>`, hoặc gõ nhầm thẻ đóng).
  - *Fix nhanh*: Kiểm tra kỹ lại mã HTML trong ô nhập, hoặc dán một đoạn HTML chuẩn khác để thử lại.
- *Triệu chứng*: Khung JSON báo lỗi đỏ liên tục, không nhận biến.
  - *Nguyên nhân*: Lỗi cú pháp JSON (ví dụ: dùng nháy đơn `'` thay vì nháy kép `"`, thiếu dấu phẩy `,` giữa các dòng hoặc thừa dấu phẩy ở phần tử cuối cùng).
  - *Fix nhanh*: Quan sát lỗi đỏ mô tả dòng bị lỗi, sửa các chuỗi và key về dấu nháy kép `""`, đảm bảo đúng định dạng JSON chuẩn.

---

### Ca 2: Kiểm thử cơ chế Two-Pass Interpolation (HTML động từ biến)

**Các bước thực hiện:**
1. Giả lập trường hợp bạn truy vấn DB Query trước đó và lấy về mã HTML động, lưu trữ vào biến `payload.db_result.html_content`.
2. Trong ô **Mock Payload (JSON)**, nhập nội dung sau:
   ```json
   {
     "payload": {
       "user": {
         "name": "Alex Johnson",
         "email": "alex@example.com"
       },
       "db_result": {
         "html_content": "<div class=\"p-4 bg-emerald-50 border border-emerald-200 rounded-xl\">\n  <h4 class=\"text-emerald-800 font-semibold\">Coupon code active: {{ payload.coupon }}</h4>\n  <p>User: {{ payload.user.name }} ({{ payload.user.email }})</p>\n</div>"
       },
       "coupon": "WELCOME50"
     }
   }
   ```
3. Sửa đổi ô nhập **Template HTML / Variable** thành:
   `{{ payload.db_result.html_content }}`

**1. Điều kiện vượt qua bài test (Pass Criteria):**
- Biến cấp 1 (`db_result.html_content`) được giải mã ra chuỗi HTML thô.
- Các biến cấp 2 nằm bên trong chuỗi HTML đó (`coupon`, `user.name`, `user.email`) tiếp tục được nội suy thành công ở lượt quét thứ hai.

**2. Kết quả kỳ vọng (Expected Results):**
- Khung **Visual Preview** kết xuất chính xác box giao diện màu xanh lá cây với các thông số: "Coupon code active: WELCOME50", "User: Alex Johnson (alex@example.com)".

**3. Lỗi thao tác người dùng & Cách fix nhanh:**
- *Triệu chứng*: Kết quả hiển thị trống rỗng hoặc chỉ in ra chữ `{{ payload.db_result.html_content }}` thô.
  - *Nguyên nhân*: Người dùng viết sai tên biến trong ô cấu hình (ví dụ: gõ nhầm thành `{{ payload.db_result.html }}`) hoặc JSON mock khai báo cấu trúc không khớp.
  - *Fix nhanh*: Đối chiếu chính xác tên và đường dẫn của biến giữa ô cấu hình và cấu trúc JSON mock.
- *Triệu chứng*: HTML hiển thị nhưng các biến con vẫn giữ nguyên chữ dạng `{{ payload.coupon }}` mà không thay đổi.
  - *Nguyên nhân*: JSON mock thiếu các trường con (`coupon` hoặc `user.name`), hoặc cú pháp viết hoa/thừa dấu cách trong dấu ngoặc kép.
  - *Fix nhanh*: Kiểm tra lại cấu trúc Mock JSON, đảm bảo có khai báo đầy đủ các biến con được sử dụng trong chuỗi HTML động.

---

### Ca 3: Kiểm thử Lưu & Tải lại Workflow (State Persistence)

**Các bước thực hiện:**
1. Kết nối các node trên Canvas bằng Edge (Ví dụ: Trigger -> Render Template -> Client Response).
2. Nhấp nút **Save Workflow** ở thanh điều khiển của Logic Builder.
3. F5/Reload lại trang trình duyệt.
4. Click chọn lại node Render Template.

**1. Điều kiện vượt qua bài test (Pass Criteria):**
- Biểu đồ và thông số cấu hình của node được lưu giữ nguyên vẹn trên database phẳng.
- Tải lại trang không phát sinh lỗi script.

**2. Kết quả kỳ vọng (Expected Results):**
- Các cấu hình của trường `template_html` và `result_var` vẫn hiển thị chính xác như trước khi reload.
- Tab Console của trình duyệt không xuất hiện lỗi JavaScript đỏ.

**3. Lỗi thao tác người dùng & Cách fix nhanh:**
- *Triệu chứng*: Khi tải lại trang, node Render Template bị mất hết các cấu hình vừa điền.
  - *Nguyên nhân*: Người dùng F5/Reload trình duyệt trước khi nhấn nút **Save Workflow** ở dashboard, dẫn đến dữ liệu tạm thời chưa được đẩy xuống CSDL MySQL.
  - *Fix nhanh*: Đảm bảo luôn bấm nút **Save Workflow** (hoặc Ctrl+S) và thấy thông báo lưu thành công trước khi reload trang.
- *Triệu chứng*: Bấm Save Workflow bị lỗi đỏ hệ thống hoặc không phản hồi.
  - *Nguyên nhân*: Người dùng đưa các đoạn mã JS nguy hại (thẻ `<script>`) hoặc thẻ HTML lỗi quá nặng vào ô nhập khiến hệ thống bảo mật của server (như ModSecurity) chặn request vì nghi ngờ tấn công XSS.
  - *Fix nhanh*: Loại bỏ các thẻ script tự chế, chỉ dùng mã HTML/CSS hiển thị giao diện thông thường.

---

### Ca 4: Kiểm thử Tương thích ngược (Backward Compatibility)

**Các bước thực hiện:**
1. Mở một Workflow cũ đã tạo từ các phiên bản trước (sử dụng cấu hình `raw_template` hoặc `organism_id`).
2. Click vào node Render Template để xem cấu hình.

**1. Điều kiện vượt qua bài test (Pass Criteria):**
- Dữ liệu cấu hình cũ được hệ thống tự động nhận diện và nạp bình thường mà không gây lỗi hoặc sập giao diện.

**2. Kết quả kỳ vọng (Expected Results):**
- Giao diện mới tự động nạp giá trị từ `raw_template` hoặc `organism_id` cũ đổ thẳng vào trường **Template HTML / Variable** trên UI để người dùng tiếp tục chỉnh sửa.

**3. Lỗi thao tác người dùng & Cách fix nhanh:**
- *Triệu chứng*: Mở workflow cũ lên nhưng ô nhập `Template HTML / Variable` bị trống.
  - *Nguyên nhân*: Node cũ có thể chưa được cấu hình bất kỳ dữ liệu nào từ trước (mới chỉ kéo thả node rỗng).
  - *Fix nhanh*: Tiến hành nhập cấu hình HTML mới vào và nhấn **Save Workflow** để lưu cấu trúc dữ liệu chuẩn cho node Render Template.
