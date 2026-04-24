# PROJECT MANAGER: LOGIC ENGINE WORKFLOW - AUTOMATION (DAG BUILDER)
@status: 🟡 In Progress | @last_update: 2026-04-24 | @context: Tái cấu trúc UX/UI cho Ska Logic Engine theo chuẩn n8n/Blender (React Flow).

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Chuyển đổi sang DAG (Directed Acyclic Graph):** Chuyển từ dạng Linear cũ sang Đồ thị rẽ nhánh đa hướng để xử lý Logic và Automation toàn diện.
- **Tiêu diệt Low-code:** Chấm dứt việc gõ phím cấu hình (Data Picker, Auto-Mapping cho các Node DB).
- **Hệ sinh thái Atomic Nodes:** Cung cấp sẵn tập hợp 10 Node cơ sở để xây dựng mọi kịch bản tự động hóa từ cơ bản đến phức tạp.

---

## 2. ROADMAP PHÁT TRIỂN 10 ATOMIC NODES (PHASE 4.2)

### 2.1. Nhóm Core & Logic (Ưu tiên P1)
- [ ] **[T1] Event Trigger Node:** (Đang làm) Lắng nghe Hooks (Form Submit, Insert). 
- [ ] **[D1] DB CRUD Action Node:** Node thao tác CSDL với Ska Data Pro. (Tích hợp Schema UI, Auto-map fields, Data Picker).
- [ ] **[L1] If/Else Condition Node:** Rẽ nhánh logic 2 cổng (True/False).

### 2.2. Nhóm Mở rộng Kết nối (Ưu tiên P2)
- [ ] **[I1] HTTP Request Node:** Trình gọi API RESTful (Cấu hình Headers, Method, Payload).
- [ ] **[T2] Webhook Trigger Node:** Nhận Payload từ bên ngoài.
- [ ] **[I2] Email/Notification Node:** Gửi thông báo SMTP & Push Notification.

### 2.3. Nhóm Nâng cao (Ưu tiên P3)
- [ ] **[L2] Switch Router Node:** Rẽ nhiều nhánh Switch/Case.
- [ ] **[L3] Iterator/Loop Node:** Lặp qua mảng dữ liệu để xử lý Bulk.
- [ ] **[T3] Schedule Trigger Node:** Thiết lập Cronjobs (Thời gian thực).
- [ ] **[U1] Data Transform / Set Node:** Trạm trung chuyển format lại biến bằng biểu thức SkaFX DSL trước khi đẩy xuống luồng dưới.

---

## 3. TIẾN TRÌNH HIỆN TẠI (EXECUTION TRACKER)

### 3.1. Nền tảng React Flow (Đã hoàn thành)
- [x] Tích hợp Vite pipeline & Tailwind CSS.
- [x] Sửa lỗi Màn hình trắng (Polyfill `process.env`).
- [x] Thiết lập React Flow, Sidebar Kéo-thả (Drag & Drop).
- [x] Xây dựng Settings Panel (Inspector) cho phép map UI Data ngược vào Đồ thị.

### 3.2. Triển khai Node: If/Else & DB CRUD (Đang thực hiện)
- [ ] Khởi tạo File Frontend React cho `ConditionNode` (If/Else).
- [ ] Xây dựng giao diện Setting Add Rules cho If/Else.
- [ ] Khởi tạo File Frontend React cho `DBActionNode` (CRUD).
- [ ] Viết chức năng Call API Fetch Schema từ Ska Data Pro.
- [ ] Tích hợp tính năng Data Picker (Biến số động).
