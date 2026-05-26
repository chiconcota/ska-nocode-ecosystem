# PROJECT MANAGER: OPEN-SOURCE PREPARATION & RELEASE ROADMAP
@status: 🟡 Planning | @priority: High | @dependency: Core Cleanup

## 1. TẦM NHÌN VÀ SỨ MỆNH (VISION & MISSION)
*   **Sứ mệnh:** Chia sẻ hệ sinh thái Ska No-code Ecosystem đến cộng đồng nhà phát triển WordPress và No-code toàn cầu để cùng đóng góp xây dựng một bộ máy kéo thả xây dựng ứng dụng (App Builder) mạnh mẽ, tinh khiết, tách biệt với sự cồng kềnh truyền thống của WordPress.
*   **Mục tiêu:** 
    *   Tách biệt mã nguồn hệ sinh thái Ska ra khỏi WordPress Core để tạo bộ cài đặt nhẹ nhất.
    *   Cung cấp tài liệu tích hợp và hướng dẫn chạy cục bộ rõ ràng.
    *   Bảo vệ an toàn thông tin nhạy cảm của tác giả trước khi public.

---

## 2. BẢO VỆ QUY TẮC HỆ SINH THÁI (SKA ECOSYSTEM CONSTRAINTS)
*   **Nguyên tắc Giấy phép (Licensing):** Mọi thành phần phân phối công khai chạy trên nền WordPress bắt buộc phải sử dụng giấy phép tương thích GPL (ví dụ: GPLv2 trở lên hoặc MIT).
*   **Quy chuẩn i18n (Internationalization):** Giữ vững quy tắc dịch thuật tiếng Anh mặc định và bọc trong hàm i18n chuẩn WordPress để cộng đồng quốc tế có thể tham gia đóng góp và dịch sang ngôn ngữ của họ.
*   **Đóng gói AI-Native (.ska-ai):** Giữ lại thư mục `.ska-ai/` và các rules của Agent để lập trình viên khác có thể tận dụng AI đồng phát triển dự án hiệu quả.

---

## 3. DANH SÁCH NHIỆM VỤ (TASK LIST)

### 3.1. Dọn dẹp bảo mật & API Keys (Độ ưu tiên: Khẩn cấp)
- [x] **Task 1: Rà quét và Rotate API Keys:**
  - Kiểm tra xem có bất kỳ API key nào của OpenAI, Gemini API, AI Proxy đang được hardcode trong plugin `ska-no-code-design` không.
  - Chuyển toàn bộ cấu hình API Keys thành biến Option API hoặc Constants nạp từ `wp-config.php`.
- [x] **Task 2: Quét lịch sử Git (Git History Sanitization):**
  - Sử dụng `git-filter-repo` để loại bỏ vĩnh viễn các file cấu hình tạm thời, các file database dump hoặc logs cũ chứa thông tin nhạy cảm có trong lịch sử commit.

### 3.2. Chuẩn bị tài liệu cộng đồng (Độ ưu tiên: Cao)
- [x] **Task 3: Viết file `LICENSE`:**
  - Khởi tạo tệp `LICENSE` ở thư mục gốc sử dụng giấy phép **GPLv2** hoặc **MIT** (Đã chọn GPLv3).
- [x] **Task 4: Tạo file `README.md` chuyên nghiệp:**
  - Giới thiệu tổng quan hệ sinh thái 4 Plugins + 1 Theme.
  - Hướng dẫn cài đặt nhanh (cách cài đặt và kích hoạt từng thành phần).
  - Hướng dẫn thiết lập môi trường phát triển (LocalWP, docker-compose, compile assets).
  - Hướng dẫn cộng tác (Contributing guidelines) và cách sử dụng `.ska-ai` cho các AI agents để phát triển tiếp.

### 3.3. Tách biệt dự án sang Repository phân phối (Độ ưu tiên: Trung bình)
- [x] **Task 5: Tạo Repo chứa mã nguồn lõi:**
  - Khởi tạo repo GitHub mới (hoặc dọn dẹp repo cũ) chỉ bao gồm:
    * `.ska-ai/` (Tài liệu kiến trúc AI).
    * `.agent/` (Quy tắc cho AI coding).
    * `wp-content/plugins/ska-no-code-design/` (đã tích hợp module import html2tailwind).
    * `wp-content/plugins/ska-data-pro/`
    * `wp-content/plugins/ska-logic-engine/`
    * `wp-content/themes/ska-canvas/`
  - Loại bỏ hoàn toàn mã nguồn WordPress Core khỏi repo này để tối ưu dung lượng (chỉ giữ các thư mục plugin và theme kể trên).

### 3.4. Kích hoạt Public & Quảng bá (khỏi làm)
- [-] **Task 6: Chuyển đổi trạng thái Repo:**
  - Truy cập mục Settings trên GitHub của repo và thực hiện "Change repository visibility" sang **Public**.
- [-] **Task 7: Giới thiệu cộng đồng:**
  - Viết bài giới thiệu hệ sinh thái Ska No-code Ecosystem trên các cộng đồng lập trình viên No-code và WordPress trong và ngoài nước.

### 3.5. Tái cấu trúc Hệ thống & Tối ưu hóa Database (Lên kế hoạch cho phiên tới)
- [ ] **Task 8: Refactor Lưu trữ Logic Engine (Chuyển đổi từ wp_options sang Bảng phẳng MySQL):**
  - Thiết kế Schema cho bảng phẳng `ska_logic_workflows` (chứa các cột: `id`, `workflow_id`, `name`, `status`, `graph` [JSON], `updated_at`).
  - Viết logic tự động tạo bảng (migration) khi kích hoạt plugin `ska-logic-engine` (tận dụng `dbDelta` hoặc `$wpdb`).
  - Thay đổi các phương thức đọc/ghi workflows trong `Ska_Logic_Core` và `Ska_Workflow_Runner`: từ `get_option`/`update_option` sang truy vấn trực tiếp dòng tương ứng theo `workflow_id`.
  - Viết hàm tự động chuyển đổi (migration script) dữ liệu cũ từ `wp_options` sang bảng phẳng để bảo toàn các workflow hiện tại của dự án.
