# SKA DATA PRO - ARCHITECTURE DOCUMENT
@status: 🟢 Done (Core Engine) | @layer: Data Engine | @context: Flat Tables, Schema, Query Builder

## 1. BOUNDARY RULES (GIỚI HẠN TRÁCH NHIỆM)
- **Tuyệt đối không sử dụng `wp_postmeta`** cho các dữ liệu App (Nhà hàng, Bất động sản, Lịch hẹn...). Phải tự sinh bảng `ska_data_*`.
- **Giữ cấu trúc lai (Hybrid):** Dữ liệu xác thực người dùng (Login, Mật khẩu, Identity) vẫn NẰM ở `wp_users`. Các bảng của Ska liên quan đến user chỉ lưu ID (Foreign Key). Không tự làm hệ thống Auth riêng.
- **Data Providers Pattern:** Không copy dữ liệu của WooCommerce vào Ska. Khi cần truy vấn dữ liệu WooCommerce, sử dụng Adapter gọi hàm `wc_get_products()` của WP Core.
- **Độc Lập UI Admin:** Dashboard của Data Pro tự load Tailwind CDN tĩnh, KHÔNG hook vào CDN chung của Builder Core để tránh gãy Layout khi Core bị tắt.

## 2. WP HOOKS EXPOSED (GIAO TIẾP XUYÊN PLUGIN)
*Dự kiến triển khai trong Phase 2:*
- `apply_filters( 'ska_data_query', $results, $query_args )`: Hook để Logic Engine hoặc Design Engine gọi dữ liệu danh sách từ Data Pro.
- `apply_filters( 'ska_data_get_row', null, $table, $id )`: Hook truy xuất nhanh 1 dòng dữ liệu duy nhất bằng Khóa chính.
- `do_action( 'ska_data_schema_installed', $template_id )`: Hook báo hiệu một Template (vd: ecommerce) vừa được cài đặt thành công.
- `apply_filters( 'ska_data_get_schema_registry', $schemas )`: Hook trả về cấu trúc mảng của tất cả các Bảng đang tồn tại (để Frontend Dynamic Tag Picker liệt kê ra Dropdown).

## 3. DATA FLOW (LUỒNG QUẢN LÝ DỮ LIỆU)
1. **Schema Initialization:** User chọn Template Gallery (UI) -> Gọi AJAX -> Server biên dịch Schema Array -> Kích hoạt `dbDelta()` -> Sinh bảng `wp_ska_data_xyz`. Hoặc tạo thủ công Custom Table `create_custom_table()`.
2. **Ký Danh Bảng Thông Minh (Table Alias):** Bản thân DB vật lý MySQL không thay đổi Tên. Tất cả Nhãn Tiếng Việt, Icon và Group App Category được định cấu hình bằng mảng `__table_info` cắm vào JSON `ska_data_dictionary`.
3. **Môi Trường Quốc Tế (i18n):** Ngành mã lõi (Plugin) hiện đang Code Base = Tiếng Việt cho quá trình Fast-MVP. Tính năng Global sẽ được chuyển dịch tự động qua WordPress `__()` bằng file `.po` trong Phase Packaging cuối.
4. **Data Retrieval (Query):** Dùng `Class_Query_Builder` nhận Array conditions từ Hook, tự Generate câu lệnh raw SQL `SELECT * FROM ska_data_xyz WHERE...` và trả về mảng dữ liệu sạch (Clean Array).
5. **Relation & Array Resolution (Virtual Constraints & Native JSON):** Thay thế giải pháp dùng chuỗi CSV truyền thống cho Relation/Multiselect. Mọi column `multi_select` và `relation` tại Bảng `ska_data_xyz` được tổ chức lưu trữ bằng kiểu **`JSON`** gốc của MySQL. Tại hàm lõi `get_table_data`, Engine tự dò Cột `relation`. Do lưu bằng cấu trúc Array/JSON, Engine dễ dàng Parse JSON -> Gom ID và thực thi 1 lệnh `WHERE IN` sang Bảng Đích. Toàn bộ ID sau đó được **Cấy (Enrich)** thẳng vào Payload dưới dạng Mảng Đối Tượng `[{id: 101, label: "Title"}]`. Nhờ cấu trúc sạch này, Frontend/API/Logic Builder được tận hưởng Native Array thay vì bị bóp băng thông bởi parser Text.
6. **Data Engine Integration:** File `class-ska-provider.php` làm Middleware. Điểm đặc biệt: API Ghi (`ska_data_insert_record`) cho phép Form Input gửi khóa (Key) bằng Alias/Label thân thiện (Ví dụ `name`, `điện thoại`) thay vì ép người dùng nhớ Column Name thật (Ví dụ `title`, `text_1`). Hệ thống tự động so khớp `__table_info` / `ska_data_dictionary` để ánh xạ thành chuẩn MySQL.
7. **Query Builder UX (Auto-Prefix):** DB Engine tự động ghép rào bảo mật cộng phân giải tiền tố `ska_data_` vào các param `table` tĩnh từ Data API để hỗ trợ Coder Dev không bao giờ gặp lỗi ngớ ngẩn No Table Detected.
7. **UI Component (DataGrid):** Gạt phăng HTML form truyền thống. Mọi tương tác Input (Boolean CSS Switch, Media Uploads) đều dùng công nghệ Live AJAX 1-Click. Quản lý chặt trật tự `mousedown` để không va chạm `z-index` với Component gốc `wp.media` của WP.
8. **DataGrid View State (URL-Driven):** Quản lý trạng thái Lọc (Filter), Sắp xếp (Sort) và Gộp nhóm (Group) bằng URL Parameters. Tính năng "Gộp Nhóm (Group)" vận hành qua câu lệnh MySQL `ORDER BY` tĩnh ở Backend, vòng lặp PHP tự sinh Divider để giữ Light Weight.
9. **Extensibility Adapter:** Sẵn sàng kết nối đa nguồn dữ liệu (như `wp_users`, WooCommerce). Các Cỗ máy đọc Data (Provider Layer) phải được móc sau khi File Lõi `ska-builder-core.php` khởi tạo hoàn toàn (Hook lúc `plugins_loaded`) để né sự cố Plugin Load Order do WP Alphabetical Loading gây ra.
10. **Rollup (Lookup Virtualization):** Các cột dữ liệu Rollup không lưu trữ giá trị nhân bản ở tầng CSDL (`NULL` trên MySQL) để bảo toàn nguyên tắc chống trùng lặp (No-Data-Redundancy). Tại điểm nút truy xuất `Data_Fetcher::enrich_rollups()`, hệ thống chạy một lượt Query O(1) chọc sang bảng đích và Cấy (Enrich) dữ liệu ảo ngược vào Array Payload ở tầng ứng dụng (PHP RAM) trước khi nhả ra REST API hoặc Logic Engine. Hỗ trợ truy xuất xuyên thẳng mảng EAV của WordPress (`wp_postmeta`, `wp_usermeta`) bằng hệ thống Nhận diện Meta Key thông minh, kết hợp bộ lọc Lưới Heuristic Filters loại bỏ tức thời các Rác hệ thống ngầm (`_wp_%`, `session_%`, `_edit_%`) để giao diện Admin Builder trở nên siêu sạch. Các thay đổi về Tham chiếu (Relation) trên UI sẽ buộc tự động Reload để ép Backend bơm lại Data ảo (Rollup) kịp thời mà không bị stale data.

## 4. BỨC TRANH JAVASCRIPT TOÀN CẢNH (ES6 + VITE + STRATEGY PATTERN)
Từng là một file monolithic, Ska DataGrid JS Frontend giờ được phân chia theo kiến trúc Modular qua Vite, bảo đảm tuân thủ Single Responsibility và Open-Closed Principle (OCP):

### 4.1. Strategy Pattern cho Cell Engine (Inline Edit)
Khi User Click vào 1 Cell bất kỳ (`.ska-editable-cell`), `CellRegistry` sẽ chặn đứng sự kiện và xác định `Strategy Class` tương ứng thông qua cấu trúc thư mục `/cells/types`:
- **`BooleanCell`**: Toggle tức thời (Nút gạt).
- **`MediaCell`**: Mở WP Media Modal.
- **`GalleryCell`**: Quản lý nhiều ảnh qua Popover thả xuống.
- **`SelectCell`**: Đọc Data Type Array và tạo Popover (Checkbox nếu multiselect).
- **`TextCell`**: Thả Text Input cho phép lưu khi Blur hoặc Enter.

Tất cả đều kế thừa Interface ngầm `BaseCell` đảm nhiệm việc tương tác duy nhất qua hàm `apiFetch('ska_data_update_cell')`.

### 4.2. Kiến Trúc Modular
Nằm tại `assets/js/src`:
- `/modules/schema.js`: Chịu trách nhiệm Cấu hình Cột, Bảng (Create, Edit, Drop).
- `/modules/rows.js`: Thêm và Xoá dòng logic.
- `/modules/modals.js`: Quản lý các Dropdown/Popup Option Cascading. Export các hàm xử lý global `window.ska*` để hỗ trợ Inline-binding từ Backend `.php` Views cũ, đóng vai trò như Bridge Pattern.
- `/modules/apps.js`: Bắt sự kiện Ajax CRUD (Create, Update, Drop) đối với App Workspace Blueprint.
- `/utils/api.js`: Tổng hợp Ajax URL và Nonce Header.

## 5. SMART OBJECT (APP BLUEPRINT) ARCHITECTURE
- **App Workspace**: Từ bỏ mô hình Table Categories (`group` string cứng). Áp dụng kiến trúc Smart Object phân cấp Workspace, định nghĩa bởi `App_Manager` và quản lý thông qua cấu trúc Option (`ska_data_apps`).
- **Data Flow Bảo Phân Phối**: Khi tạo Bảng, thông tin `app_id` được nạp thẳng xuống Từ Điển Cấu Hình Bảng. Đặc quyền bảo vệ Data: Xóa App Blueprint không kích hoạt xóa SQL Bảng, mà dịch chuyển Bảng về trạng thái Môi Trường Mặc Định (`uncategorized`) để ngăn mất dữ liệu vô ý. Tầng Admin UI Load động danh sách qua thẻ `<select>` thay vì Input tay.
- **Blueprint Portable System (JSON)**: Hệ thống đóng gói toàn bộ bảng (schema), các cột, cũng như cấu hình Tham chiếu (Relation & Rollup) sang một file Blueprint `.json` gọn nhẹ. Không đóng gói Raw Data để bảo mật.
- **Dynamic Slug Resolution**: (Biện pháp chống đụng độ Tên Bảng). Khi người dùng Nhập (Import) file JSON, Tên Bảng gốc (Vd: `teachers`) chỉ đóng vai trò là "Ký danh Tương đối" (Relative Slug). Bộ Import sẽ bắt lỗi MySQL Collision `table_exists` và tự động rẽ nhánh sinh ra Hậu tố Vật lý mới (Vd: `ska_data_teachers_1`). Sau đó biên dịch ngược (Re-wire) tệp Ký danh này vào bảng nối mạng (Relation Config), đảm bảo cấu trúc bảng dù có mang đi qua Server khác vẫn giữ được sự toàn vẹn. Kéo theo Pipeline WP Hooks `ska_import_smart_object`.
