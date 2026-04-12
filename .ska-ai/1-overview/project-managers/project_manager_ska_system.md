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
- [ ] Không tạo plugin mới. Tạo folder thư viện `ska-system-framework` bên trong mỗi plugin hiện có hoặc nhúng qua composer.
- [ ] Viết logic Load-Balancer: Kiểm tra plugin nào có Version Framework cao nhất thì sẽ dành quyền Khởi tạo (Boot) System Dashboard để tránh xung đột tải trùng class.
- [ ] Xây dựng 1 trang Dashboard Mẹ chung trong `wp-admin` menu với cấu trúc dạng Tab, ví dụ: `?page=ska-system&tab=general`.

### 2.2. Giao Diện System Dashboard (Quản Trị Tập Trung)
- [ ] **Tab 1: Theme Options (Nhận Diện Thương Hiệu):** Chứa các Token Color (Primary, Secondary), Typography, Logo. Cấu hình này sẽ feed thẳng vào `Ska No-code Design` JIT Compiler, không cần hardcode CSS brand colors.
- [ ] **Tab 2: Ecosystem Status:** Hiển thị danh sách các mảnh ghép (Data Pro, Logic Engine) đang kích hoạt trơn tru hay thiếu file.
- [ ] **Tab 3: Tích hợp API Bên Thứ 3:** Cấu hình Keys cho SendGrid, Stripe/Paypal hoặc Zalo ZNS nếu có kích hoạt.

### 2.3. Quản Lý Symbol & Extension
- [ ] Thêm kho quản lý các Custom Block (tái sử dụng) và hệ thống Template/Extension mua từ bên ngoài.
