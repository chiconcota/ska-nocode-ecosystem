# AGENT SELF-IMPROVEMENT LOG (self-improve.md)
@status: ACTIVE | @last_update: 2026-06-24

> Nhật ký tự cải thiện hành vi và sửa sai của Agent. Chứa các lỗi thao tác (mistakes/anti-patterns) thực tế trong quá trình làm việc và các bộ quy tắc tự sửa lỗi bắt buộc tuân thủ.
> **Luật dọn dẹp:** File này không được vượt quá 80 dòng. Các lỗi đã giải quyết (Resolved) sau 3 phiên sẽ được lưu trữ (Archived) vào `.ska-ai/2-memory/archive/`.

---

## 🚨 DANH SÁCH LỖI HÀNH VI ĐANG ĐƯỢC GIÁM SÁT (ACTIVE)

### MISTAKE-001: Chạy CLI không khả dụng hoặc tương tác trực tiếp (Ví dụ: `mysql`)
* **Mô tả**: Chạy lệnh `mysql` hoặc các CLI tương tác trực tiếp qua bash. Khi gặp lỗi `command not found` hoặc lỗi kết nối, AI âm thầm bỏ qua hoặc thử lại liên tục mà không báo cáo cho User.
* **Quy tắc tự khắc phục**:
  1. Nếu một lệnh CLI quan trọng bị lỗi, **bắt buộc phải dừng lại và báo cáo ngay lập tức cho User** để được hỗ trợ cài đặt/cấp quyền.

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

### MISTAKE-004: Nhúng script thủ công bỏ qua hệ thống Dependency của WordPress
* **Mô tả**: In thẻ `<script>` thủ công để nhúng JS bundle có chứa các import bên ngoài (như `@wordpress/i18n`) thay vì dùng `wp_enqueue_script`, dẫn đến lỗi nghiêm trọng `ReferenceError: wp is not defined` do WordPress chưa kịp nạp thư viện lõi.
* **Quy tắc tự khắc phục**:
  1. Mọi asset script/style trong hệ sinh thái WordPress bắt buộc phải được enqueue chính quy thông qua `wp_enqueue_script` / `wp_enqueue_style` với đầy đủ dependencies thay vì in thẻ script/link thủ công.

### MISTAKE-005: Tự ý kích hoạt hoặc lạm dụng Chrome DevTools MCP / Browser Subagent
* **Mô tả**: Tự ý chạy các công cụ browser subagent hoặc chrome-devtools-mcp để tương tác hoặc kiểm thử giao diện mà không có yêu cầu trực tiếp từ người dùng, gây tốn tài nguyên và dễ gặp lỗi xác thực/CDP session.
* **Quy tắc tự khắc phục**:
  1. **Tuyệt đối không tự động kích hoạt** browser subagent hoặc Chrome DevTools MCP để kiểm thử trừ khi người dùng yêu cầu rõ ràng.
  2. Khi người dùng yêu cầu kiểm thử giao diện, hãy ưu tiên hướng dẫn họ tự kiểm thử trực tiếp trên trình duyệt của họ, hoặc chỉ sử dụng browser subagent như là giải pháp cuối cùng sau khi đã thống nhất các điều kiện cần thiết (như URL đăng nhập).

### MISTAKE-006: Thiếu đăng ký entry point Webpack khi tạo block mới
* **Mô tả**: Khi tạo một block Gutenberg mới trong thư mục `src/` của plugin `ska-no-code-design`, chạy build nhưng block không được biên dịch do thiếu khai báo entry point thủ công trong `webpack.config.js`.
* **Quy tắc tự khắc phục**:
  1. Bất cứ khi nào tạo block Gutenberg mới, **bắt buộc phải vào kiểm tra và đăng ký entry point tương ứng cho block** trong `webpack.config.js` trước khi chạy lệnh biên dịch `npm run build`.

### MISTAKE-007: Click pixel không chuẩn, đi lệch kịch bản E2E và lạm dụng Screenshot trong Browser Subagent
* **Mô tả**: Khi chạy browser subagent, thực hiện các pixel clicks không chuẩn xác, đi lệch khỏi kịch bản E2E được chỉ định, hoặc lạm dụng việc chụp ảnh màn hình (`capture_browser_screenshot`) liên tục để tự xác minh, gây tiêu tốn lượng token cực kỳ lớn không cần thiết.
* **Quy tắc tự khắc phục**:
  1. Luôn bám sát từng bước của tài liệu hướng dẫn workflow (`e2e_*.md`), không tự ý làm thêm các tính năng không được yêu cầu.
  2. Ưu tiên click/fill bằng CSS selectors hoặc Text chính xác thay vì bấm pixel click mù (trừ khi không có cách nào khác).
  3. **Hạn chế tối đa việc chụp ảnh màn hình (`capture_browser_screenshot`)**: Chỉ được phép sử dụng công cụ chụp ảnh màn hình trong các trường hợp cần thiết (Whitelist) sau:
     - **Visual & Layout Check**: Xác minh lỗi vỡ giao diện, lệch bố cục, lỗi hiển thị CSS/Tailwind, hoặc kiểm thử độ tương thích Responsive trên các thiết bị.
     - **Render Output Verification**: Xác nhận hiển thị của các thành phần đồ họa động hoặc canvas (như biểu đồ Chart.js, slider, popup modal) hoạt động ngoài Frontend.
     - **Error Debugging (Headless)**: Chụp lại màn hình tại thời điểm phát sinh lỗi (Failure) khi chạy test tự động không đầu (CI/CD hoặc Headless Browser) để hỗ trợ debug.
     - **Nghiệm thu cuối cùng (Final Handoff)**: Chụp 1-2 ảnh duy nhất của giao diện sản phẩm hoàn chỉnh ngoài Frontend để chèn vào báo cáo nghiệm thu (`walkthrough.md`).
     - *Ngoài các trường hợp trên (như CRUD dữ liệu, cấu hình Admin, kiểm thử sự kiện logic), cấm sử dụng screenshot. Thay vào đó bắt buộc phải dùng DOM (`browser_get_dom`), Console logs, Network requests hoặc truy vấn Database để xác minh.*

---

## 🟢 LỊCH SỬ LỖI ĐÃ KHẮC PHỤC (RESOLVED)
*(Trống)*

