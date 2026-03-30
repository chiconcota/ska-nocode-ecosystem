---
trigger: always_on
---

# SKA APP BUILDER RULES (v2.0.0)
@target: Ska Micro-Ecosystem | @architecture: 4 Plugins + 1 Theme | @ai_token_saving: Maximum (Decoupled Focus)

## 0. KHẨU QUYẾT TỐI THƯỢNG (THE PRIME DIRECTIVES)
1. **No-Postmeta Rule:** Ska Data Pro sử dụng bảng phẳng (Flat Tables `ska_data_*`). Tuyệt đối không xài lại `wp_postmeta` cho việc xây App. Mọi model mới phải sinh table mới.
2. **Framework-Agnostic WP:** WordPress từ nay bị xem là một nền tảng cung cấp Authentication & Admin UI tạm thời, KHÔNG phải môi trường rễ. Tránh phụ thuộc các class nội địa cũ nếu rủi ro hiệu năng cao.
3. **Decoupled Plugins Rule (Microservices):** Tuyệt đối KHÔNG ĐƯỢC để Plugin A gọi trực tiếp một class của Plugin B. Sự giao tiếp giữa 4 Plugins bắt buộc phải truyền qua WordPress Hooks (`do_action`, `apply_filters`). 

## 1. PHÂN CÁCH TRÁCH NHIỆM (BOUNDARY ISOLATION)
Trước khi Code, Agent BẮT BUỘC nhận diện code mình viết sẽ rơi vào Plugin nào để không lẫn lộn:
- **Ska Blank Theme:** Loại bỏ `.wp-block-library` và CSS của WordPress.
- **Ska Builder Core:** Render ra mã HTML sạch (Ska Container, Text, Image). Không chứa logic CSS.
- **Ska No-code Design:** Phụ trách hệ thống JIT Tailwind v4, biến `<div class="bg-red">` thành CSS thật.
- **Ska Data Pro:** Đọc/Ghi dữ liệu dưới Database (`ska_data_*`). Schema Manager.
- **Ska Logic Engine:** Nhận `<div ska-if="{{check}}">` và quyết định Render hay Ẩn mất thẻ đó.

## 2. AI WORKFLOW PROTOCOL BẮT BUỘC
- **Step 1 (Context):** Quét `/1-overview/` để nắm giới hạn ranh giới (System Map).
- **Step 2 (Isolate Memory):** Thay vì đọc toàn bộ, hãy hỏi user xem đang làm việc với Plugin nào? Rồi chỉ quét tài liệu trong `/3-ecosystem/[Tên Plugin]/`. (Ví dụ: Đang dev Theme thì cấm AI đọc doc của Logic Engine).
- **Step 3 (Schema First):** Lên thiết kế Interface & WP Hooks giữa các khối TRƯỚC khi gõ Code vào File. Cấm vội vã.
- **Step 4 (Documentation Loop):** Code xong, cập nhật tài liệu ở `/3-ecosystem/[Plugin]/` và ném Log lịch sử vào `/2-memory/decision-log.md`.

## 3. FILE SIZE LIMIT (DIVIDE & CONQUER)
- Cấm để file to vượt mốc 700 lines. 
- Giữ logic ở các Hook callback, thay vì ném code dồn toa vào constructor `__construct`.

## 4. STYLING & FRONTEND RULE
- Nếu có Tailwind, chỉ xài Design Engine.
- Ở ngoài Editor (Backend JSX), hạn chế hardcode CSS, bắt mọi thuộc tính phải quy về class Tailwind (Nguồn gốc: Single Source Of Truth).
- Tránh ghi đè global nếu không có scope `.ska-builder [class*='wp-block-ska-builder']`. Tránh làm gãy Theme khác.
