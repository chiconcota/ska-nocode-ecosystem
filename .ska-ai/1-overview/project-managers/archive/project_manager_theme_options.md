# PROJECT MANAGER: THEME OPTIONS & DESIGN TOKENS
@status: COMPLETED | @phase: 4.4 | @focus: Ska System Framework & Tailwind Integration

## 1. TỔNG QUAN (OVERVIEW)
Hệ thống **Theme Options & Design Tokens** đóng vai trò thiết lập nền tảng nhận diện thương hiệu (Typography, Logo, Colors) cho toàn bộ Ứng dụng/Website xây dựng bằng Ska Builder.
Tính năng này sẽ được tích hợp trực tiếp vào **Ska System Framework** (Dashboard quản trị) thay vì tạo màn hình cài đặt riêng lẻ, đảm bảo triết lý quản trị tập trung (Unified Canvas).

## 2. KIẾN TRÚC LÕI (CORE ARCHITECTURE)
- **Data Storage:** Dữ liệu cấu hình Design Tokens (Màu sắc, Font chữ, Cấu hình Layout) được lưu an toàn trong bảng phẳng chuyên biệt `ska_data_sys_presets` qua column `json_content`, thay vì dùng `wp_options` của WordPress.
- **Physical Caching (Bộ đệm vật lý):** Để tăng tốc truy xuất và đáp ứng độ trễ 0ms cho JIT Tailwind Editor/Frontend, hệ thống tự động dịch Payload Database sang trạng thái tệp vật lý `.json` lưu tại `wp-content/uploads/ska-data/tokens.json`. Điều này giải phóng CPU Database khỏi việc query cho mỗi lần render Stylesheet.
- **Tailwind Integration:** Cỗ máy JIT Compiler sẽ tự động đọc cấu hình màu sắc và font từ `tokens.json` và inject vào cấu hình tailwind config nội bộ.

---

## 3. LỘ TRÌNH TRIỂN KHAI (MILESTONES & TASKS)

### Milestone 1: Ska System Dashboard UI & JSON Storage
*Tích hợp giao diện điều khiển vào System Dashboard và cơ chế lưu trữ chuẩn Flat Table.*
- [x] Bổ sung Tab mới: **"Theme Options"** (hoặc **Design Tokens**) vào giao diện của Ska System Dashboard.
- [x] Code UI bằng Alpine.js: Cung cấp Color Picker cho các màu chuẩn hệ thống (Primary, Secondary, Accent, Background) và Typography Picker (Heading Font, Body Font).
- [x] Tích hợp Input cho Logo (hỗ trợ chọn từ Media Library).
- [x] Map API: Nối nút "Save Changes" để đẩy payload dạng JSON vào bảng `ska_data_sys_presets`.

### Milestone 2: Physical JSON Caching Layer
*Đồng bộ dữ liệu cấu hình xuống file vật lý để Tailwind JIT có thể đọc với tốc độ bàn thờ.*
- [x] Viết Hook lắng nghe khi bản ghi cấu hình trong `ska_data_sys_presets` được insert/update.
- [x] Khởi tạo module xử lý file để trích xuất payload JSON và lưu thành file `wp-content/uploads/ska-data/tokens.json`.
- [x] Xử lý chống lỗi ghi file (Permissions) và in thông báo lỗi rõ ràng nếu không tạo được cache.

### Milestone 3: Tailwind JIT Config Injection
*Bơm Design Tokens vào cấu trúc biên dịch của Tailwind.*
- [x] Cập nhật `Ska_No_Code_Design\Core` để khi JIT chạy, nó sẽ đọc file `tokens.json`.
- [x] Render các token màu sắc và font chữ vào mảng `theme.extend.colors` và `theme.extend.fontFamily` của Tailwind Config nội tuyến (hoặc file cấu hình JIT).
- [x] Đảm bảo cơ chế làm mới (Flush Cache): Khi người dùng đổi Theme Option, xóa Transient Cache của JIT để Frontend được compile lại CSS mới ngay lập tức.

### Milestone 4: Kiểm thử E2E (End-to-End)
*Xác nhận tính năng từ Frontend đến Backend.*
- [x] Test Case 1: Nhập màu Primary là `#ff0000`, sử dụng class `bg-primary` ở Gutenberg Editor xem có đổi màu đỏ không.
- [x] Test Case 2: Kiểm tra Frontend render CSS sau khi xóa bộ đệm.
- [x] Test Case 3: Chắc chắn rằng Database không bị quá tải khi người truy cập trang tăng đột biến (nhờ cơ chế đọc file tĩnh).

---

## 4. QUY TẮC BẢO VỆ (CONSTRAINTS)
1. **Zero wp_options:** Cấm tuyệt đối việc sử dụng Options API mặc định của WordPress (`add_option`, `get_option`) để lưu Theme Options.
2. **Zero-Latency JIT:** Tailwind Compiler không được phép gọi DB Queries để lấy Token màu sắc. Bắt buộc phải đọc từ file vật lý `tokens.json`.
3. **Smart Defaults:** Luôn có giá trị dự phòng (Fallback) an toàn trong trường hợp file `tokens.json` bị xóa hoặc chưa được sinh ra.
