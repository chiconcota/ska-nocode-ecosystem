# AGENT SELF-IMPROVEMENT LOG (self-improve.md)
@status: ACTIVE | @last_update: 2026-07-21

> Nhật ký tự cải thiện hành vi và sửa sai của Agent. Chứa các lỗi thao tác thực tế và quy tắc tự sửa lỗi.
> **Luật dọn dẹp:** File này không được vượt quá 80 dòng. Các lỗi đã giải quyết (Resolved) sau 3 phiên sẽ được lưu trữ.

---

## 🚨 DANH SÁCH LỖI HÀNH VI ĐANG ĐƯỢC GIÁM SÁT (ACTIVE)

### MISTAKE-001: CLI mysql tương tác trực tiếp
- Lỗi: Chạy CLI mysql trực tiếp gây treo.
- Sửa đổi: Dừng và báo cáo ngay cho User để được hỗ trợ.

### MISTAKE-002: Vi phạm chuẩn i18n & Text cứng
- Lỗi: Viết text hiển thị tiếng Việt hoặc text cứng không bọc i18n.
- Sửa đổi: Tất cả text hiển thị UI phải là tiếng Anh và bọc i18n: `__( 'Text', 'skaaa-domain' )`.

### MISTAKE-003: Lạm dụng E2E Browser Subagent tốn token
- Lỗi: Chạy E2E tự động quá phức tạp trong admin.
- Sửa đổi: Chỉ dùng E2E cho trường hợp đơn giản, ưu tiên lập checklist cho User test tay.

### MISTAKE-004: Nhúng script thủ công bỏ qua WP Dependency
- Lỗi: In script cứng không enqueue dẫn đến lỗi `wp is not defined`.
- Sửa đổi: Luôn dùng `wp_enqueue_script` / `wp_enqueue_style` với đầy đủ dependencies.

### MISTAKE-005: Tự ý chạy Chrome DevTools MCP / Browser Subagent
- Lỗi: Chạy tools browser khi không có yêu cầu.
- Sửa đổi: Chỉ chạy khi User yêu cầu rõ ràng.

### MISTAKE-006: Thiếu đăng ký Webpack entry point
- Lỗi: Tạo block mới trong `src/` nhưng quên khai báo trong `webpack.config.js`.
- Sửa đổi: Luôn kiểm tra và đăng ký entry point webpack trước khi build.

### MISTAKE-007: Lạm dụng Screenshot trong Browser Subagent
- Lỗi: Chụp ảnh màn hình liên tục gây tốn token lớn.
- Sửa đổi: Chỉ chụp khi cần check layout, render canvas, failure debug, hoặc handoff.

### MISTAKE-008: ArtifactMetadata sai chỗ
- Lỗi: Dùng ArtifactMetadata cho file code nguồn dự án ngoài thư mục artifacts.
- Sửa đổi: Chỉ dùng cho file markdown trong thư mục artifacts của conversation.

### MISTAKE-009: Thiếu Fail-Safe Fallback cho Dynamic UI
- Lỗi: Import thiếu component hoặc thiếu map icon gây crash trang.
- Sửa đổi: Luôn có fail-safe fallback (VD: icon mặc định `ServerCog` khi lỗi).

### MISTAKE-010: Xung đột bộ gõ tiếng Việt (Style Reflow)
- Lỗi: Reset hash compile (`previousHash`) liên tục mỗi 5s làm ghi đè CSSOM, gây nhảy con trỏ và mất chữ bộ gõ `fcitx5-lotus`.
- Sửa đổi: Lưu vết `activeIframeDoc`, chỉ reset hash 1 lần duy nhất khi reload hoặc đổi iframe.

### MISTAKE-012: Thẻ Editor Selector bị CSV trượt Selector
- Lỗi: Nối chuỗi CSS selector dạng CSV dễ bị trượt vế và áp sai thuộc tính lên toàn bộ iframe wrapper.
- Sửa đổi: Luôn gom nhóm editor selector dưới `:where(.editor-styles-wrapper)` để đảm bảo tính scoped tuyệt đối.

### MISTAKE-013: Sự kiện Click tự đính thêm dấu # vào URL
- Lỗi: Sử dụng handler `@click` trên nút bấm hoặc liên kết mà không dùng modifier `.prevent` gây nhảy cuộn trang và đính `#` URL.
- Sửa đổi: Tất cả handler sự kiện `@click` trong Alpine.js bắt buộc dùng `@click.prevent` để giữ URL luôn sạch.

---

## 🟢 LỊCH SỬ LỖI ĐÃ KHẮC PHỤC (RESOLVED)
*(Trống)*
