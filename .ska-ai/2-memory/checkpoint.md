# SYSTEM CHECKPOINT

**Thời điểm lưu:** 2026-04-28

## 1. Trạng thái hiện tại
- Đã hoàn thiện tài liệu kiến trúc Primitives (9 Core Nodes) cho Logic Engine (`primitive-nodes.md`, `logic-engine.md`).
- Đã hợp nhất Trigger Node và cập nhật Roadmap.

## 2. Nhiệm vụ phiên tiếp theo (Handover)
- Bắt tay vào xây dựng Node mới: **DB Query Node (`[D2] DB Query`)**.
- Trọng tâm triển khai:
  1. Giao diện (React UI) cho DB Query Node (Chọn Bảng, Điều Kiện Where, Order By, Limit).
  2. Lớp Backend PHP (`class-ska-logic-db-query.php`).
  3. Đảm bảo hỗ trợ SkaFX Expression trong các trường điều kiện.
- **Tính năng lùi mốc:** Bắt đầu suy nghĩ về cấu trúc **Template Settings (Display Rules)** cho Global Popup (Delay, Scroll Trigger, Scope). Tính năng này sẽ được triển khai trong Phase Theme Builder (Ska Design Engine) qua công cụ Alpine.js thay vì dùng Logic Engine.

## 3. Các files liên đới dự kiến (Phiên sau)
- `wp-content/plugins/ska-logic-engine/assets/src/builder/nodes/DBQueryNode.jsx`
- `wp-content/plugins/ska-logic-engine/includes/primitives/class-ska-logic-db-query.php`
