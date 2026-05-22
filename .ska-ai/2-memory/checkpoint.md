# CHECKPOINT - PHẦN BÀN GIAO TIẾN ĐỘ (v1.0.0)
*Ngày cập nhật: 2026-05-22*

## 1. Trạng thái hiện tại (Status)
- **Công việc**: Khắc phục lỗi các trường dữ liệu bị biến mất (reset về chuỗi rỗng) sau khi người dùng nhấn nút cập nhật trên Detail/Update View của Portal.
- **Trạng thái**: 🟢 ĐÃ HOÀN THÀNH & KIỂM THỬ THÀNH CÔNG.
- **Kết quả E2E**: Đã thêm logic kiểm tra `isUpdate` thông minh trong `ska-frontend.js`. Khi thực thi lưu ở Detail/Update View (thông qua `actionId.startsWith('update_')`, hoặc presence của `fields.id`, hoặc presence của `Alpine.store('skaPortal').currentData.id`), controller `skaForm` sẽ giữ nguyên giá trị đã nhập ngoài frontend thay vì reset trắng biểu mẫu. Đã kiểm thử thành công trên trang `http://ska-core-builder.local/phong-khach-san/1/` bằng Chrome DevTools, xác nhận dữ liệu được cập nhật thành công và các trường dữ liệu không bị biến mất. Trái lại, trên trang Create View (`/create/`), form vẫn được tự động reset rỗng như mong đợi.

### 5. Fixed Teleport querySelector & Separated Close vs Save Modal Actions
- **Files:**
  - [ska-frontend.js](file:///c:/Users/ADMIN/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-no-code-design/assets/js/ska-frontend.js)
  - [render.php](file:///c:/Users/ADMIN/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-no-code-design/src/ska-form-rich-text/render.php)
  - [class-api-shadow-scratchpad.php](file:///c:/Users/ADMIN/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-no-code-design/inc/theme-builder/class-api-shadow-scratchpad.php)
- **Change:**
  - Resolved `null` reference error when querying the Gutenberg iframe inside the Alpine.js component: because the modal container is teleported to `body` via `x-teleport="body"`, querying `this.$el.querySelector('iframe')` always returned `null`. Replaced this with a global lookup using `document.getElementById` and a unique iframe ID structure (`ska_iframe_${fieldName}`).
  - Separated the modal closing action: only retrieve and update Alpine fields and TinyMCE when `saveChanges` is `true`.
  - Added a `150ms` delay after calling Gutenberg's `clearEntityRecordEdits` to let React re-render inside the iframe.
  - **Bulletproof Beforeunload Interceptor:** Added a hook on `admin_head` inside `class-api-shadow-scratchpad.php` to inject an inline script inside the iframe when editing `ska_scratchpad`. This script overrides `window.addEventListener` and `window.onbeforeunload` to intercept and completely block Gutenberg's `beforeunload` warnings, resolving the browser alert dialog issue in both save and cancel flows.
  - Bumped frontend script version in `blocks/init.php` to `1.0.3` to prevent client browser caching.

## 2. Chi tiết các tệp đã sửa đổi (Modified Files)
- **Frontend JS**: [ska-frontend.js](file:///c:/Users/ADMIN/Local%20Sites/ska-core-builder/app/public/wp-content/plugins/ska-no-code-design/assets/js/ska-frontend.js) - Bổ sung logic clear entity edits trước khi unload iframe URL.
- **Build Output**: Đã compile thành công qua `npm run build` và đồng bộ qua `npm run sync`.

## 3. Nhật ký và Tài liệu đi kèm
- Cập nhật **Decision Log**: `.ska-ai/2-memory/decision-log.md`
- Cập nhật **System Map Recent Logs**: `.ska-ai/1-overview/system_map.md`
- Cập nhật **Ecosystem Blocks Docs**: `.ska-ai/3-ecosystem/ska-no-code-design/blocks.md`

## 4. Công việc tiếp theo (Next Steps)
- Tiếp tục kiểm thử E2E và bàn giao cho khách hàng hoặc triển khai các tác vụ tiếp theo của Phase 4.6.
