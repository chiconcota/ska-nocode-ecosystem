# BIÊN BẢN HỘI NGHỊ KIẾN TRÚC: THEME BUILDER VS APP BUILDER
*Trạng thái: Đang suy ngẫm (Chưa chốt định hướng cuối cùng)*
*Ngày: 05/05/2026*

## 1. MÂU THUẪN CỐT LÕI (THE CORE CONFLICT)
Chúng ta đang đứng giữa ngã ba đường về định vị sản phẩm của Ska Builder:
- **Hướng A (App Builder Độc tài):** Muốn giành quyền kiểm soát 100% trang (DOM, CSS, JS) để tạo ra Web App hiệu năng cao. Sẵn sàng đập bỏ mọi di sản của WordPress Theme.
- **Hướng B (Website Builder Thân thiện):** Cần sự mềm dẻo để dựng các website cơ bản, tương thích tốt với các Theme hiện có (Astra, FSE) và không phá vỡ cấu trúc của WooCommerce.

Sự băn khoăn: "Làm App thì đúng, nhưng muốn làm App thì trước hết phải làm được một cái Website hoàn chỉnh cơ bản đã. Không thể bắt người dùng tự xây lại cái rổ hàng WooCommerce từ con số không chỉ vì cái tôi của App Builder."

---

## 2. NHỮNG "TỬ HUYỆT" CỦA CÁC GIẢI PHÁP TẠM BỢ
Trong quá trình thảo luận, chúng ta đã bác bỏ các giải pháp chắp vá vì những lỗi chí mạng sau:

1. **Output Buffering (ob_start) để nuốt Header/Footer cũ:**
   - *Tử huyệt:* Dễ gây Memory Leak, mất thẻ `<body>`, và xung đột trực tiếp với các plugin Minify/Cache (như WP Rocket). Quá mong manh để làm Core Foundation.
2. **Hook vào FSE (get_block_template):**
   - *Tử huyệt:* FSE Parser kỳ vọng nhận vào mã HTML có comment block (`<!-- wp:group -->`). Ska trả về HTML thuần trộn PHP, sẽ khiến Block Parser báo lỗi (Block Recovery) và gây vỡ giao diện (FOUC).
3. **Injection Mode cho WooCommerce (Chỉ tiêm Hook):**
   - *Tử huyệt:* Biến Ska thành "Trình quản lý Hook" thay vì Builder. Nếu người dùng muốn tự vẽ lại thẻ sản phẩm (Product Card), họ sẽ bị khóa tay vì phải xài lại ruột của Woo. Tụt hậu so với Elementor/Bricks.

---

## 3. GIẢI PHÁP KIẾN TRÚC TẠM ĐỀ XUẤT: "SMART VIRTUAL WRAPPER"
*(Đây là giải pháp trung hòa đang được cân nhắc)*

Kết hợp sức mạnh "Đập đi xây mới" của App Builder và sự "Thân thiện" của Theme Builder thông qua cơ chế **Tách rời lũy tiến (Progressive Decoupling)**:

- **Duy trì Virtual Wrapper (`template_include` ở Priority 99):** Xóa xổ file `archive.php` hoặc `page.php` gốc. Bypass hoàn toàn FSE một cách tự nhiên (vì trả về file PHP).
- **Rẽ nhánh Tĩnh (Không dùng OB):**
  - Nếu User thiết kế Custom Header trong Ska -> Router gọi `wp_head()` và tự vẽ Ska Header.
  - Nếu User KHÔNG thiết kế Custom Header -> Router gọi `get_header()` để kế thừa Child Theme hoàn hảo.
- **Khái niệm "Native Blocks" cho WooCommerce:**
  - Để làm web nhanh: Cung cấp 1 block tên là "Native Woo Archive", kéo vào là tự gọi các hàm gốc của Woo ra y hệt cũ.
  - Để làm App/Web xịn: Xóa block đó đi, tự thả "Ska Grid", "Ska Price" vào để thiết kế lại 100% vòng lặp sản phẩm.

---

## 4. BƯỚC TIẾP THEO (NEXT ACTIONS)
- Đọc lại tài liệu này để tĩnh tâm xem liệu hướng đi **Smart Virtual Wrapper (Đa chế độ)** có thực sự đáp ứng đúng nhu cầu thực tế của người dùng Ska không.
- Tự trả lời câu hỏi: *Chúng ta sẵn sàng thỏa hiệp đến mức nào với hệ sinh thái WordPress cũ để đổi lấy sự tiện dụng?*
