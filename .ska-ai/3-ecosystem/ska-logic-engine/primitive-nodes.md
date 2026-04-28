# MODULE: PRIMITIVE NODES (LOGIC ENGINE)
> **Path:** `ska-logic-engine/includes/primitives/` & `ska-logic-engine/assets/src/builder/nodes/`

## 1. Nhiệm vụ (Responsibility)
Hệ thống các "Hạt Cơ Sở" (Atomic Nodes) tạo nên sức mạnh tự động hóa của Ska Logic Engine. Dựa trên triết lý DAG (Directed Acyclic Graph) và Single Responsibility (Mỗi node làm đúng 1 việc).

## 2. Danh sách Primitive Nodes (Cập nhật 2026-04-28)

### A. Quy tắc chung (Global Rules)
- **Interface Chuẩn:** Tất cả Node Backend PHP phải implement `Ska_Logic_Node` interface với phương thức `execute($payload, $config)`.
- **Mutate by Reference:** Data chảy qua các Node dưới dạng mảng `$payload`. Các Node tự do thêm, bớt dữ liệu vào mảng này và truyền đi tiếp. Không có biến trung gian rườm rà.
- **SkaFX Expression:** Tất cả các ô nhập liệu (Text input) trong Node phải hỗ trợ nội suy biến động thông qua `SkaFX_Engine::execute`.
- **Smart Fallback:** Cú pháp tĩnh (Literal string) gõ sai sẽ tự động được đánh giá thành chuỗi văn bản thông thường thay vì quăng lỗi NULL. (VD: gõ `khách lẻ` thay vì `"khách lẻ"`).

### B. Nhóm Logic & Trigger
#### 1. Event Trigger Node (`[T1] Trigger`)
- **Vai trò:** Lắng nghe tín hiệu kích hoạt từ các nguồn (Frontend, Hệ thống ngoài, Định kỳ).
- **Backend:** `class-ska-logic-trigger.php`. Chỉ đơn thuần hứng dữ liệu (từ Form, Button Click, hoặc Webhook) và khởi tạo `$payload` gốc.
- **Phân loại (Trigger Type):**
  - `Logic Trigger (AJAX / Form)`: Nhận tín hiệu từ Button Click (`.ska-action-[id]`) hoặc Form Submit ở Frontend.
  - `Webhook URL`: Nhận tín hiệu từ máy chủ/ứng dụng bên thứ 3.
  - `Schedule (Cron)`: Kích hoạt tự động theo chu kỳ thời gian.

#### 2. If/Else Condition Node (`[L1] If/Else`)
- **Vai trò:** Rẽ nhánh logic 2 cổng (True / False).
- **Backend:** `class-ska-logic-condition.php`. Trả về `port = 'true'` hoặc `port = 'false'` dựa trên biểu thức boolean đánh giá bởi SkaFX.

#### 3. Switch Router Node (`[L2] Switch`)
- **Vai trò:** Rẽ nhiều nhánh dựa trên so sánh biến. Giải quyết bài toán 10 if/else lồng nhau.
- **Backend:** `class-ska-logic-switch.php`. Trả về `port = branch_id` hoặc cổng `fallback`.

### C. Nhóm Data & Giao thức
#### 5. DB CRUD Action Node (`[D1] DB Action`)
- **Vai trò:** Các thao tác Mutate (Insert, Update, Delete) xuống CSDL bảng phẳng (`ska_data_*`).
- **Backend:** `class-ska-logic-db-action.php`.
- **Đặc tả:** Hỗ trợ Data Vector (Batch Insert/Update), tự động map Field của DB vào giao diện. Khắc phục vấn đề N+1 Query. Lấy lại `last_insert_id` gắn vào `$payload`. (Lưu ý: Không hỗ trợ tính năng Lấy dữ liệu/Query).

#### 6. DB Query Node (`[D2] DB Query`)
- **Vai trò:** Chuyên trị việc Lọc, Tìm kiếm, Lấy dữ liệu (Read/Fetch) từ DB.
- **Backend:** `class-ska-logic-db-query.php`.
- **Đặc tả:** Giao diện gồm Chọn Bảng -> Cấu hình Điều Kiện (Where) -> Sắp Xếp (Order By) -> Số lượng (Limit). Trả về Array Object cho `$payload`. Tự động ghi log truy vấn SQL nếu không tìm thấy dữ liệu.

#### 7. Set Data / Context Node (`[U1] Set Data`)
- **Vai trò:** Gán giá trị tĩnh hoặc tính toán biến động, chỉnh sửa mảng `$payload`.
- **Backend:** `class-ska-logic-set-data.php`.
- **Đặc tả:** Áp dụng Mutate-by-Reference mạnh mẽ. Cho phép thay đổi kiểu dữ liệu giữa chừng.

#### 8. Raw HTTP Request Node (`[I1] HTTP Request`)
- **Vai trò:** Giao tiếp với thế giới bên ngoài (API POST, GET, PUT, PATCH).
- **Backend:** `class-ska-logic-http-request.php`.
- **Đặc tả:** Hỗ trợ nhúng Header, Body, URL tự do bằng SkaFX (VD: `Bearer {{ token }}`). Trả kết quả JSON từ API ngoài vào nhánh tiếp theo.

### D. Nhóm Trình Diễn & Trả Về (Frontend Response)
#### 9. Client Response Node (`[C1] Client Response`)
- **Vai trò:** Phản hồi tín hiệu về cho trình duyệt (Browser).
- **Backend:** `class-ska-logic-client-response.php`.
- **Đặc tả:** Thay vì tạo 3 Node riêng (Redirect, Toast, Modal), gộp thành 1 Node chỉ trả về cấu trúc Event Bus (Ví dụ: `{"action": "show_toast", "message": "Thành công!"}`). Script JS ở Frontend sẽ lắng nghe và xuất hình.

#### 10. Render Template Node (`[R1] Render Template` - *Done*)
- **Vai trò:** Ép dữ liệu động (Data) vào khung giao diện tĩnh (HTML).
- **Backend:** `class-ska-logic-render-template.php`.
- **Đặc tả:** Nội suy biến Mustache `{{key}}` vào mã HTML thông qua `SkaFX_Engine`. Node hỗ trợ 2 chế độ (Source Type): 1) Truy vấn HTML từ bảng `ska_data_sys_organisms` dựa trên `organism_id` (Dành cho System Builder). 2) Nhận trực tiếp đoạn văn bản HTML thông qua biến cấu hình `raw_template` (VD lấy từ kết quả DB Query của Ska Data Pro). Dữ liệu HTML sau nội suy được đẩy vào mảng `$payload` (Ví dụ `payload.rendered_template`) để làm mồi xuất báo cáo PDF, hiện nội dung Pop-up tùy chỉnh, hoặc gửi lên máy chủ Email (SMTP).
---
**Ghi chú Phát triển:**
- Các Compound Nodes (VD: Upsert) và Composite Nodes (Macro) đã được quyết định dời sang Phase Post-MVP để giữ cho Lõi (Core Primitives) sạch sẽ, gọn nhẹ và đạt độ ổn định 100%.
