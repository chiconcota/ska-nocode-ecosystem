# [HOÀN THÀNH - 2026-04-29] Quy Trình Kiểm Thử (End-to-End) Node DB Query & Render Template

## Mục tiêu
Sau khi hoàn thiện tính năng **DB Query Node**, quy trình này được tạo ra để:
- Xác minh Node có khả năng truy vấn dữ liệu từ DB (Select) chính xác theo các điều kiện lọc (Where).
- Kiểm tra tính năng nội suy biến SkaFX (VD: `[payload.abc]`) bên trong các điều kiện truy vấn.
- Đảm bảo dữ liệu trả về (Return Type: Object/Array) được lưu trữ đúng vào biến (Result Variable) và sẵn sàng cho các Node phía sau.
- Đánh giá UX/UI của bảng SettingsPanel: Chọn bảng (Table Picker), chọn Operator, và thêm bớt Condition có dễ dàng và mượt mà cho No-code Developer không.

---

## Quy trình thực hiện (DB Query Node)

### Bước 1: Chuẩn bị Database và Dữ liệu mẫu (Ska Data Pro)
- **Thao tác:** Dùng Smart Object tạo một bảng dữ liệu người dùng (Ví dụ: `users`).
- **Tạo dữ liệu mẫu:** Chèn các bản ghi sau vào bảng để làm dữ liệu đối chiếu:
  1. Nguyễn Thị Kiều Nga (năm sinh: 1993)
  2. Lý Tất Thành (năm sinh: 1991)
  3. Lý Nguyễn Bình An (năm sinh: 2020)
  4. Lý Tất Thành 2 (năm sinh: 1991)
  5. Lý Tất Thành (năm sinh: 1991)
  6. Người Già (năm sinh: 1975)
> **✅ Pass khi:** 
> - Bảng được tạo thành công, chứa đúng bộ dữ liệu mẫu thực tế như trên.

### Bước 2: Tạo Workflow tích hợp DB Query
- **Thao tác:** Mở DAG Builder, kéo thả các Node: `Trigger`, `Set Data`, `DB Query` và `Client Response`. Nối dây chúng lại với nhau.
- **Cấu hình Set Data Node (Giả lập Input Tìm Kiếm):**
  - Đặt Key: `search_year`.
  - Đặt Value: `1991`.
- **Cấu hình DB Query Node:**
  - **Bảng (Table):** Chọn bảng `users` vừa tạo ở Bước 1.
  - **Return Type:** Chọn `Nhiều dòng (Array)`.
  - **Where Conditions:** Thêm điều kiện: cột `nam_sinh`, operator `=`, value `[payload.search_year]`.
  - **Order By:** `id` | **Direction:** `DESC`.
  - **Result Variable:** Nhập `ket_qua_tim_kiem`.
- **Cấu hình Client Response (Log kết quả):**
  - Hành động: Hiển thị thông báo (Toast/Notification).
  - Nội dung: `Tìm thấy {{ ket_qua_tim_kiem.length }} người sinh năm 1991!` (Dùng SkaFX để đọc độ dài mảng).
> **✅ Pass khi:**
> - Bảng Settings của DB Query hiển thị đầy đủ, không bị lỗi UI.
> - Thao tác chọn bảng, thêm điều kiện Where mượt mà, không lag.
> - Việc chèn biến `[payload.search_year]` dễ hiểu và không gây nhầm lẫn.

### Bước 3: Kích hoạt Workflow và Xác thực Dữ liệu
- **Thao tác:** Kích hoạt Trigger (Submit form ngoài frontend hoặc gọi API giả lập).
- **Kiểm tra Logs/Network:** Mở tab Network trên trình duyệt hoặc xem file log backend để kiểm tra quá trình chạy.
> **✅ Pass khi:**
> - Luồng Logic chạy thành công không báo lỗi.
> - Hệ thống Notification trên Frontend báo chính xác thông báo: **"Tìm thấy 3 người sinh năm 1991!"** (Đúng khớp với dữ liệu mẫu là id 3, 5, 6).
> - Backend sinh ra câu SQL hợp lệ (đã được *prepare* chống SQL Injection).
> - Mảng dữ liệu kết quả thực sự tồn tại trong biến `payload.ket_qua_tim_kiem` và có thể dùng tiếp cho các node bên dưới.

---

# Quy Trình Kiểm Thử (End-to-End) Node Render Template trên Ska Logic Engine

## Mục tiêu
Sau khi hoàn thiện tính năng **Render Template Node [R1] (Decoupled Architecture)**, quy trình này được tạo ra để:
- Xác minh Node có khả năng lấy đúng nội dung HTML thô từ bảng hệ thống `ska_data_sys_organisms` (Chế độ System Organisms).
- Xác minh Node có khả năng nhận dữ liệu văn bản HTML tùy biến từ biến Payload (Chế độ Raw Variable - kết hợp DB Query).
- Kiểm tra quá trình nội suy biến SkaFX (VD: `{{ payload.user_name }}`) hoạt động trơn tru bên trong chuỗi HTML.
- Đảm bảo HTML đầu ra được lưu trữ chính xác vào biến đích (Result Var) của `$payload` và có thể truy xuất bởi các Node tiếp theo (như Client Response).
- Đánh giá UI/UX cấu hình Node trên SettingsPanel (Chọn Source Type, Nhập ID/Biến) đảm bảo thân thiện, không lỗi.

## Kịch bản 1: Kiểm thử Chế độ System Organisms (Builder)

### Bước 1: Chuẩn bị Template HTML (Ska Data Pro / Theme Builder)
- **Thao tác:** Khởi tạo một mẫu HTML tượng trưng trong bảng `wp_ska_data_sys_organisms` (bằng cách dùng DB tool hoặc giao diện Data Pro).
- **Dữ liệu mẫu (HTML):**
  - **Tên Organism:** `Email Welcome Template`
  - **ID (organism_id):** `org_welcome_email`
  - **Nội dung (HTML):**
    ```html
    <div class="email-container">
      <h1>Chào mừng {{ user.full_name }}!</h1>
    </div>
    ```

### Bước 2: Tạo Workflow tích hợp Render Template (System Mode)
- **Thao tác:** Kéo thả các Node: `Trigger` ➡️ `Set Data` ➡️ `Render Template` ➡️ `Client Response`.
- **Cấu hình Set Data Node:**
  - `user.full_name` = `Lý Tất Thành`
- **Cấu hình Render Template Node:**
  - **Nguồn Template (Source):** `Từ System Organisms (Builder)`
  - **Organism ID:** `org_welcome_email`
  - **Result Var:** `payload.html_output`
- **Cấu hình Client Response:**
  - **Hành động:** Hiển thị thông báo (Toast).
  - **Nội dung:** `{{ html_output }}`

### Bước 3: Kích hoạt & Xác thực
- **Thao tác:** Kích hoạt Trigger.
> **✅ Pass khi:** 
> Backend không lỗi, Toast trả về mã HTML: `<div class="email-container"><h1>Chào mừng Lý Tất Thành!</h1></div>`.

---

## Kịch bản 2: Kiểm thử Chế độ Raw Variable (Ska Data Pro Custom Table)

### Bước 1: Chuẩn bị Custom Table (Ska Data Pro)
- **Thao tác:** Tạo bảng tùy chỉnh `ska_data_app_email_templates`.
- **Dữ liệu mẫu:**
  - **Cột:** `id` (1), `name` (Khuyến mãi), `html_content` (Mã HTML bên dưới).
    ```html
    <div class="promo">
      <h2>Giảm giá {{ promo.discount }}% cho {{ user.name }}</h2>
    </div>
    ```

### Bước 2: Tạo Workflow tích hợp DB Query + Render Template (Raw Mode)
- **Thao tác:** Kéo thả: `Trigger` ➡️ `Set Data` ➡️ `DB Query` ➡️ `Render Template` ➡️ `Client Response`.
- **Cấu hình Set Data Node:**
  - `promo.discount` = `50`
  - `user.name` = `John Doe`
- **Cấu hình DB Query Node:**
  - **Bảng (Table):** `ska_data_app_email_templates`
  - **Điều kiện (Where):** `id` = `1`
  - **Return Type:** Dòng đầu tiên (Object)
  - **Result Variable:** `db_template`
- **Cấu hình Render Template Node:**
  - **Nguồn Template (Source):** `Từ Biến / Custom Text (Ska Data Pro)`
  - **Dữ liệu HTML / Biến:** `{{ payload.db_template.html_content }}`
  - **Result Var:** `payload.final_html`
- **Cấu hình Client Response:**
  - **Hành động:** Hiển thị thông báo (Toast).
  - **Nội dung:** `{{ final_html }}`

### Bước 3: Kích hoạt & Xác thực
- **Thao tác:** Kích hoạt Trigger.
> **✅ Pass khi:**
> DB Query lấy đúng dòng dữ liệu, truyền `html_content` vào Render Template. Render Template nội suy các biến `discount` và `name`, cuối cùng Client Response hiển thị chuỗi HTML hoàn chỉnh: `<div class="promo"><h2>Giảm giá 50% cho John Doe</h2></div>`.
> Kiến trúc giải phóng Render Template khỏi bảng hệ thống hoạt động mỹ mãn!
