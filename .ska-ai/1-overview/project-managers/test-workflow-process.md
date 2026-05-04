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

---

# Quy Trình Kiểm Thử (End-to-End) Node Iterator/Loop trên Ska Logic Engine

## Mục tiêu
Sau khi hoàn thiện tính năng **Iterator/Loop Node [L3] (Group Node Architecture)**, quy trình này được tạo ra để:
- Xác minh chức năng gom nhóm (Group Node) trên Canvas hoạt động tốt, cho phép các node con (như Set Data, DB Action) nằm gọn bên trong Iterator.
- Xác minh thuật toán Topological Sorting biên dịch chính xác các node con thành một pipeline tuyến tính (JIT Compilation) chỉ 1 lần duy nhất, tối ưu hiệu năng.
- Đảm bảo **tính cô lập (Isolation)**: Chỉ các node nằm gọn trong Group mới bị lặp N lần. Các node nằm ngoài Group (kết nối phía sau Iterator) chỉ chạy đúng 1 lần, ngăn chặn tình trạng "tính toán 10 phép tính bị lặp 1000 lần" gây sập hệ thống.
- Kiểm tra tính năng nội suy các biến ngữ cảnh `{{ $item }}`, `{{ $index }}`, `{{ $first }}`, `{{ $last }}` trong mỗi vòng lặp.
- Đảm bảo Payload được truyền liên tục và không bị "ô nhiễm" biến ngữ cảnh sau khi vòng lặp kết thúc.

## Kịch bản Kiểm thử: Lặp qua danh sách đơn hàng và Tính tổng tiền

### Bước 1: Chuẩn bị Dữ liệu mẫu (Set Data)
- **Thao tác:** Khởi tạo danh sách mảng dữ liệu mẫu thông qua Set Data Node để Iterator xử lý.
- **Cấu hình Set Data Node (Khởi tạo Payload trước vòng lặp):**
  - Cấu hình Gán biến 1: Root Key = `orders` | Giá trị = `[{"id": 1, "price": 100}, {"id": 2, "price": 250}, {"id": 3, "price": 50}]`
  - Cấu hình Gán biến 2: Root Key = `total_revenue` | Giá trị = `0` (Biến tích lũy)

### Bước 2: Tạo Workflow tích hợp Iterator Node
- **Thao tác:** Kéo thả các Node: `Trigger` ➡️ `Set Data (Khởi tạo)` ➡️ `Iterator Node` ➡️ `Client Response`.
- **Cấu hình Iterator Node:**
  - **Array Source:** `[payload.orders]`
  - Thay đổi kích thước (Resize) Iterator Node cho đủ rộng để chứa node con.
- **Thêm chuỗi Node con vào trong Iterator:**
  1. **Node con A (Set Data):** Kéo thả vào Iterator.
     - **Mục tiêu:** Tính toán thuế VAT cho từng đơn hàng.
     - Cấu hình: Root Key = `current_item_tax` | Giá trị = `[$item.price] * 0.1`
  2. **Node con B (DB Action):** Kéo thả vào Iterator, nối dây sau Node A.
     - **Mục tiêu:** Ghi nhận lịch sử xử lý đơn hàng vào bảng `ska_data_logs`.
     - Cấu hình: Action = `Insert`, Cột `log_msg` = `Đang xử lý đơn #{{ $item.id }} với thuế {{ current_item_tax }}$`.
  3. **Node con C (Set Data - Accumulator):** Nối dây sau Node B.
     - **Mục tiêu:** Cộng dồn vào biến `total_revenue`.
     - Cấu hình: Root Key = `total_revenue` | Giá trị = `[payload.total_revenue] + [$item.price] + [payload.current_item_tax]`

> **🔍 Kiểm tra tính liên kết:**
> - Cả 3 node A, B, C đều phải có `Parent Node` trỏ về cùng một Iterator ID.
> - Khi di chuyển Iterator, cả cụm 3 node con phải di chuyển theo đồng bộ.
> - Thử kéo Iterator di chuyển → Node con phải di chuyển theo.
> - Thử kéo node con ra ngoài vùng Iterator → `Parent Node` phải tự xóa trắng (detach).

### Bước 3: Cấu hình Client Response & Xác thực
- **Cấu hình Client Response (Nằm NGOÀI Iterator, nối tiếp sau Iterator):**
  - **Hành động:** Hiển thị thông báo (Toast).
  - **Nội dung:** `Đã lặp qua {{ orders.length }} đơn hàng. Tổng doanh thu là: {{ total_revenue }}$`
- **Thao tác:** Lưu đồ thị và Kích hoạt Trigger.
> **✅ Pass khi:** 
> - UI Canvas cho phép kéo thả và nối dây nhiều Node con bên trong vùng Iterator một cách mượt mà.
> - Backend biên dịch JIT chính xác thứ tự A -> B -> C cho mỗi lần lặp.
> - Vòng lặp chạy qua 3 đơn hàng. Toast hiển thị chính xác tổng tiền đã bao gồm thuế: `Đã lặp qua 3 đơn hàng. Tổng doanh thu (kèm thuế) là: 440$`.
> - Bảng `ska_data_logs` sinh ra 3 bản ghi log tương ứng với 3 vòng lặp, chứng minh DB Action chạy N lần.

---

# Quy Trình Kiểm Thử (End-to-End) Node Ska Loop Block trên Frontend (Theme Builder)

## Mục tiêu
Sau khi hoàn thiện toàn bộ tầng **Backend PHP** và **React Inspector UI** của **Ska Loop Block** (Phase 4), quy trình này nhằm xác minh:
- Nocode User có thể cấu hình bảng nguồn và chọn Khuôn mẫu (Organism) từ Gutenberg Editor.
- Tính năng Live Preview bằng `ServerSideRender` hoạt động tốt, hiển thị dữ liệu trực quan ngay trong màn hình Editor.
- Cơ chế **Zero N+1 Query & Hydration Engine** xử lý tốt việc biến đổi các biến `[cột_dữ_liệu]` thành giá trị thật siêu tốc ở ngoài Frontend.
- Đảm bảo tính tương tác mượt mà của Ska Select Inspector (được tạo ở Phase trước) khi kết hợp cùng Loop.

## Kịch bản Kiểm thử: Hiển thị danh sách Bác sĩ

### Bước 1: Khởi tạo Schema Dữ liệu (Ska Data Pro - Vai trò Super Admin)
- **Thao tác:** Sử dụng tính năng Smart Object để định nghĩa cấu trúc bảng phẳng `ska_data_doctors`.
- **Cấu hình Cột:** Khai báo các cột dữ liệu như `name` (Tên bác sĩ), `specialty` (Chuyên khoa), `experience` (Số năm kinh nghiệm). *Lưu ý: Chỉ tạo Schema rỗng, không nhập dữ liệu bằng tay trong Database.*

### Bước 1.5 (Trung gian): Thu thập dữ liệu qua Form (Vai trò Admin/User)
- **Thao tác:** Xây dựng một biểu mẫu (Form) nhập liệu đơn giản trên Frontend hoặc Dashboard.
- **Tích hợp Logic Engine:** Kết nối Form này với Ska Logic Engine:
  - `Trigger`: Khi Form Submit.
  - `DB Action Node`: Hành động `Insert`, lưu dữ liệu từ Form vào bảng `ska_data_doctors`.
  - `Client Response Node`: Thông báo "Đã thêm bác sĩ thành công".
- **Thực thi:** Người dùng truy cập Form và nhập 3 bác sĩ mẫu. Bước này xác minh luồng dữ liệu chảy xuyên suốt từ Giao diện Form ➡️ Logic Engine ➡️ Bảng phẳng.

### Bước 2: Thiết kế Khuôn mẫu (Ska Symbol - Vai trò Designer)
- **Thao tác:** Chuyển sang phần **Ska Symbols** (hoặc tạo một nháp mới).
- **Thiết kế:** 
  - Kéo một khối `Ska Container` làm thẻ bao bọc (Card).
  - Kéo khối `Ska Text` làm Tên bác sĩ. Gắn Data Binding (Dynamic Content) cho Text này là `[name]`.
  - Kéo khối `Ska Text` làm Chuyên khoa. Gắn Data Binding là `[specialty]`.
- **Lưu lại:** Đặt tên là `Thẻ Bác Sĩ Chuẩn` (Ghi nhớ Organism ID, giả sử là `15`).

### Bước 3: Cấu hình khối Ska Loop Block trên trang Frontend
- **Thao tác:** Mở một Trang mới (Pages), kéo thả khối **Ska Query Loop** vào.
- **Cấu hình trên Inspector (Bảng bên phải):**
  - **Source Table:** Nhập `ska_data_doctors`
  - **Limit:** Nhập `10`
  - **Thêm Slot Mới:**
    - **Organism ID:** Chọn `Thẻ Bác Sĩ Chuẩn` (ID 15) từ danh sách thả xuống.
    - **Condition:** Bỏ trống hoặc gõ `default`.
- **Kiểm tra Editor:** 
  - Đợi khoảng 1-2 giây, Editor phải xuất hiện **Live Preview** của 3 bác sĩ đã tạo ở Bước 1. Các thẻ văn bản hiện đúng dữ liệu thật, không phải chữ "Hello World".

### Bước 4: Kiểm chứng Frontend và Tốc độ (Zero N+1)
- **Thao tác:** Bấm Lưu trang và mở xem trang trên Frontend (Chế độ ẩn danh).
> **✅ Pass khi:** 
> - Ngoài Frontend hiển thị chính xác danh sách 3 bác sĩ với giao diện thẻ (Card) chuẩn chỉnh.
> - Cài công cụ `Query Monitor` (Plugin WordPress) để xem số lượng truy vấn DB: Tổng số truy vấn phải cực thấp (chỉ tốn đúng 1 Query lấy HTML và 1 Query truy vấn bảng `ska_data_doctors`). Không có bất kỳ truy vấn nào bị phát sinh thêm trong quá trình vòng lặp (Chứng tỏ Zero N+1 hoạt động hoàn hảo).
> - Tính năng Data Binding chuyển đổi `[name]` thành dữ liệu thật chính xác qua cơ chế Hydration.
