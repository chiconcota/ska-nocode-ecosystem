# AGENT SELF-IMPROVEMENT LOG (self-improve.md)
@status: ACTIVE | @last_update: 2026-06-07

> Nhật ký tự cải thiện hành vi và sửa sai của Agent. Chứa các lỗi thao tác (mistakes/anti-patterns) thực tế trong quá trình làm việc và các bộ quy tắc tự sửa lỗi bắt buộc tuân thủ.
> **Luật dọn dẹp:** File này không được vượt quá 80 dòng. Các lỗi đã giải quyết (Resolved) sau 3 phiên sẽ được lưu trữ (Archived) vào `.ska-ai/2-memory/archive/`.

---

## 🚨 DANH SÁCH LỖI HÀNH VI ĐANG ĐƯỢC GIÁM SÁT (ACTIVE)

### MISTAKE-001: Chạy CLI không khả dụng hoặc tương tác trực tiếp (Ví dụ: `mysql`)
* **Mô tả**: Tự ý chạy lệnh `mysql` hoặc các CLI tương tác trực tiếp qua bash. Khi gặp lỗi `command not found` hoặc lỗi kết nối, AI âm thầm bỏ qua hoặc thử lại liên tục mà không báo cáo cho User.
* **Quy tắc tự khắc phục**:
  1. TUYỆT ĐỐI không gọi trực tiếp `mysql`. Nếu cần truy vấn DB, hãy dùng WP-CLI (`wp db query ...`) hoặc viết script PHP chạy ngầm qua API WordPress (`$wpdb`).
  2. Nếu một lệnh CLI quan trọng bị lỗi, **bắt buộc phải dừng lại và báo cáo ngay lập tức cho User** để được hỗ trợ cài đặt/cấp quyền.

### MISTAKE-002: Vi phạm chuẩn đa ngôn ngữ (i18n) và viết Text cứng trong Code
* **Mô tả**: Viết chuỗi hiển thị (labels, placeholders, messages) bằng tiếng Việt hoặc tiếng Anh dạng "text cứng" (không bọc qua hàm dịch của WordPress) trong mã nguồn PHP/JS.
* **Quy tắc tự khắc phục**:
  1. Tuân thủ tuyệt đối quy tắc `wp-architect.md` (Rule 10): Mọi chuỗi hiển thị UI mặc định phải viết bằng **tiếng Anh** và bọc trong hàm i18n chuẩn:
     - PHP: `__( 'Text', 'ska-domain' )`, `esc_html_e( 'Text', 'ska-domain' )`...
     - JS/JSX: `__( 'Text', 'ska-domain' )` (từ thư viện `@wordpress/i18n`).
  2. Ngay cả khi User yêu cầu bằng tiếng Việt, mã nguồn sinh ra phải viết bằng tiếng Anh bọc i18n. Chỉ có chú thích code (Comments) và PHPDoc là được viết tiếng Việt.

### MISTAKE-003: Lạm dụng Browser Subagent kiểm thử E2E phức tạp gây tốn token
* **Mô tả**: Sử dụng browser subagent để thực hiện một chuỗi thao tác kiểm thử giao diện quản trị phức tạp liên tục, dẫn đến tiêu tốn lượng token lớn mà không mang lại kết quả kiểm thử tối ưu.
* **Quy tắc tự khắc phục**:
  1. Thay vì tự chạy browser subagent để thực hiện kiểm thử E2E phức tạp trong admin, ưu tiên xây dựng trước một tài liệu quy trình kiểm thử E2E chi tiết (`test-*-e2e.md`) để bàn giao cho User tự kiểm thử tay nhanh chóng và hiệu quả.
  2. Chỉ sử dụng browser subagent cho các trường hợp kiểm thử tự động đơn giản, bắt buộc (như xác nhận hiển thị UI cơ bản hoặc screenshot ban đầu) và tối ưu hóa số bước thao tác.

---

## 🟢 LỊCH SỬ LỖI ĐÃ KHẮC PHỤC (RESOLVED)
*(Trống)*
