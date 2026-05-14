# PROJECT MANAGER: APP DASHBOARDS & PORTALS (PHASE 4.5)
@status: TODO
@priority: High | @dependency: Ska Theme Builder, Ska Data Pro

## 1. TẦM NHÌN (VISION)
Cung cấp hạ tầng cốt lõi để đưa Ska từ "Website Builder" lên tầm "App Builder". Xóa bỏ hoàn toàn sự phụ thuộc vào `/wp-admin` để quản lý dữ liệu, tạo ra các giao diện quản trị Frontend (Portals) độc lập, bảo mật và chuẩn White-label (có thể đóng gói đem bán).

## 2. QUYẾT ĐỊNH KIẾN TRÚC (ARCHITECTURE DECISIONS)
- **Shadow CPT (`ska_portal`):** Sử dụng Post Type Ảo để mượn sức mạnh của trình kéo thả Gutenberg.
- **Flat Table Storage:** Lưu toàn bộ thiết kế Portal vào bảng `ska_data_sys_portals`, tuyệt đối không dùng `wp_postmeta`.
- **Interception Layer:** Chặn API lưu của Gutenberg (`/wp/v2/ska_portal`) để bẻ lái luồng dữ liệu xuống Data Pro.
- **Virtual Routing:** Sử dụng cơ chế Wrapper của Theme Builder để quyết định giao diện hiển thị ở Frontend dựa trên URL (Ví dụ: `/portal/*`). Hỗ trợ cả cơ chế Multi-page và SPA (Single Page Application) qua Alpine.js.

## 3. DANH SÁCH NHIỆM VỤ (TASK LIST)
- [ ] **DB Setup:** Tạo bảng `ska_data_sys_portals` trong Ska Data Pro.
- [ ] **Shadow CPT:** Đăng ký Post Type `ska_portal` ở dạng Ảo (Không UI, Không public query).
- [ ] **Interception Layer:** Viết Hook đánh chặn `rest_pre_insert_ska_portal` và `rest_prepare_ska_portal` để chặn lưu rác.
- [ ] **Frontend Routing:** Viết class `Ska_Portal_Router` bắt regex `/portal/([a-zA-Z0-9-]+)` để load nội dung từ bảng phẳng và trùm Theme Builder (Master Layout) lên.
- [ ] **Dashboard UI:** Thêm tab "App Portals" trong Ska System Dashboard để Admin quản lý danh sách các trang Portal.
