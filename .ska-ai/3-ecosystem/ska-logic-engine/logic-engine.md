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

## 4. UI Tương Tác & Gắn Kết Lược Đồ (Schema Mapping)
- **Explicit Target Mapping:** Hỗ trợ tính năng `Mapping DB Database` trực quan cho Node Hành động Cuối. Hệ thống sử dụng AJAX lôi Lược đồ từ `Ska Data Pro` (dùng khóa an ninh `ska_data_nonce`) giúp người dùng khớp dữ liệu tự do.
- **Tính năng Đi Lên / Đi Xuống (Swap Array):** Thay vì thêm một Node ở dạng Modal cố định, UI cho phép dịch chuyển trực tiếp cục Node rảnh tay bằng Native DOM Vanilla Swap.
- **Hệ Cơ Chế Tự Phục Hồi HTTP POST (Data Healing):** Độc quyền hệ điều hành Nocode mới có. Khi Client nhập biến tự thân có chứa khoảng trắng (Vd: `ngày sinh`), máy chủ PHP sinh ra biến cục bộ theo Post (`ngày_sinh`). Logic Engine Processor và Logic Insert tự động tìm dò quy hồi bằng cách replace ` ` -> `_` để thỏa dụng đầu vào. Tôn trọng 100% người dùng Nocode.
