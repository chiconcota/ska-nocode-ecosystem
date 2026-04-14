# PROJECT MANAGER: SKA SYSTEM FRAMEWORK
@status: 🟡 In Progress | @last_update: 2026-04-13 | @context: Khung quản trị tĩnh cho hệ sinh thái Ska

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Tập trung quản trị (Centralized Hub):** Cung cấp một điểm neo (Ska System Dashboard) để chứa toàn bộ các thiết lập cài đặt (Settings), giấy phép (Licenses), cài đặt Theme (Theme Options - Colors, Typography, Logo) của tất cả các plugin (Ska Builder Core, Ska Data Pro, Ska Logic Engine, Ska Bridge).
- **Loại bỏ Plugin Mẹ cồng kềnh:** Khai tử `ska-no-code-home`. Chuyển sang mô hình **Shared Drop-in Framework** (thư viện nhúng chung) tự động kích hoạt nếu bất kỳ plugin Ska nào được cài đặt. Không bắt người dùng cài thêm plugin thừa thãi.
- **Microservice Settings Management:** Mỗi plugin vẫn giữ nguyên thiết lập độc lập của mình nhưng giao diện (UI) sẽ quy về một cửa sổ điều khiển trung tâm duy nhất trên Wp-Admin.

---

## 2. ROADMAP THEO KIẾN TRÚC FRAMEWORK

### 2.1. Kiến Trúc "Shared Drop-in Framework"
- [x] Không tạo plugin mới. Tạo folder thư viện `ska-system-framework` bên trong mỗi plugin hiện có hoặc nhúng qua composer.
- [x] Viết logic Load-Balancer: Kiểm tra plugin nào có Version Framework cao nhất thì sẽ dành quyền Khởi tạo (Boot) System Dashboard để tránh xung đột tải trùng class.
- [x] Xây dựng 1 trang Dashboard Mẹ chung trong `wp-admin` menu với cấu trúc dạng Tab, ví dụ: `?page=ska-system-dashboard`.

### 2.2. Giao Diện System Dashboard (Quản Trị Tập Trung)
- [x] **Giao diện Dashboard Mẹ:** Xây dựng framework với logic fallback thông minh, tích hợp các Module vào trang quản lý chung (`?page=ska-system-dashboard`).
- [x] **Dev Mode Settings:** Hoàn thiện backend cho công tắc Dev Mode, móc nối vào `Ska_Dynamic_Content` để hiển thị Error Badges trực quan khi ở chế độ nhà phát triển.
- [x] **Khu vực Extensions:** Tách tính năng Ska AI Architect ra khỏi Header và đặt làm thẻ cấu hình trong Extensions (Cắm API Key, Prompts).
- [ ] **Tab Theme Options:** Cấu hình Token Color (Primary, Secondary), Typography... (Deferred to next phase).

### 2.3. Tính Năng Cao Cấp (Milestone 4 - Post MVP)
- [ ] Tính năng Import JSON Blueprint (Import Design, Schema, Workflows).
- [ ] Triển khai Code Hook thực tế cho 2 nút Danger Zone (Clear Context, Flush JIT).
- [ ] Implement luồng gửi Request API thực tế của thẻ cấu hình Ska AI Architect.
