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
