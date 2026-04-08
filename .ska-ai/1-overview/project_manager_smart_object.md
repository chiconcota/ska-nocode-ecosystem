# PROJECT MANAGER: SMART OBJECT (APP BLUEPRINT)
@target: Ska Data Pro / Ska Logic Engine
@status: 📝 Draft (Kế hoạch lên khung)
@timeline: Chuẩn bị cho phiên làm việc tới.

Dự án **Smart Object (App Blueprint)** là cuộc đại phẫu cấu trúc móng của toàn bộ hệ sinh thái Ska. Mục tiêu là bãi bỏ việc tạo Bảng dữ liệu (Table) mồ côi, thay vào đó mọi bảng phải được quy hoạch gọn gàng vào trong các **Object/App Workspace** cụ thể (Ví dụ: App Quản Lý Booking, App Bán Hàng).

## 🏁 PHASE 1: Triển Khai Schema Lõi (Backend Database)
*Nhiệm vụ: Cập nhật hệ thống dữ liệu ngầm cho Ska Data Pro.*

- [ ] Tạo bảng (Table) hoặc cấu trúc `Option` chuẩn mực mang tên `ska_apps` để tàng trữ các App Blueprint khách tạo.
- [ ] Chỉnh sửa lược đồ lưu trữ Metadata của Bảng: Thay thế trường `__table_info['group']` dạng chuỗi nhãn hiệu nghèo nàn thành Khóa Ngoại `app_id` trỏ về Bảng App.
- [ ] Xây dựng các hàm Getters nội bộ (API): `Ska_App_Manager::get_app_by_id()`, `Ska_App_Manager::get_tables_in_app()`.

## 🏁 PHASE 2: Giao Diện Quản Trị (Ska Data Pro Admin UI)
*Nhiệm vụ: Nâng cấp trải nghiệm người dùng Quản lý Workspace.*

- [ ] Thay đổi thiết kế Màn hình Danh sách Bảng: Gom nhóm các Bảng theo từng "App Workspace". (Giống giao diện Folders của Airtable).
- [ ] Bổ sung Nút CTA lớn: "Tạo Ứng Dụng Mới (Create App)" thay vì chỉ có nút "Tạo Bảng Mới".
- [ ] Thiết kế form chọn App khi người dùng thao tác Tạo/Sửa Lược đồ Bảng.

## 🏁 PHASE 3: Kết Nối Liên Thông (Logic Engine & Theme Builder)
*Nhiệm vụ: Dùng App Blueprint làm đòn bẩy ngữ cảnh cho các Plugin khác.*

- [ ] **Data Hydration (SkaFX):** Bổ sung bộ giải mã `Context_Resolver`. Cho phép Lễ tân gõ cú pháp `[nam_sinh]`, hệ thống tự động chui vào Bảng Nội Khu của chính App đó để lùng dữ liệu.
- [ ] **Cross-App Communication:** Cho phép gọi chéo dữ liệu bằng cú pháp `[app.table.field]` nếu user muốn lôi Data của App khác.
- [ ] **Theme Builder Readiness:** Chắp cánh cho Phase sau (Ska Portal). Khi Lễ tân tạo 1 trang Admin, họ chọn `App` làm Mẹ. Tự động các tính năng trên trang đó (Bảng biểu, Nút Thêm, Xóa, Sửa) đều mặc định mồi Data của App đó.

---
*Ghi chú: Lộ trình này ưu tiên tính Độc Lập (Decoupling). Nếu khách hàng không xài Theme Builder, họ vẫn tận hưởng khả năng quản lý Data gọn gàng theo App (Workspace) bên trong Data Pro.*
