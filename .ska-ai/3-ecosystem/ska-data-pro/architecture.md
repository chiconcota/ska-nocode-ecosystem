# SKA DATA PRO - ARCHITECTURE DOCUMENT
@status: 🟢 Done (Core Engine) | @layer: Data Engine | @context: Flat Tables, Schema, Query Builder

## 1. BOUNDARY RULES (GIỚI HẠN TRÁCH NHIỆM)
- **Tuyệt đối không sử dụng `wp_postmeta`** cho các dữ liệu App (Nhà hàng, Bất động sản, Lịch hẹn...). Phải tự sinh bảng `ska_data_*`.
- **Giữ cấu trúc lai (Hybrid):** Dữ liệu xác thực người dùng (Login, Mật khẩu, Identity) vẫn NẰM ở `wp_users`. Các bảng của Ska liên quan đến user chỉ lưu ID (Foreign Key). Không tự làm hệ thống Auth riêng.
- **Data Providers Pattern:** Không copy dữ liệu của WooCommerce vào Ska. Khi cần truy vấn dữ liệu WooCommerce, sử dụng Adapter gọi hàm `wc_get_products()` của WP Core.
- **Độc Lập UI Admin:** Dashboard của Data Pro tự load Tailwind CDN tĩnh, KHÔNG hook vào CDN chung của Builder Core để tránh gãy Layout khi Core bị tắt.

## 2. WP HOOKS EXPOSED (GIAO TIẾP XUYÊN PLUGIN)
*Dự kiến triển khai trong Phase 2:*
- `apply_filters( 'ska_data_query', $results, $query_args )`: Hook để Logic Engine hoặc Design Engine gọi dữ liệu từ Data Pro mà không cần hardcode gọi Class.
- `do_action( 'ska_data_schema_installed', $template_id )`: Hook báo hiệu một Template (vd: ecommerce) vừa được cài đặt thành công.
- `apply_filters( 'ska_data_get_schema_registry', $schemas )`: Hook trả về cấu trúc mảng của tất cả các Bảng đang tồn tại (để Frontend Dynamic Tag Picker liệt kê ra Dropdown).

## 3. DATA FLOW (LUỒNG QUẢN LÝ DỮ LIỆU)
1. **Schema Initialization:** User chọn Template Gallery (UI) -> Gọi AJAX -> Server biên dịch Schema Array -> Kích hoạt `dbDelta()` -> Sinh bảng `wp_ska_data_xyz`. Hoặc tạo thủ công Custom Table `create_custom_table()`.
2. **Ký Danh Bảng Thông Minh (Table Alias):** Bản thân DB vật lý MySQL không thay đổi Tên. Tất cả Nhãn Tiếng Việt, Icon và Group App Category được định cấu hình bằng mảng `__table_info` cắm vào JSON `ska_data_dictionary`.
3. **Môi Trường Quốc Tế (i18n):** Ngành mã lõi (Plugin) hiện đang Code Base = Tiếng Việt cho quá trình Fast-MVP. Tính năng Global sẽ được chuyển dịch tự động qua WordPress `__()` bằng file `.po` trong Phase Packaging cuối.
4. **Data Retrieval (Query):** Dùng `Class_Query_Builder` nhận Array conditions từ Hook, tự Generate câu lệnh raw SQL `SELECT * FROM ska_data_xyz WHERE...` và trả về mảng dữ liệu sạch (Clean Array).
