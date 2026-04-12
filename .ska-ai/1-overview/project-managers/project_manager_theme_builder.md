# PROJECT MANAGER: THEME BUILDER (SKA CORE EXTENSION)
@status: 🔴 Pending | @last_update: 2026-04-12 | @context: Tầm nhìn kiến trúc Theme Builder Frontend

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Framework-Agnostic Site Design:** Cho phép dựng toàn bộ Website (Header, Footer, Single Item, Archive, Custom Block) mà KHÔNG cần dựa dẫm vào bất kì cấu trúc mã PHP nào của WordPress Theme (vốn nặng nề và dễ xung đột CSS).
- **Thoát ly FSE/Gutenberg:** Thay vì mượn hệ thống FSE của WordPress, chúng ta xây dựng hệ thống Template phân định bằng cơ sở dữ liệu Bảng Phẳng (`ska_data_templates`) gắn kết chặt chẽ với Ska Logic Engine để hỗ trợ Conditional Display nâng cao (ẩn/hiện Header cho Guest/Admin) nhằm đạt tốc độ tải trang 0-milisecond.
- **Phản Vấn đề (Data Fetching):** Xây dựng cầu nối cho UI. Render Dữ liệu Bảng (Flat Tables của Ska Data Pro) thành những khối HTML động lặp lại (Danh sách khóa học, Sản phẩm).

---

## 2. ROADMAP THEME BUILDER - TÍNH NĂNG CHÌA KHÓA
*(Lưu ý Kiến trúc: Phân hệ Theme Builder / App Portals được đẩy về lộ trình ưu tiên Số 5, sau khi bộ khung quản trị chung Ska System Framework & kho chứa Custom Block đã hoàn thiện trạng thái nền).*

### 2.1. Kiến Trúc Lưu Trữ (Flat Table Templates)
- [ ] KHÔNG SỬ DỤNG CPT (Custom Post Type) hay wp_postmeta. 
- [ ] Khởi tạo bảng phẳng `ska_data_templates` thông qua module `Ska Data Pro` để lưu trữ trực tiếp các giao diện khung.
- [ ] Cấu trúc bảng: `id`, `name`, `type` (Header, Footer, Single, Archive, Custom Block), `content` (Lưu cục mã JSON hoặc HTML Blocks thuần), `logic_rules` (Lưu điều kiện hiển thị).
- [ ] UI Bảng điều khiển (App Portals) List danh sách template lấy nguồn trực tiếp từ CSDL phẳng với trải nghiệm No-code siêu tốc.

### 2.2. Ghi Đè Hiển Thị (Template Hierarchy Override)
- [ ] Tận dụng lõi `Ska Blank Theme` (Ska Canvas) làm proxy.
- [ ] Chặn WordPress Template Loader (Action `template_redirect` hoặc filter `template_include`).
- [ ] Bơm khối nội dung dữ liệu (truy xuất từ trường `content` của `ska_data_templates` theo đúng route/url) vào Body của Ska Canvas hoặc tiêm thẳng vào Hook Header/Footer. 

### 2.3. Khối Vòng Lặp Vạn Năng (Ska Query Loop / Map Block)
- **Bài toán:** Chuyển đổi dữ liệu thô từ Database thành danh sách giao diện người dùng lặp lại.
- **Tiến trình:**
  - [ ] *1. Khối Nguồn Dữ Liệu (Query/Source):* UI cho phép chọn bảng Nguồn (Ska Data Pro, WP Posts). Kết hợp Logic Query (Lọc trạng thái Active).
  - [ ] *2. Khối Khuôn Chứa (Repeater / Item Template):* Một khối `InnerBlocks` để người dùng thả `Ska Container`, `Ska Image`, `Ska Text` vào để trang trí cho cấu trúc MỘT dòng dữ liệu.
  - [ ] *3. Hệ thống Biến Vòng Lặp (Loop Data Binding):* Kế thừa hệ thống Nội Suy `{{...}}` của `Ska_Dynamic_Content`. Trong khối Loop, tự động context là Data hiện tại.
- **Vị trí cốt lõi:** Đây là sức mạnh sống còn. Chỉ khi làm được khối lặp Data ra UI, dự án mới thức tỉnh đúng nghĩa là một No-code App Builder hoàn chỉnh (như Bubble/Webflow).

### 2.4. Khối Dynamic UI & Global State
- [ ] **Ska Menu (Nav):** Giao diện thả menu nhưng styled trực tiếp bằng Tailwind JIT thay cho wp_nav_menu cổ điển.
- [ ] **Micro-interactions:** Tích hợp bộ tạo hoạt cảnh lướt chuột/view scroll vào Inspector.

---

## 3. CÁC QUY TẮC BẢO VỆ (CONSTRAINTS)
- ⚠️ **Zero Dependency:** Khối Query Loop và Giao diện Theme KHÔNG lấy dữ liệu cứng, tất cả phải dùng chung cỗ máy Data Fetcher của `Ska Data Pro`. Giao tiếp qua `apply_filters`.
- ⚠️ **Decoupled:** Danh sách các Template được xây dựng trên Flat Tables. Việc kích hoạt điều kiện ưu tiên luồng Header/Footer nào (Role Conditional / URL route) sẽ do phân hệ `Ska Logic Engine` giám định và phân giải lúc truy cập để đảm bảo tách bạch dữ liệu và hiển thị.
