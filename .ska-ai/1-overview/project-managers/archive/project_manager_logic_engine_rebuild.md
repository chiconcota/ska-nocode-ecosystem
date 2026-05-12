# PROJECT MANAGER: LOGIC ENGINE WORKFLOW - AUTOMATION (DAG BUILDER)
@status: ✅ Completed | @last_update: 2026-04-30 | @context: Tái cấu trúc UX/UI cho Ska Logic Engine theo chuẩn n8n/Blender (React Flow).

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Chuyển đổi sang DAG (Directed Acyclic Graph):** Chuyển từ dạng Linear cũ sang Đồ thị rẽ nhánh đa hướng để xử lý Logic và Automation toàn diện.
- **Tiêu diệt Low-code:** Chấm dứt việc gõ phím cấu hình (Data Picker, Auto-Mapping cho các Node DB).
- **Hệ sinh thái Atomic Nodes:** Cung cấp sẵn tập hợp 10 Node cơ sở để xây dựng mọi kịch bản tự động hóa từ cơ bản đến phức tạp.

---

## 2. ROADMAP PHÁT TRIỂN CORE PRIMITIVE NODES (PHASE 4.2 MVP)
Thay vì làm các Node chức năng khổng lồ, hệ thống sẽ chỉ cung cấp các "Viên gạch cơ sở" (Primitives). Các Composite Nodes (nhóm các Primitive thành Sub-flow) sẽ được dời sang Phase sau MVP.

### 2.1. Nhóm Logic & Trigger (Ưu tiên P1)
- [x] **[T1] Event Trigger Node:** (Đang làm) Lắng nghe Hooks (Form Submit, Insert). 
- [x] **[L1] If/Else Condition Node:** Rẽ nhánh logic 2 cổng (True/False).
- [x] **[L2] Switch Router Node:** Rẽ nhiều nhánh dựa trên giá trị.

### 2.2. Nhóm Data & Giao thức (Ưu tiên P2)
- [x] **[D1] DB CRUD Action Node (Ska Native):** Node thao tác CSDL với Ska Data Pro. (Tích hợp Schema UI, Auto-map fields, Data Picker, hỗ trợ Data Vector Batch Process để chống N+1).
- [x] **[D2] DB Query Node (Ska Native):** Node chuyên đọc/truy vấn CSDL (Fetch/Read). Hỗ trợ lọc (Where), sắp xếp (Sort), và giới hạn (Limit). Trả về Array of Objects vào Payload.
- [x] **[U1] Context / JSON Node:** Parse, Stringify, Set Data (Gán biến qua SkaFX).
- [x] **[I1] Raw HTTP Request Node:** Trình gọi API RESTful (Cấu hình Headers, Method, Payload) dùng chung cho mọi dịch vụ ngoài.

### 2.3. Nhóm Nâng cao (Ưu tiên P3)
- [x] **[L3] Iterator/Loop Node:** Lặp qua mảng dữ liệu để xử lý Bulk.
- [ ] **[T2] Webhook & Cron Trigger:** [Post-MVP] Hỗ trợ nhận tải trọng và lên lịch.
- [x] **[T3] Action Click / Custom Event:** Cho phép gắn Trigger vào bất kỳ một Nút bấm (Button) hoặc Thẻ (Div) nào trên Frontend thay vì bắt buộc dùng Form.

### 2.4. Nhóm Trình Diễn & Trả Về (Render Template & Client Response)
- [x] **[R1] Render Template Node:** Node sinh HTML động. Lấy Smart Object từ DB và đắp dữ liệu (Hydration) bằng Mustache để phục vụ gửi Email, tạo PDF, hoặc giao diện Pop-up. Hỗ trợ 2 chế độ: Nguồn từ bảng hệ thống và Nguồn biến động (Raw Template).
- [x] **[C1] Client Response Node:** Trả tín hiệu JSON về trình duyệt. Đảm nhiệm mọi tương tác UI ở Frontend bằng cách ném ra chỉ thị (Ví dụ: `{"action": "show_toast"}`, `{"action": "redirect"}`, `{"action": "open_modal"}`) cho Javascript xử lý.
### 2.5. Nhóm Pre-built Integration (Tương lai / Post-MVP)
- [ ] **[N1] Notion Node:** [Post-MVP] Đóng gói sẵn kết nối API của Notion (Tự động chèn Authorization & Notion-Version).
- [ ] **[N2] Các dịch vụ bên thứ 3 phổ biến:** [Post-MVP] Google Sheets, Zalo ZNS, Telegram Bot...

---

## 3. TIẾN TRÌNH HIỆN TẠI (EXECUTION TRACKER)

### 3.1. Nền tảng React Flow (Đã hoàn thành)
- [x] Tích hợp Vite pipeline & Tailwind CSS.
- [x] Sửa lỗi Màn hình trắng (Polyfill `process.env`).
- [x] Thiết lập React Flow, Sidebar Kéo-thả (Drag & Drop).
- [x] Xây dựng Settings Panel (Inspector) cho phép map UI Data ngược vào Đồ thị.

### 3.2. Triển khai Cơ Chế Core & Primitive Nodes
- [x] Khởi tạo Frontend React và Backend Core cho Primitive Node `Set Data` (Gán biến).
- [x] Khởi tạo File Frontend React cho `ConditionNode` (If/Else).
- [x] Xây dựng giao diện Setting Add Rules cho If/Else.
- [x] Khởi tạo File Frontend React cho `DBActionNode` (CRUD).
- [x] Hoàn thiện trải nghiệm UX/UI (Dynamic Submenus, Unified Dashboard Cards, chống tràn Settings Panel, sửa lỗi ESC đóng modal).
- [x] Cập nhật Frontend Event Bus và Alpine.store để đón nhận Client Response từ Node.
- [x] Triển khai **DB Query Node**: Lấy dữ liệu mảng/đối tượng từ Ska Data Pro. Tích hợp SQL Logging.
- [x] Triển khai **Render Template Node**: Mở rộng kiến trúc Decoupled hỗ trợ nội suy Raw Variable.
### 3.3. Technical Backlog (Tối ưu sau)
- [ ] Cập nhật `class-workflow-runner.php` thêm giới hạn đệ quy (`Circuit Breaker`).
- [ ] Tích hợp tính năng Data Picker nâng cao (Gom nhóm theo App).
- [x] Kiểm thử toàn diện DBActionNode (Insert/Update Record) kết hợp với If/Else và Set Data (Đã hoàn tất quy trình cuối).
