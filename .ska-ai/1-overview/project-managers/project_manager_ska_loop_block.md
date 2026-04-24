# PROJECT MANAGER: SKA LOOP BLOCK (KHỐI VÒNG LẶP VẠN NĂNG)
@status: ⏸️ Paused (Chờ Rebuild Logic Engine) | @last_update: 2026-04-24 | @context: Bóc tách từ Phase 4 để quản lý độc lập tiến độ khối vòng lặp dữ liệu và Zero N+1 Queries.

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

### 2.2. Giao Diện Biên Tập (React Editor / Inspector) - [CHƯA XONG 🔴]
- [ ] Xây dựng UI Component để người dùng Nocode chọn Bảng Nguồn (Source Table).
- [ ] Giao diện cấu hình Slot và điều kiện hiển thị (Condition Matching) tích hợp SkaFX.
- [ ] Đồng bộ trực quan dữ liệu vòng lặp vào Editor (Live preview mảng ảo).

### 2.3. Tương tác Frontend (Ska Molecule Binding) - [ĐANG CHỜ 🟡]
- [ ] Tích hợp Ska Query Loop kết nối với Ska Dynamic Content để biên dịch các biến nhúng `{{...}}` ngoài giao diện thực.
- [ ] **[PENDING TEST]** Chạy bài test diện rộng: Kết hợp Bảng Data (Bác sĩ) + Vòng Lặp + Ska Select Inspector (Vừa xong trước lúc Pivot) để kiểm chứng tốc độ render mảng dữ liệu.

---

## 3. LƯU Ý / GHI CHÚ
- *Roadmap này đang bị tạm hoãn (Paused) để nhường đường cho chiến dịch "Đập đi xây lại giao diện Node của Ska Logic Engine".*
- *Ngay sau khi Logic Engine UX được cải thiện thành công chuẩn No-code, chúng ta sẽ quay lại file này để tiếp tục thực thi phần 2.2 và 2.3.*
