# Hướng Dẫn Sử Dụng Alpine.store() Trong Ska Builder

Tài liệu này hướng dẫn cách sử dụng `Alpine.store` để thiết lập **Global State Management (Quản lý trạng thái toàn cục)**, cho phép các Blocks hoàn toàn độc lập (không có quan hệ cha-con) có thể giao tiếp và tương tác lẫn nhau trong hệ sinh thái Ska Builder.

Tính năng này đã được hỗ trợ đồng bộ hiển thị cho cả môi trường **Frontend** lẫn **Trình duyệt giả lập (Skapine Preview Mode)** ngay bên trong Gutenberg Editor.

---

## 1. Bản Chất Hoạt Động (Cross-Block State)

Trước đây, khi bạn dùng `x-data="{ open: false }"`, trạng thái `open` chỉ tồn tại cục bộ (Local State) trong phạm vi của Block mẹ và các Block con nằm trong nó.

Để một nút bấm (Ví dụ: `Header Block`) có thể điều khiển một thành phần tách biệt (Ví dụ: `Offcanvas Block` nằm tận dưới Footer), bạn bắt buộc phải dùng **Global State** bằng **`Alpine.store()`**.

> **⚠️ Lưu ý cực kỳ quan trọng về Vòng đời (Lifecycle):**
> Tính năng `Alpine.store()` **chỉ lưu trữ trạng thái (state) trên Cùng Một Trang Nhất Định (Single Page).**
> - Nếu người dùng F5 tải lại trang, hoặc chuyển sang Page khác, vòng đời của trang kết thúc và toàn bộ dữ liệu trong Store sẽ bị **Reset sạch sẽ**.
> - Bạn KHÔNG THỂ dùng cái này để lưu giỏ hàng hay cấu hình vĩnh viễn xuyên suốt website được. Nếu cần dữ liệu sống dai qua nhiều trang (persistence), ta sẽ phải cài thêm Plugin `$persist` của Alpine JS (kết nối với Local Storage). Store hiện tại chỉ giải quyết bài toán "Giao tiếp giữa các Block trên cùng 1 trang hiển thị".

---

## 2. Các Bước Thiết Lập

### Bước 1: Khởi tạo Store bằng `x-init`

Bạn cần một Block nào đó làm nhiệm vụ "Khởi tạo" Store. Tốt nhất là thêm thuộc tính `x-init` vào thẻ **Container** ngoài cùng hoặc một Block cố định.

- **Thuộc tính:** `x-init`
- **Giá trị:** 
  ```javascript
  if (!window.Alpine.store('app')) window.Alpine.store('app', { modal: false })
  ```

> **🔥 Chú ý:** Luôn phải có dòng `if (!window.Alpine.store('app'))` để đảm bảo State không bị reset (đè lại thành false) bất thường nếu Component bị render lại (đặc biệt là trong Editor).

### Bước 2: Kích hoạt thay đổi từ một Block (Bộ điều khiển)

Trên Block mà bạn muốn dùng làm công tắc (VD: Nút bấm Menu, Nút Login), bạn thao tác gán sự kiện click và thay đổi biến bên trong `$store`:

- **Thuộc tính:** `@click` (hoặc `x-on:click`)
- **Giá trị:** 
  ```javascript
  $store.app.modal = true
  ```
  *(Hoặc toggle: `$store.app.modal = !$store.app.modal`)*

### Bước 3: Phản ứng lại thay đổi ở Block khác (Bộ nhận)

Tại Block mà bạn muốn ẩn/hiện hoặc thay đổi UI theo Global State (VD: Panel Offcanvas, Dialog, Popup Modal), hãy dùng:

- **Thuộc tính 1:** `x-show`
- **Giá trị 1:** `$store.app.modal`

- **Thuộc tính 2 (Nút đóng):** `@click`
- **Giá trị 2:** `$store.app.modal = false`

---

## 3. Hoạt Động Trong Skapine Editor Của Chúng Ta

Động cơ (Skapine Engine) của chúng ta đã được tùy chỉnh đặc biệt (Monkey-Patching) để hiểu được toàn bộ Workflow này:

1. Khi bạn thiết lập `x-init="if(!window.Alpine.store('app')) ..."`, **SkapineEngineChild** sẽ giả lập trích xuất mã Javascript đó ra chạy trên một Môi trường ảo, tự động tạo ra Polyfill cho `window.Alpine.store`.
2. Khi bạn Click vào nút bấm trên Editor: Cây AST Parser của Skapine sẽ tiến hành duyệt Cây thuộc tính đa cấp (Deep Assignment `MemberExpression`). Nó biết bạn đang muốn chọc vào biến `$store.app.modal`.
3. Skapine Store sẽ bắn ra tín hiệu `DEEP_UPDATE` tới React. Kéo theo toàn bộ Editor sẽ re-render và hiển thị / ẩn Modal y hệt như frontend thực tế mà không cần Reload lại trang.

## 4. Những Giới Hạn Cần Biết
- Khi gán trị (Assignment Express), hệ thống Skapine AST Parser hiện tại chỉ hỗ trợ gán trên đối tượng căn bản (Identifier) và thuộc tính đối tượng (MemberExpression). Ví dụ `$store.app.modal = true` là hợp lệ.
- Trình giả lập Editor chưa hỗ trợ chạy các logic JS phức tạp hoặc phương thức tùy chỉnh bên trong Store (như `$store.app.toggle()`). Trong Editor, bạn nên ưu tiên mutate/gán value trực tiếp (`$store.app.modal = ...`).  
