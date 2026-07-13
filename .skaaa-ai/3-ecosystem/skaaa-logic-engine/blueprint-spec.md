# Tài liệu Đặc tả cấu trúc JSON Blueprint (Skaaa Logic Engine)
@version: 1.0.0 | @status: Kế hoạch Phase 4.6 | @last_update: 2026-06-01

Tài liệu này hướng dẫn cách viết và định dạng tệp tin JSON Blueprint để nạp (Import) các sơ đồ tự động hóa (DAG Workflows) vào hệ thống **Skaaa Logic Engine** một cách chuẩn xác, tránh lỗi trắng màn hình hiển thị.

---

## 1. Cấu trúc tổng thể (Global Structure)

Một tệp tin JSON Blueprint hợp lệ bắt buộc phải có 2 mảng chính:
* `nodes`: Mảng định nghĩa các trạm xử lý (các ô vuông trên màn hình).
* `edges`: Mảng định nghĩa các đường nối (các dây liên kết logic giữa các trạm).

```json
{
  "nodes": [],
  "edges": []
}
```

---

## 2. Đặc tả chi tiết các Trạm (Nodes Specification)

Mỗi đối tượng trạm trong mảng `nodes` bắt buộc phải có đầy đủ các thuộc tính cấu trúc sau:

### 2.1. Các thuộc tính bắt buộc của một Node:
* **`id`** (Chuỗi - String): Định danh duy nhất cho trạm đó trong sơ đồ. Viết liền không dấu, không chứa ký tự đặc biệt (VD: `trigger_1`, `db_action_1`, `response_success`).
* **`type`** (Chuỗi - String): Loại trạm hiển thị trên giao diện React Flow. Phân biệt chữ hoa/thường (Case-sensitive).
* **`class`** (Chuỗi - String): Lớp PHP xử lý logic tương ứng trên máy chủ.
* **`position`** (Đối tượng - Object): Tọa độ định vị hiển thị trên bảng vẽ. Nếu thiếu thuộc tính này, trạm sẽ không thể hiển thị trên màn hình.
  * `x` (Số - Number): Tọa độ ngang (VD: `150`).
  * `y` (Số - Number): Tọa độ dọc (VD: `100`).
* **`data`** (Đối tượng - Object): Chứa nhãn tiêu đề hiển thị và các tham số cấu hình riêng biệt của từng trạm.

### 2.2. Ba loại Trạm cơ bản trong hệ thống:

#### A. Trạm Kích Hoạt (TriggerNode)
* Dùng để bắt đầu một luồng sự kiện (Ví dụ: khách nộp Form).
* **`type`**: `TriggerNode`
* **`class`**: `Skaaa_Logic_Trigger_Node`
* **Cấu trúc mẫu**:
  ```json
  {
    "id": "trigger_1",
    "type": "TriggerNode",
    "class": "Skaaa_Logic_Trigger_Node",
    "data": {
      "label": "Trigger: Khách Đăng Ký Form",
      "workflowId": "id_cua_workflow"
    },
    "position": { "x": 100, "y": 100 }
  }
  ```

#### B. Trạm Thao Tác CSDL (DBActionNode)
* Dùng để thêm/sửa/xóa dòng dữ liệu trong các bảng phẳng `skaaa_data_*`.
* **`type`**: `DBActionNode`
* **`class`**: `Skaaa_Logic_DB_Action`
* **Cấu trúc mẫu**:
  ```json
  {
    "id": "db_save_1",
    "type": "DBActionNode",
    "class": "Skaaa_Logic_DB_Action",
    "data": {
      "label": "DB Action: Lưu Database",
      "table": "wp_skaaa_data_app_leads",
      "actionType": "insert"
    },
    "position": { "x": 100, "y": 250 }
  }
  ```
  *(Lưu ý: `actionType` có thể là `insert`, `update` hoặc `delete`)*.

#### C. Trạm Phản Hồi Client (ClientResponseNode)
* Dùng để trả về các thông báo (toast, redirect) hiển thị cho người dùng ở trình duyệt web.
* **`type`**: `ClientResponseNode`
* **`class`**: `Skaaa_Logic_Client_Response`
* **Cấu trúc mẫu**:
  ```json
  {
    "id": "response_1",
    "type": "ClientResponseNode",
    "class": "Skaaa_Logic_Client_Response",
    "data": {
      "label": "Phản hồi: Hiện thông báo",
      "message": "Đăng ký thành công!",
      "response_type": "toast"
    },
    "position": { "x": 100, "y": 400 }
  }
  ```
  *(Lưu ý: `response_type` có thể là `toast`, `modal`, `redirect` hoặc `remove_row`)*.

---

## 3. Đặc tả chi tiết Đường nối (Edges Specification)

Mỗi đối tượng đường nối trong mảng `edges` định nghĩa dòng chảy dữ liệu từ trạm này sang trạm khác:

* **`id`** (Chuỗi - String): Định danh duy nhất cho dây nối (VD: `edge_1`).
* **`source`** (Chuỗi - String): `id` của trạm bắt đầu (Trạm nguồn).
* **`target`** (Chuỗi - String): `id` của trạm kết thúc (Trạm đích).
* **`animated`** (Boolean): Bật cờ `true` để tạo chấm sáng động chạy dọc dây nối, `false` để dây nối hiển thị tĩnh.

**Cấu trúc mẫu**:
```json
{
  "id": "edge_1",
  "source": "trigger_1",
  "target": "db_save_1",
  "animated": true
}
```

---

## 4. Hướng dẫn dành cho Trợ lý AI (AI Agents Instruction)

Khi lập trình viên hoặc người dùng ra lệnh: *"Hãy tự động tạo và nạp sơ đồ logic..."*, Trợ lý AI phải tuân thủ nghiêm ngặt 3 quy tắc vàng sau:

1. **Bắt buộc điền đúng Case-sensitive**:
   * `type` phải viết đúng là: `TriggerNode`, `DBActionNode`, hoặc `ClientResponseNode`.
   * `class` phải viết đúng là: `Skaaa_Logic_Trigger_Node`, `Skaaa_Logic_DB_Action`, hoặc `Skaaa_Logic_Client_Response`.
2. **Luôn tính toán tọa độ `position`**:
   * Không bao giờ được bỏ trống `position`.
   * Tính toán tăng dần tọa độ `y` (trục dọc) của các trạm theo thứ tự luồng chạy (Ví dụ: Trạm 1: `y: 100` -> Trạm 2: `y: 250` -> Trạm 3: `y: 400`) để các trạm không đè lên nhau.
3. **Liên kết `edges` chính xác**:
   * Đảm bảo giá trị `source` và `target` trong mảng `edges` khớp chính xác 100% với `id` của các trạm đã khai báo trong mảng `nodes`.

---

## 5. Tệp tin JSON mẫu đính kèm (Sample File)

Bạn có thể tải về hoặc tham khảo trực tiếp cấu trúc luồng logic chuẩn (Trigger -> Lưu CSDL -> Hiện Toast thông báo) tại tệp tin mẫu dưới đây:

📄 **[workflow-sample.json](file:///home/chiconcota/Local%20Sites/skaaa-core-builder/app/public/.skaaa-ai/3-ecosystem/skaaa-logic-engine/workflow-sample.json)**

