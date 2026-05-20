# TEST WORKFLOW: AUTO-GENERATED CRUD & SHADOW SCRATCHPAD
**Kịch bản:** Xây dựng Ứng dụng "Quản lý Đặt Phòng Khách Sạn" từ A-Z
**Mục tiêu:** Kiểm thử quy trình tự động sinh giao diện (One-Click App Generator), thiết lập mối quan hệ (Relational DB) và công cụ soạn thảo Rich Text ẩn (Shadow Scratchpad).

---

## BƯỚC 1: KHỞI TẠO CƠ SỞ DỮ LIỆU (DATA FOUNDATION)
*Bắt đầu từ việc định nghĩa dữ liệu cốt lõi trong hệ thống với 2 bảng dữ liệu có liên kết.*

### Bảng 1: Phòng Khách Sạn (Danh mục phòng)
1. Đăng nhập vào WordPress Admin -> **Ska Data Pro** -> **Tạo Bảng Mới (Add New Table)**.
2. Nhập Tên Bảng: `Phòng Khách Sạn`.
3. Thêm các cột (Columns) sau:
   - `Tên Phòng` (Kiểu: **Text**, Bắt buộc: Có)
   - `Loại Phòng` (Kiểu: **Select**, Tùy chọn: `Standard`, `Deluxe`, `VIP`)
   - `Giá mỗi đêm` (Kiểu: **Number**)
   - `Mô tả chi tiết` (Kiểu: **Long Text**) -> *Để test tính năng Scratchpad.*
   - `Trạng thái` (Kiểu: **Select**, Tùy chọn: `Trống`, `Đã Đặt`, `Bảo Trì`)
4. Bấm **Lưu Bảng (Save Table)**.

### Bảng 2: Lịch Đặt Phòng (Quản lý Doanh thu)
1. Bấm **Tạo Bảng Mới (Add New Table)**.
2. Nhập Tên Bảng: `Lịch Đặt Phòng`.
3. Thêm các cột (Columns) sau:
   - `Mã Đặt Phòng` (Kiểu: **Text**, Bắt buộc: Có)
   - `Phòng Đặt` (Kiểu: **Relation**, Liên kết đến bảng: `Phòng Khách Sạn`) -> *Để kết nối 2 bảng.*
   - `Khách Hàng` (Kiểu: **Text**)
   - `Ngày Check-in` (Kiểu: **Date**)
   - `Ngày Check-out` (Kiểu: **Date**)
   - `Tổng Tiền` (Kiểu: **Number**) -> *Dành cho việc tính doanh thu của Admin.*
   - `Trạng thái Thanh toán` (Kiểu: **Select**, Tùy chọn: `Chưa thanh toán`, `Đã thanh toán`)
4. Bấm **Lưu Bảng (Save Table)**.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria):**
> - Cả 2 bảng phẳng `ska_data_phong_khach_san` và `ska_data_lich_dat_phong` được tạo thành công trong MySQL Database.
> - Cột `phong_dat` trong bảng `Lịch Đặt Phòng` được tạo chính xác với thuộc tính liên kết `Relation` trỏ tới bảng `Phòng Khách Sạn`.
> - Hệ thống tự động thiết lập chỉ mục `INDEX` trên cột khóa ngoại `phong_dat` tại MySQL để tối ưu truy vấn.

---

## BƯỚC 2: KÍCH HOẠT ONE-CLICK APP GENERATOR
*Chỉ với vài thao tác click, hệ thống sẽ xây dựng toàn bộ Frontend cho 2 bảng.*

1. Trong **Ska Data Pro**, mở bảng `Phòng Khách Sạn`.
2. Mở **App Portal Settings** ở cột bên phải.
   - Tích chọn **Kích hoạt App Portal**.
   - Nhập URL Slug: `phong-khach-san`.
   - Bấm **"Tự động sinh App Portal ngay"** và chờ thông báo thành công.
3. Chuyển sang bảng `Lịch Đặt Phòng`.
4. Mở **App Portal Settings**:
   - Tích chọn **Kích hoạt App Portal**.
   - Nhập URL Slug: `lich-dat-phong`.
   - (Gợi ý: Nếu hệ thống hỏi có muốn tự động thêm cột "Nội dung" không, bạn có thể bỏ qua vì bảng này chỉ lưu số liệu).
   - Bấm **"Tự động sinh App Portal ngay"** và chờ thông báo thành công.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria): [ĐÃ PASS ✅]**
> - [x] Sau khi sinh app, hệ thống hiển thị Modal Success với các tùy chọn điều hướng nhanh: Edit List View, Edit Detail View, View Frontend, Manage Templates.
> - [x] Sinh ra đúng các tệp Template (`phong-khach-san-list`, `phong-khach-san-detail`, `lich-dat-phong-list`, `lich-dat-phong-detail`) trong CPT `ska_theme_builder` và các Organism layout tương ứng trong bảng phẳng hệ thống.
> - [x] Truy cập được các đường dẫn Frontend: `/portal/phong-khach-san` và `/portal/lich-dat-phong` mà không bị lỗi 404.

---

## BƯỚC 3: KIỂM THỬ THÊM MỚI NHANH (QUICK EDIT MODAL)
*Hệ thống sẽ tự động giấu trường Long Text ra khỏi Modal tạo nhanh để tối ưu UX.*

1. Truy cập Frontend: `yoursite.com/portal/phong-khach-san`.
2. Bấm nút **"Thêm Mới"** ở góc phải màn hình.
3. **Hành vi Kỳ vọng (Cực kỳ quan trọng):** 
   - Một Modal "Quick Edit" hiện ra.
   - Bạn sẽ thấy các trường: `Tên Phòng`, `Loại Phòng`, `Giá mỗi đêm`, `Trạng thái`.
   - **Tuyệt đối KHÔNG THẤY** trường `Mô tả chi tiết` ở đây.
4. Điền dữ liệu test: `P101`, `Deluxe`, `1500000`, `Trống`. Bấm **Lưu**.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria):**
> - Modal "Quick Edit" hiện lên mà không chứa trường Long Text (`Mô tả chi tiết`).
> - Khi bấm Lưu, dữ liệu được gửi qua AJAX thành công (HTTP 200), Modal đóng lại và dòng dữ liệu phòng `P101` ngay lập tức xuất hiện trên List View ở Frontend mà không cần tải lại trang.

---

## BƯỚC 4: KIỂM THỬ THÊM ĐẶT PHÒNG & LIÊN KẾT DỮ LIỆU (RELATION)
*Kiểm tra khả năng hiển thị liên kết giữa Bảng Lịch Đặt Phòng và Bảng Phòng Khách Sạn.*

1. Đổi sang trang Frontend: `yoursite.com/portal/lich-dat-phong`.
2. Bấm nút **"Thêm Mới"**.
3. Trong form tạo Lịch đặt phòng:
   - `Mã Đặt Phòng`: BK001
   - `Phòng Đặt`: Mở dropdown chọn phòng (Sẽ thấy `P101` vừa tạo ở bước 3 hiện ra để chọn nhờ có trường Relation).
   - Nhập các ngày check-in/check-out.
   - `Tổng Tiền`: 1500000.
4. Bấm **Lưu**. Dữ liệu sẽ đổ ra List View của `Lịch Đặt Phòng`. Admin có thể dùng bảng này để thống kê tổng doanh thu sau này.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria):**
> - Dropdown chọn phòng hiển thị chính xác tên phòng `P101` thay vì ID thô của bản ghi.
> - Sau khi lưu, bảng List View của Lịch đặt phòng render cột `Phòng Đặt` là `P101` (thông qua cơ chế Reverse Lookup/Rollup hydrate thành công từ bảng quan hệ), đảm bảo không xảy ra hiện tượng N+1 Query.

---

## BƯỚC 5: KIỂM THỬ DETAIL VIEW & SHADOW SCRATCHPAD
*Kịch bản phức tạp: Nhập liệu văn bản dài (Rich Text) có hình ảnh trên trang Chi tiết.*

1. Quay lại trang `yoursite.com/portal/phong-khach-san`, bấm trực tiếp vào phòng `P101`.
2. Trình duyệt chuyển sang trang **Detail View**.
3. **Cải tiến Giao diện Premium (2026-05-20):**
   - Bạn sẽ thấy một Header sang trọng có Breadcrumb/Back link trỏ về trang danh sách (`← Quay lại danh sách`) cùng tiêu đề `Chi tiết: Phòng Khách Sạn`.
   - Các trường dữ liệu ngắn (`Tên Phòng`, `Loại Phòng`, `Giá mỗi đêm`, `Trạng thái`) được tự động gom nhóm vào bố cục Grid 2 cột sắc nét, tối ưu không gian hiển thị.
   - Trường `Mô tả chi tiết` (Giao diện **Ska Form Rich Text**) được hiển thị full-width bên dưới Grid.
   - Chân trang form có đường viền ngăn cách tinh tế cùng thanh tác vụ Actions gom gọn nút **"Hủy bỏ"** và **"Lưu Thay Đổi"** ở góc phải.
4. Bấm vào nút **"Mở Trình Thiết Kế"**.
5. **Hành vi Kỳ vọng:** Một Iframe tải giao diện Gutenberg quen thuộc. (Sử dụng ID phái sinh).
6. Soạn thảo: Đặt Tiêu đề H2, chèn ảnh, viết in đậm, v.v.
7. Gõ xong, bấm **"Cập nhật / Đóng"** ở góc trên cùng của Iframe.
8. Bấm nút **"Lưu (Save / Submit)"** ở dưới cùng trang Detail View để lưu vào Database.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria):**
> - Trang Detail View áp dụng bố cục Premium Layout: các trường ngắn chia Grid 2 cột sắc nét, có nút Breadcrumb `← Quay lại danh sách` hoạt động đúng và thanh footer Form Actions cố định.
> - Bấm "Mở Trình Thiết Kế" nạp thành công Iframe chứa Gutenberg Editor với ID của Custom Post Type nháp `ska_scratchpad`.
> - Sau khi "Cập nhật / Đóng", CPT nháp `ska_scratchpad` bị xóa ngay lập tức khỏi CSDL (bảo vệ dung lượng database).
> - Nhấp "Lưu Thay Đổi" thành công, trường `mo_ta_chi_tiet` trong database `ska_data_phong_khach_san` lưu đúng đoạn mã HTML vừa soạn thảo.

---

## BƯỚC 6: KIỂM THỬ DỌN RÁC TOÀN HỆ THỐNG (GARBAGE COLLECTION)
*Hủy ứng dụng và xem hệ thống tự dọn dẹp sạch sẽ như thế nào.*

1. Quay lại trang quản trị **Ska Data Pro** trên WordPress Admin.
2. Tìm bảng `Phòng Khách Sạn` và bấm Xóa Bảng.
3. Chuyển sang **Ska No-Code Design -> Theme Builder**.
4. **Hành vi Kỳ vọng:** Trong Tab **Templates**, file `phong-khach-san-list` và `phong-khach-san-detail` đã biến mất hoàn toàn. Trong Tab **Organisms**, thẻ `Row: Phòng Khách Sạn` cũng bốc hơi.
5. Trả lại sự sạch sẽ 100% cho hệ thống.

> [!NOTE]
> **Điều kiện vượt qua (Pass Criteria):**
> - Khi bảng dữ liệu bị xóa, hệ thống lập tức dọn sạch toàn bộ các tệp Templates & Organisms liên đới trong CPT `ska_theme_builder` và bảng hệ thống qua hook `ska_data_table_deleted`.
> - Không để lại bất kỳ dữ liệu rác hay thực thể mồ côi nào liên quan tới bảng `Phòng Khách Sạn` trong CSDL.

**✅ KẾT THÚC QUY TRÌNH KIỂM THỬ**
