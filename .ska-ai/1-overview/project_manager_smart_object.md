# PROJECT MANAGER: SMART OBJECT (APP BLUEPRINT)
@target: Ska Data Pro / Ska Logic Engine
@status: 🟢 Thắng lợi Phase 1, 2 và Phase Phụ (Export/Import)
@timeline: Đang bước vào tích hợp SkaFX Logic.

Dự án **Smart Object (App Blueprint)** là cuộc đại phẫu cấu trúc móng của toàn bộ hệ sinh thái Ska. Mục tiêu là bãi bỏ việc tạo Bảng dữ liệu (Table) mồ côi, thay vào đó mọi bảng phải được quy hoạch gọn gàng vào trong các **Object/App Workspace** cụ thể (Ví dụ: App Quản Lý Booking, App Bán Hàng).

## 🏁 PHASE 1: Triển Khai Schema Lõi (Backend Database) - 🟢 [DONE]
*Nhiệm vụ: Cập nhật hệ thống dữ liệu ngầm cho Ska Data Pro.*

- [x] Tạo cấu trúc `Option` chuẩn mực mang tên `ska_data_apps` để tàng trữ các App Blueprint khách tạo (Thay vì table cồng kềnh). 
- [x] Chỉnh sửa lược đồ lưu trữ Metadata của Bảng: Thay thế trường `__table_info['group']` dạng chuỗi nhãn hiệu nghèo nàn thành Khóa Ngoại `app_id` trỏ về Bảng App. Tích hợp Migration tự động.
- [x] Xây dựng các hàm Getters nội bộ (API): `App_Manager::get_apps()`, logic Safe Drop đưa Table mồ côi về App "Uncategorized".

## 🏁 PHASE 2: Giao Diện Quản Trị (Ska Data Pro Admin UI) - 🟢 [DONE]
*Nhiệm vụ: Nâng cấp trải nghiệm người dùng Quản lý Workspace.*

- [x] Thay đổi thiết kế Màn hình Danh sách Bảng: Gom nhóm các Bảng theo từng "App Workspace". Tích hợp Kebab Menu tiện ích.
- [x] Bổ sung Nút CTA lớn: "Tạo Workspace Mới" và các Modal xử lý tương thích (Tạo, Sửa tên với Icon, Giải tán).
- [x] Thiết lập logic gán App qua form thẻ `<select>` khi người dùng tương tác Tạo/Sửa Lược đồ Bảng.

## 🏁 PHASE 2.5: Đóng Gói Và Lan Truyền (Export / Import Blueprint) - 🟢 [DONE]
*Nhiệm vụ: Chống kẹt dữ liệu (Vendor Lock-in) cục bộ 1 web, cho phép sang nhượng thiết kế.*

- [x] Xuất JSON (Export Schema): Lấy Metadata (`app_name`, `version`, `icon`), bỏ Row Data. Bảo mật an toàn.
- [x] Tái thiết Database (Dynamic Resolver): Phát triển cục Import có thuật toán tự dò Tên Bảng Trùng trên Website B (Ví dụ `teachers` đã tồn tại -> `ska_data_teachers_1`). 
- [x] Nối Mạng (Re-wiring Relation): Tự dò các Target Slug (`campaigns`) có trong File Blueprint để nạp lại Target Column (Slug Mapping) tránh gãy liên kết (Database Corrupted). Mở Hook ống xả `do_action('ska_import_smart_object')`.

## 🏁 PHASE 3: Kết Nối Liên Thông (Logic Engine & Theme Builder) - 🔴 [PENDING]
*Nhiệm vụ: Dùng App Blueprint làm đòn bẩy ngữ cảnh cho các Plugin khác.*

- [ ] **Data Hydration (SkaFX):** Bổ sung bộ giải mã `Context_Resolver`. Cho phép Lễ tân gõ cú pháp `[nam_sinh]`, hệ thống tự động chui vào Bảng Nội Khu của chính App đó để lùng dữ liệu.
- [ ] **Cross-App Communication:** Cho phép gọi chéo dữ liệu bằng cú pháp `[app.table.field]` nếu user muốn lôi Data của App khác.
- [ ] **Theme Builder Readiness & Auto-Generated Portal:** Chắp cánh cho Phase sau (Ska Portal). Khi Lễ tân tạo 1 trang Admin, họ chọn `App` làm Mẹ. Tự động các tính năng trên trang đó (Bảng biểu, Nút Thêm, Xóa, Sửa) đều mặc định mồi Data của App đó. Không cần phải kéo thả lại List View từ đầu.

---
*Ghi chú: Lộ trình này ưu tiên tính Độc Lập (Decoupling). Nếu khách hàng không xài Theme Builder, họ vẫn tận hưởng khả năng quản lý Data gọn gàng theo App (Workspace) bên trong Data Pro.*
