# HỆ TƯỞNG SKAAA CANVAS THEME (BAREBONE ARCHITECTURE)
@module: Skaaa Canvas (Theme) | @status: Core Foundation | @role: The "Blank Canvas" Host

## 1. Vai trò cốt lõi trong Hệ Sinh Thái
**Skaaa Canvas** không phải là một "Giao diện vạn năng" (Multipurpose Theme) như Astra, Flatsome hay Divi. Nó là một **Vật Chủ (Barebone Host)** tĩnh lặng tuyệt đối.

*   **Sứ mệnh duy nhất:** Chứa chấp bộ máy Skaaa App Builder (do các Plugins đảm nhận) và dọn sạch mọi "bãi rác mã nguồn" mặc định do WordPress tạo ra.
*   **Triết lý "Sân Khấu Vô Trùng":** Để một diễn viên (Skaaa Block) có thể tỏa sáng chính xác như bản vẽ thiết kế (Tailwind CSS), Sân Khấu (Theme) không được phép phát ra bất kỳ ánh sáng tạp âm nào (CSS rác).

## 2. Kiến trúc Chống Phá Hoại (Anti-Bloat Standards)

### A. Chặn đứng FSE (Full-Site Editing)
FSE là một kiến trúc nguy hiểm đối với Tailwind vì nó đi kèm cái gọi là **Global Styles (`theme.json`)** — nguồn cơn sinh ra hàng tá biến CSS (`--wp--preset--...`, `--wp--style--block-gap`) ghi đè tàn bạo lên các thiết lập Layout của ta.
*   **Biện pháp:** File `theme.json` của Skaaa Canvas ép giá trị `false` cho toàn bộ các thuộc tính hệ thống. Cắt đứt mạch máu cấp CSS của FSE.

### B. Vô hiệu hóa CSS Mặc định (The Great Purge)
Trong file `functions.php`, tất cả các stylesheets cốt lõi của WordPress bị dỡ bỏ (`wp_dequeue_style`):
1.  `wp-block-library` và `wp-block-library-theme`: Viền nút bóp méo, margin đoạn văn rác rưởi.
2.  `global-styles`: Mã inline CSS dài thòng ở phần `<head>`.
3.  `classic-theme-styles`: Các class cũ kĩ không còn chỗ đứng.
4.  Tính năng linh tinh: Loại bỏ triệt để js/css Emojis, oEmbeds để tránh phải tải hàng đống http request vô nghĩa.

## 3. Kiến trúc Theme Builder Tương Lai (Hook-Based Template)
Skaaa Canvas sử dụng kiến trúc **Classic-Hybrid**. Nó bám vào cấu trúc file `index.php` truyền thống của WP nhưng đã được rút gọn tối đa:

```php
do_action( 'skaaa_theme_header' ); 
// ... the_content() ...
do_action( 'skaaa_theme_footer' ); 
```
*   **Logic Hoạt Động:** Các file layout của Theme không chứa bất kỳ đoạn mã thiết kế HTML/CSS (Header/Footer) nào. Nó chỉ cung cấp các **Điểm Móc (Action Hooks)**. 
*   **Tích hợp:** Trong tương lai, tính năng **Skaaa Theme Builder** (dựng Header/Footer/Archive) sẽ **đặt trong Plugin `skaaaaa-builder-core`** và móc nối trực tiếp các mẫu thiết kế Tailwind vào các Hook này. Nếu khách hàng đổi sang Theme khác, họ chỉ cần ánh xạ lại các Hook, mà không hề mất đi bản thiết kế.

## 4. Quản lý Brand Styles (Màu sắc Thương hiệu)
*   **KHÔNG NẰM Ở THEME!**
*   Toàn bộ cấu hình Primary Color, phông chữ (Typography) thuộc thẩm quyền của **Skaaa Design Engine (Plugin)** để đảm bảo người dùng không bị "Khóa chặt" (Vendor Lock-in) vào Skaaa Canvas.

## 5. Cấu trúc File
```text
themes/skaaa-canvas/
├── style.css           -> Khai báo tên theme (Tuyệt đối cấm viết CSS vào đây)
├── theme.json          -> Vô hiệu hóa FSE và WP CSS Presets
├── functions.php       -> Bộ lọc tẩy rửa (Dequeue rác) và Action Hooks
└── index.php           -> Khung xương Hook-Based Skeleton
```
