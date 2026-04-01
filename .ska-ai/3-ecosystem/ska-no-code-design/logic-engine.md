# MODULE: LOGIC ENGINE
> **Namespace:** `Ska\Builder\Logic`
> **Path:** `ska-builder-core/inc/logic-engine/`

## 1. Nhiệm vụ (Responsibility)
Module này xử lý logic có điều kiện và vòng lặp trong template, hoạt động như một lớp bao (wrapper) quanh Data Engine.

## 2. Cú pháp (Syntax)
### A. Điều kiện (IF)
```html
{{#if provider:key}}
  Nội dung này chỉ hiện khi key có giá trị (truthy).
{{/if}}
```
Ví dụ: `{{#if post:has_thumbnail}} <img src="..."> {{/if}}`

### B. Vòng lặp (FOREACH)
```html
{{#foreach item in provider:array_key}}
  Nội dung lặp lại cho mỗi item.
  Context bên trong sẽ tự động chuyển sang item đó.
{{/foreach}}
```
Ví dụ: 
```html
<ul>
{{#foreach item in post:related_posts}}
  <li>{{post:title}} (ID: {{post:id}})</li>
{{/foreach}}
</ul>
```

## 3. Cấu trúc Class
- `Core`: Singleton class chính.
  - `compile( $content )`: Hàm xử lý chính, parse logic tags và gọi Data Engine để bind data.

## 4. Tích hợp
- Logic Engine được load sau Data Engine.
- Các Block (như Ska Text) nên gọi `Logic\Core::compile($content)` thay vì `Data\Core::bind_data()` để hỗ trợ cả logic và data.
