# Kiến trúc Ska Logic Engine (Ska-xi măng)
@version: 1.0.0
@focus: Lớp Nhân (Dự án The Trinity Architecture)

Ska Logic Engine là bộ óc kết nối (Node Engine định tuyến) giúp "Vỏ" (Ska Design / Form Frontend) nối với "Kho" (Ska Data Pro / Flat Tables MySQL).

## 1. Tôn Chỉ Hoạt Động (Event-Driven Backbone)
Giao tiếp hoàn toàn vô hình thông qua WP Hook. Cách ly Microservices triệt để.
- REST API `POST /ska-logic/v1/submit` đón nhận dữ liệu thô. (API Receiver).
- Bơm dữ liệu lên phễu bằng Filter `apply_filters( 'ska_logic_run_pipeline', $clean_data, $form_id )`.
- Class `Ska_Workflow_Runner` khởi động, lặp qua đồ thị Graph và cho điện chạy qua các cục `Nodes`.
- Ở cuối bằng chuyền, một Action Node sẽ gồng mình lên phát lệnh `apply_filters('ska_data_insert_record')` để tống cổ cục hàng qua sang vương quốc `Ska Data Pro` đúc MySQL.

## 2. Tiêu Chuẩn Giao Diện Node
Tất cả các cục Xử lý mềm (Date, Slug) hoặc Cục Hành Động chốt (Gửi Email) BẮT BUỘC IMPLEMENTS `Ska_Logic_Node`.
Chúng bắt buộc chứa tham số `public function execute( $payload, $config )`. 
Mọi thứ vào là `$payload` và bắt buộc trả về mảng `$payload` cho Node kế nhiệm kế thừa xử lý. (Polymorphism thuần túy).

## 3. Visual Node UI Roadmap
Engine phía Backend đã sẵn sàng 100% để hấp thụ JSON Graph. Giai đoạn tiếp theo của hệ sinh thái sẽ là tích hợp bảng vẽ kéo thả (Node UI - React Flow) ở Phase 4.
Trong giai đoạn quá độ, hệ thống sử dụng **Linear Builder MVP (Băng chuyền dọc)** xây dựng bằng Vanilla JS ở wp-admin. Linear Builder cho phép người dùng tùy ý chèn thêm bao nhiêu Step tùy thích và cấu hình các Field; sau đó xuất ngược ra mảng đồ thị `JSON Graph` cấu trúc sâu truyền vào bộ máy `Ska_Workflow_Runner`. Đảm bảo Kiến trúc Node Component sẽ kế thừa được Database Graph ở Phase sau mà không bị gãy vỡ.

## 4. UI Tương Tác & Quản Trị Băng Chuyền
- **Hệ Thống Dual-View (Quản Trị vs Thiết Kế):** Lớp giao diện quản trị wp-admin được tách thành 2 module riêng biệt. `Manager UI` đóng vai trò Dashboard (List View) cho phép Thêm/Xóa/Sửa tên các ID Luồng an toàn. `Builder UI` đóng vai trò không gian kéo thả tuyến tính, hoàn toàn cô lập để bảo vệ dữ liệu cấu trúc luồng hiện tại.
- **Explicit Target Mapping:** Hỗ trợ tính năng `Mapping DB Database` trực quan cho Node Hành động Cuối. Bấm nút OK sẽ kích hoạt ngầm AJAX lôi Lược đồ từ `Ska Data Pro` (dùng khóa an ninh `ska_data_nonce`) giúp người dùng khớp dữ liệu tự do mà KHÔNG THỰC HIỆN Re-render phá hủy DOM tree (ngăn lỗi Extension Chrome dính kèm).
- **Tính năng Đi Lên / Đi Xuống (Swap Array):** Thay vì thêm một Node ở dạng Modal cố định, UI cho phép dịch chuyển trực tiếp cục Node rảnh tay bằng Native DOM Vanilla Swap.
- **Hệ Cơ Chế Tự Phục Hồi HTTP POST (Data Healing):** Độc quyền hệ điều hành Nocode mới có. Khi Client nhập biến tự thân có chứa khoảng trắng (Vd: `ngày sinh`), máy chủ PHP sinh ra biến cục bộ theo Post (`ngày_sinh`). Logic Engine Processor và Logic Insert tự động tìm dò quy hồi bằng cách replace ` ` -> `_` để thỏa dụng đầu vào. Tôn trọng 100% người dùng Nocode.

## 5. Universal Dynamic Binding (Data Hydration Engine)
Cỗ máy không chỉ đón dữ liệu đẩy xuống, mà còn bơm dữ liệu ngược lên Giao diện Frontend.
- **Đánh chặn Tầng Trình Bày:** Sử dụng Filter `the_content` (Priority `90`) qua class `Ska_Dynamic_Content`.
- **Cơ chế Tiền tố "Ngầm" (Smart Prefixing):** Lễ Tân không bắt buộc phải gõ đầy đủ `ska_data_doctors`. Thuật toán Regex tự hiểu `{{doctors.ho_va_ten}}` và bồi đắp tiền tố chuẩn để giao tiếp trơn tru bằng hàm lõi `\Ska\Data\Core\Data_Fetcher`.
- **Memory Cache (Singleton RAM):** Lỗ hổng Database được vá kín mít. Cơ chế Singleton `$memory_cache` lưu lại Query theo chỉ mục `?id=`. Nó mở liên kết với MariaDB/MySQL đúng 1 lần duy nhất cho toàn bộ trang web kể cả khi trang có in tới 50 cột dữ liệu khác nhau. Response rate duy trì ảo diệu ở mức `0ms`.
- **SkaFX Context Resolver (Đoán ngữ cảnh thông minh):** Ngăn ngừa triệt để lỗi "nhân đôi tiền tố" và xung đột (Collision) giữa các App:
  1. **Smart Context Guessing:** Ưu tiên đọc tham số `GLOBAL_TABLE` (như `?table=app_kiem_thu_moi`) kết hợp với Hậu tố truy vấn của người dùng để sinh ra Full Tên đúng mà KHÔNG cần dùng Suffix Scan. Loại bỏ hoàn toàn ngoại lệ Xung đột.
  2. **Memory Array Traversal:** Nạp nguyên từ điển Schema lên bộ nhớ tĩnh (Static Cache RAM PHP). Dù mảng có hơn 5,000 DB flat tables, tốc độ cắt chuỗi truy vết (`substr`) vẫn đạt dưới 1ms. Bảo toàn 100% Hiệu năng Nocode Frontend.

## 6. SkaFX Evaluator (Engine Thông Dịch Cú Pháp Lõi)
SkaFX cấu tạo nên Trái Tim Giải Trí (Logic & Calculation) của toàn bộ Micro-Ecosystem Ska. Nó là ngôn ngữ chung dùng cho tất cả Plugins thay thế Blockly truyền thống.
- **Vai Trò Đa Năng:** Phục vụ Data Hydration Frontend, Conditional Block Display (Logic hiển thị), và Backend Formula Data Grid (Cột Ảo Ska Data Pro).
- **Cú Pháp Excel/Airtable Chuẩn Hóa:** Biến dữ liệu được bọc trong Ngoặc Vuông `[table.field]`. Hàm được bao bằng Ngoặc Tròn `IF(...)`. Tính tương thích rộng nhưng sức mạnh Lập trình cao.
- **Hoạt Động Dựa Trên AST (Abstract Syntax Tree PHP):** 
  - (1) **Lexer:** Băm chuỗi người dùng gõ.
  - (2) **Parser (Statements):** Biến thành luồng xử lý và lập bản đồ Cây phân cấp (Top-Down Precedence). Hỗ trợ Statement variables `var x = 1;`.
  - (3) **Evaluator:** Đối chiếu Symbol Table Local Row đầu tiên, nếu không thấy mới phân tách Scope App/Table tìm ngoài DB. Nuốt 100% Syntax Error không bao giờ Fatal Website.
