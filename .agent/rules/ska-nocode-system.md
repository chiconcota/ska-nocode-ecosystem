---
trigger: always_on
---

# SKA APP BUILDER RULES (v2.0.0)
@target: Ska Micro-Ecosystem | @architecture: 4 Plugins + 1 Theme | @ai_token_saving: Maximum (Decoupled Focus)

## 0. KHẨU QUYẾT TỐI THƯỢNG (THE PRIME DIRECTIVES)
1. **No-Postmeta Rule:** Ska Data Pro sử dụng bảng phẳng (Flat Tables `ska_data_*`). Tuyệt đối không xài lại `wp_postmeta` cho việc xây App. Mọi model mới phải sinh table mới.
2. **Framework-Agnostic WP:** WordPress từ nay bị xem là một nền tảng cung cấp Authentication & Admin UI tạm thời, KHÔNG phải môi trường rễ. Tránh phụ thuộc các class nội địa cũ nếu rủi ro hiệu năng cao.
3. **Decoupled Plugins Rule (Microservices):** Tuyệt đối KHÔNG ĐƯỢC để Plugin A gọi trực tiếp một class của Plugin B. Sự giao tiếp giữa 4 Plugins bắt buộc phải truyền qua WordPress Hooks (`do_action`, `apply_filters`). 
4. **Single Source Of Truth (Global State):** Tuân thủ tuyệt đối chuẩn Nocode Molecule. Không tách vỡ State, Skapine Engine phải sử dụng độc quyền hệ sinh thái `Alpine.store` để giao tiếp chéo giữa các Block độc lập.

## 1. PHÂN CÁCH TRÁCH NHIỆM (BOUNDARY ISOLATION)
Trước khi Code, Agent BẮT BUỘC nhận diện code mình viết sẽ rơi vào Plugin nào để không lẫn lộn:
- **Ska Blank Theme:** Loại bỏ hoàn toàn `.wp-block-library` và CSS mặc định của WP. Cung cấp một Blank Canvas thuần khiết.
- **Ska No-Code Design:** Nhốt toàn bô trách nhiệm của builder cũ vào một khối: Render mã HTML cực sạch (Atomic Blocks), bảng điều khiển Inspector, Skapine Engine (Alpine.js) phục vụ Live Preview tương tác thời gian thực, và cỗ máy JIT Tailwind v4. Cấm chỉ định Hardcode CSS Inline.
- **Ska Data Pro:** Đọc/Ghi dữ liệu dưới Database (Bảng phẳng `ska_data_*`). Trọng tâm vào Schema Manager và xuất nhập Smart Object Blueprint qua định dạng JSON Native.
- **Ska Logic Engine:** Tổ hợp Event-Driven (The Trinity). Khai thác ngôn ngữ biểu thức SkaFX DSL (AST Evaluator) phục vụ Dynamic Binding, Data Healing và bảo vệ Nonce quy trình Form Submission.
- **Ska Bridge (Adapter):** Lớp cầu nối dịch thuật html2tailwind và xuất API JSON cho giao diện Headless (Next.js).

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
- Ở ngoài Editor (Backend JSX), hạn chế hardcode CSS, bắt mọi thuộc tính phải quy về class Tailwind (Nguồn gốc: Single Source Of Truth). Không được tự ý nhúng mã `<style>` nội tuyến nếu không có sự phê duyệt.
- Tránh ghi đè global nếu không có phạm vi cách ly (scoped). Dùng `.ska-builder [class*='wp-block-ska-builder']`. Tránh làm gãy Theme khác.