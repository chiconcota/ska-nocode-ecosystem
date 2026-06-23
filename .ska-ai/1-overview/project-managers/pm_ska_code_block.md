# PROJECT MANAGER: SKA CODE BLOCK
@status: 🟡 Planning | @target_milestone: MILESTONE 1 (POST-MVP) | @last_update: 2026-06-23

> [!NOTE]
> Tài liệu này quản lý tiến độ, kiến trúc và kế hoạch triển khai của khối Gutenberg **`ska-code`** (Custom Code Block) thuộc plugin **Ska No-Code Design**. Khối này thay thế Custom HTML mặc định của WordPress, cho phép nhúng mã CSS/JS/HTML tùy biến cục bộ hoặc liên kết với *Ska Scripts Library*, hỗ trợ tối ưu hóa và chống nạp trùng lặp.

---

## 1. MỤC TIÊU CỐT LÕI (CORE GOALS)
- **Nhúng mã tùy biến (Inline Code Injection):** Cho phép viết trực tiếp JS, CSS, hoặc HTML tùy biến ngay trong Inspector Panel hoặc block Canvas của Gutenberg Editor.
- **Liên kết Thư viện (Library Connection):** Cho phép chọn nhanh một Script đã đăng ký từ *Ska Scripts Library* thông qua một ô chọn Dropdown trực quan.
- **Khử trùng lặp Server-side (Deduplication):** Tự động phát hiện và loại bỏ các script/style trùng lặp khi người dùng kéo thả nhiều block `ska-code` giống nhau hoặc tham chiếu chung một script trên cùng một trang.
- **Đồng bộ hóa vị trí hiển thị:** Hỗ trợ render code inline tại vị trí block, hoặc đưa lên Header (`wp_head` qua cơ chế Pre-parsing), hoặc đưa xuống Footer (`wp_footer`).

---

## 2. KIẾN TRÚC & PHÂN CHIA TRÁCH NHIỆM

### A. Giao diện Editor Gutenberg (Ska No-Code Design)
- Đăng ký một block Gutenberg mới có tên `ska/code` (Ska Code Block).
- **Attributes của block:**
  - `codeType` (string): `inline` (tự viết mã) hoặc `library` (chọn từ thư viện).
  - `libraryScriptId` (string): ID của script liên kết từ Scripts Library.
  - `inlineCode` (string): Đoạn mã JS/CSS/HTML viết trực tiếp.
  - `location` (string): `inline` (render tại chỗ), `header` (lên `<head>`), `footer` (xuống cuối trang).
- **Inspector Controls (Cài đặt Sidebar):**
  - Toggle chọn chế độ: Viết mã trực tiếp hay Chọn từ thư viện.
  - Dropdown lấy danh sách script từ REST API của Scripts Library (chỉ hiển thị khi chọn chế độ thư viện).
  - Editor nhỏ với font monospace và căn chỉnh thụt dòng cho phép gõ code inline mượt mà (chỉ hiển thị khi chọn chế độ viết mã trực tiếp).
  - Dropdown chọn vị trí nạp: `Inline`, `Header`, `Footer`.

### B. Xử lý Render Backend & Khử Trùng Lặp (Ska No-Code Design)
- Viết class `Ska_Code_Block_Renderer` xử lý hàm `render_callback` của block `ska-code`:
  - **Inline Render:** Nếu `location === 'inline'`, in trực tiếp mã ra ngoài frontend ngay tại vị trí block.
  - **Footer Render:** Nếu `location === 'footer'`, đẩy mã hoặc asset ID vào static queue của `Ska_Scripts_Loader`. Hook `wp_footer` sẽ in ra và tự động loại bỏ trùng lặp (Deduplication).
  - **Header Render (Pre-parsing):** Để giải quyết vấn đề Header render trước Content, ta đăng ký hook `wp_enqueue_scripts`. Tại đây, kiểm tra xem post hiện tại có chứa block `ska/code` hay không thông qua `has_block('ska/code')`. Nếu có, parse các blocks bằng `parse_blocks()`, lọc ra các block `ska/code` có `location === 'header'`, đẩy mã vào hàng đợi `header` để in ra trong hook `wp_head`.
  - **Deduplication Logic:** 
    - Nếu nhiều block `ska-code` cùng liên kết đến một `libraryScriptId`, hệ thống chỉ nạp script đó một lần duy nhất.
    - Nếu nạp inline code, hệ thống có thể băm (hash) nội dung code để chống in trùng lặp nếu người dùng sao chép y nguyên block ở nhiều nơi trên trang.

---

## 3. KẾ HOẠCH HÀNH ĐỘNG (ACTION ITEMS)

- [ ] **Phase 1: Khởi Tạo Block Gutenberg (Giao Diện Editor)**
  - [ ] Đăng ký block `ska/code` với đầy đủ file cấu hình `block.json` và assets JS/CSS.
  - [ ] Thiết kế giao diện Edit trong `edit.js` với các panels Inspector.
  - [ ] Viết API endpoint GET `/ska-data/v1/scripts` để Gutenberg fetches danh sách script từ library làm dữ liệu cho dropdown.
  - [ ] Thiết kế hiển thị preview của block trong Editor (hiển thị biểu tượng code kèm nhãn script đang liên kết hoặc dòng code preview ngắn).

- [ ] **Phase 2: Render Engine & Deduplication (Xử lý Backend)**
  - [ ] Thiết lập file `render.php` xử lý `render_callback` cho block.
  - [ ] Viết hàm quét trước bài viết (`pre_parse_header_scripts`) hook vào `wp_enqueue_scripts` để giải quyết nạp Header.
  - [ ] Triển khai cơ chế khử trùng lặp (Deduplication) cho cả script liên kết thư viện và mã inline dựa trên hash MD5 nội dung.

- [ ] **Phase 3: Tối Ưu Hóa Trải Nghiệm Editor (Code Formatting)**
  - [ ] Tinh chỉnh stylesheet trong Editor để textarea nhập code inline có màu nền tối, font chữ monospace rõ ràng và tự động giãn dòng.
  - [ ] Thêm các cảnh báo bảo mật hoặc chú thích trực quan khi viết code JS/CSS tùy chỉnh.

- [ ] **Phase 4: Kiểm Thử & Nghiệm Thu (Testing & Verification)**
  - [ ] Viết tài liệu quy trình kiểm thử E2E thủ công.
  - [ ] Test kéo thả nhiều block `ska-code` dùng chung thư viện CDN để xác nhận chỉ tải 1 lần duy nhất ngoài frontend.
  - [ ] Test chức năng nạp Inline, Header (Pre-parsing), và Footer hoạt động đúng vị trí DOM.

---

## 4. TIÊU CHÍ NGHIỆM THU (ACCEPTANCE CRITERIA)
1. Block Gutenberg `ska-code` hiển thị chính xác trong Inserter (+) của editor.
2. Inspector Sidebar hiển thị đầy đủ các tùy chọn cấu hình và lấy được danh sách script động từ Scripts Library.
3. Cơ chế khử trùng lặp hoạt động hoàn hảo: Không có bất kỳ thẻ script/style liên kết thư viện nào bị nạp 2 lần ở frontend trên cùng 1 trang.
4. Cơ chế Pre-parsing quét chính xác block cấu hình `header` và đưa thành công lên phần `<head>` của trang web.
