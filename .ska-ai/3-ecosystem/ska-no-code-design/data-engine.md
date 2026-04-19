# MODULE: FRONTEND DATA BINDER (Legacy Naming: Data Engine)
> ⚠️ **CẢNH BÁO KIẾN TRÚC:** Tên gọi "Data Engine" trong thư mục này (namespace Ska\Builder\Data) chỉ đề cập đến Lớp Phân Giải Text Placeholder ở mặt tiền (Frontend Text Renderer). KHÔNG ĐƯỢC nhầm lẫn với `Ska Data Pro` (Hệ quản trị Database Flat Tables ở Backend). Tên đúng của module này là Data Binder.
> **Namespace:** `Ska\Builder\Data`
> **Path:** `ska-builder-core/inc/data-engine/`
> **Status:** 🟢 Hoạt động | Đã tích hợp với `ska-data-pro` (Phase 2).

## 1. Nhiệm vụ (Responsibility)
Module này là "Trái tim" của hệ thống, chịu trách nhiệm lấy và xử lý dữ liệu động.
- **Dynamic Binding:** Thay thế các placeholder `{{post:title}}`, `{{scf:price}}` bằng dữ liệu thật.
- **Context Aware:** Hiểu ngữ cảnh hiện tại (đang ở Single Post, hay đang trong Loop con của Repeater).
- **Data Abstraction:** Cung cấp API thống nhất để truy cập dữ liệu từ WP Core, SCF, WooCommerce, User Meta.

## 2. Luồng hoạt động (Architecture Flow)
1. **Input:** Chuỗi/Mảng chứa placeholder (VD: `<h1>{{post:title}}</h1>`).
2. **Context Check:** Xác định `current_object_id` và `source_type` (Post/User/Term).
3. **Extraction:**
   - Parse chuỗi tìm pattern `{{provider:key}}` hoặc `{{key}}`.
   - Map key sang dữ liệu (VD: `scf:price` -> `get_field('price')`).
4. **Formatting:** Apply format nếu có (VD: `{{price|money}}` - *Pending*).
5. **Output:** Dữ liệu thô hoặc HTML đã bind.

## 3. Giao diện (Interface Contracts)

### A. Providers (Implemented)
| Provider | Slug | Mô tả |
| :--- | :--- | :--- |
| **Ska Data** | `ska-data` | Tích hợp từ `ska-data-pro`. Lấy dữ liệu từ mảng Flat Tables (`ska_data_xyz`). (*New Phase 2*) |
| **WP Post** | `post` | Lấy dữ liệu Post (title, content, id, meta). |
| **SCF (ACF)** | `scf` | ⚠️ **(Legacy - Ít Dùng)** Lấy dữ liệu Custom Fields (Postmeta EAV cũ) mục đích hỗ trợ Cũ. *Luật: App mới nên xài Ska Data*. |
| **Term** | `term` | Lấy dữ liệu Taxonomy Term (name, slug, desc). |
| **User** | `user` | Lấy dữ liệu User (name, email, avatar). |

### B. Filters
| Filter Name | Input | Output | Mô tả |
| :--- | :--- | :--- | :--- |
| `ska_bind_data` | `string`, `context` | `string` | Bind data vào chuỗi text. |
| `ska_get_field` | `key`, `id` | `mixed` | Lấy giá trị của một field cụ thể. |

### C. Context Interface
- `set_context( $id, $type )`: Đặt ngữ cảnh dữ liệu hiện tại (Quan trọng cho Nested Loop).
- `get_context()`: Lấy ngữ cảnh hiện tại.
- `reset_context()`: Reset về ngữ cảnh global (Main Query).

## 4. Cấu trúc Class (Implemented)
- `Core`: Singleton, facade cho mọi hoạt động.
- `Context_Manager`: Stack quản lý ngữ cảnh (hỗ trợ Loop lồng nhau).
- `Provider_Registry`: Quản lý danh sách providers.
- `Provider` (Interface): Contract cho mọi nguồn dữ liệu.

## 5. Ghi chú triển khai
- **Security:** Luôn escape dữ liệu đầu ra dựa trên ngữ cảnh sử dụng (HTML, Attribute, URL).
- **Performance:** Tránh gọi `get_post_meta` lặp lại.
- **Syntax:** Sử dụng `{{provider:key}}` (VD: `{{post:title}}`, `{{user:email}}`). Nếu không có provider, mặc định là `post`.
