# PROJECT MANAGER: SKA LOOP BLOCK (KHỐI VÒNG LẶP VẠN NĂNG)
@status: 🟢 Active | @last_update: 2026-04-30 | @context: Bóc tách từ Phase 4 để quản lý độc lập tiến độ khối vòng lặp dữ liệu và Zero N+1 Queries.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- Xây dựng Component/Block vòng lặp (Foreach / Map) cho Frontend.
- Tự động nhận dữ liệu mảng (Array) từ DB, lặp ra các thẻ HTML (như danh sách bác sĩ, bài viết, sản phẩm).
- **Tuyệt đối tuân thủ Zero N+1 Queries:** Không đẻ thêm query CSDL khi lặp HTML.
- **Data Binding:** Tích hợp nội suy thông minh (Mustache Syntax `{{key}}`).

---

## 2. ROADMAP & TRẠNG THÁI HIỆN TẠI (EXECUTION TRACKER)

### 2.1. Tầng Lõi PHP (Backend Core MVP) - [HOÀN THÀNH 🟢]
- [x] **Bulk Load (Zero N+1):** Triển khai API lấy hàng loạt HTML (`Organisms_API::get_bulk_html`) để lấy trước toàn bộ Symbol Template trước khi vòng lặp diễn ra.
- [x] **Hydration Engine:** Xây dựng cơ chế render siêu tốc đắp dữ liệu từ Flat Table vào HTML bằng biểu thức Mustache `{{key}}` kết hợp `preg_replace_callback`. Không dùng cơ chế nối chuỗi string thủ công.
- [x] **SkaFX Token Upgrade:** Nâng cấp cỗ máy `Lexer` của Ska Logic Engine để nhận dạng các biến hệ thống đặc thù của vòng lặp (như `$index`, `$first`, `$last`, `$even`, `$odd`).

### 2.2. Giao Diện Biên Tập (React Editor / Inspector) - [HOÀN THÀNH 🟢]
- [x] Xây dựng UI Component để người dùng Nocode chọn Bảng Nguồn (Source Table).
- [x] Giao diện cấu hình Slot và điều kiện hiển thị (Condition Matching) tích hợp SkaFX.
- [x] Đồng bộ trực quan dữ liệu vòng lặp vào Editor (Live preview mảng ảo).
- [x] **Layout Container (2026-05-04):** Tích hợp TailwindPanel, gỡ bỏ Ghost Block CSS Hacks, định danh lại Ska Loop là một thành phần Container độc lập có khả năng flex/grid tự do.

### 2.3. Tương tác Frontend (Ska Molecule Binding) - [ĐANG CHỜ 🟡]
- [ ] Tích hợp Ska Query Loop kết nối với Ska Dynamic Content để biên dịch các biến nhúng `{{...}}` ngoài giao diện thực.
- [ ] **[PENDING TEST]** Chạy bài test diện rộng: Kết hợp Bảng Data (Bác sĩ) + Vòng Lặp + Ska Select Inspector (Vừa xong trước lúc Pivot) để kiểm chứng tốc độ render mảng dữ liệu.

---

## 3. LƯU Ý / GHI CHÚ
- *Roadmap này chính thức được tái khởi động sau khi hoàn tất Phase 3 (Logic Engine Core).*
- *Trọng tâm hiện tại: Xây dựng UI Inspector trong React để người dùng cấu hình Source Table và Slot Repeater.*
