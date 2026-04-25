# PROJECT MANAGER: LOGIC ENGINE WORKFLOW - AUTOMATION (DAG BUILDER)
@status: 🟡 In Progress | @last_update: 2026-04-24 | @context: Tái cấu trúc UX/UI cho Ska Logic Engine theo chuẩn n8n/Blender (React Flow).

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
- [ ] **[L2] Switch Router Node:** Rẽ nhiều nhánh dựa trên giá trị.

### 2.2. Nhóm Data & Giao thức (Ưu tiên P2)
- [x] **[D1] DB CRUD Action Node (Ska Native):** Node thao tác CSDL với Ska Data Pro. (Tích hợp Schema UI, Auto-map fields, Data Picker, hỗ trợ Data Vector Batch Process để chống N+1).
- [x] **[U1] Context / JSON Node:** Parse, Stringify, Set Data (Gán biến qua SkaFX).
- [ ] **[I1] Raw HTTP Request Node:** Trình gọi API RESTful (Cấu hình Headers, Method, Payload) dùng chung cho mọi dịch vụ ngoài.

### 2.3. Nhóm Nâng cao (Ưu tiên P3)
- [ ] **[L3] Iterator/Loop Node:** Lặp qua mảng dữ liệu để xử lý Bulk.
- [ ] **[T2] Webhook & Cron Trigger:** Hỗ trợ nhận tải trọng và lên lịch.

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
- [ ] Cập nhật `class-workflow-runner.php` thêm giới hạn đệ quy (`Circuit Breaker`).
- [ ] Tích hợp tính năng Data Picker nâng cao (Gom nhóm theo App).
- [ ] Kiểm thử toàn diện DBActionNode (Insert/Update Record) cho quy trình cuối.
