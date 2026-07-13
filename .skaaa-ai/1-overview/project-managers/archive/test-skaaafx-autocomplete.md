# E2E Test Workflow: SkaaaFX Autocomplete & Data Picker (v1.2.0)

> [!NOTE]
> This document details the E2E manual testing scenarios for verifying the SkaaaFX Autocomplete & Data Picker feature. It covers variables autocomplete triggering, dynamic context variables extraction, loop context detection, built-in functions suggestions, template interpolation bindings, and keyboard navigation.

## Objectives
1. **Triggering Integrity:** Verify that typing `[`, `{` (double curly brace `{{`), or typing capitalized letters of built-in functions opens the suggestions dropdown.
2. **Context-Aware Variable Extraction:** Validate that variables suggested inside `[` are dynamically gathered from:
   - Mock Payload JSON defined reactively in the settings panel.
   - Outputs (`resultVar` / `result_var`) of preceding nodes in the React Flow graph.
   - Database tables and columns defined in `AVAILABLE_TABLES`.
3. **Loop Context Sensitivity:** Confirm that loop variables `[$item]` and `[$index]` are suggested **only** when the selected node is a nested child of an `IteratorNode`.
4. **Built-in Functions Autocomplete:** Verify suggestions of built-in functions (`IF(`, `CONCAT(`, etc.) when typing in formula fields.
5. **Caret Position & Focus Restoring:** Verify that after selecting a suggestion, caret position is moved to the correct position (e.g. inside function parentheses or after brackets) and focus is returned to the input/textarea.
6. **Keyboard Navigation:** Validate that ArrowUp, ArrowDown, Enter/Tab, and Escape keys correctly interact with the suggestions overlay.

---

## 🧪 Test Cases

### Test Case 1: Variables Auto-suggest (`[`) & Database Schema Lookup
**Steps:**
- [ ] Navigate to **Skaaa Logic Engine** workspace.
- [ ] Open any workflow diagram (e.g., click `Design Flow` on a workflow).
- [ ] Select the `ConditionNode` (If/Else node) or drag a new one.
- [ ] Click inside the **"If condition"** textarea, type a single `[` character, and pause.
- [ ] Observe the dropdown list.
- [ ] Type `cli` after `[` (so the input reads `[cli`).

**Expected Results:**
- Typing `[` immediately opens the glassmorphic suggestions dropdown right below the textarea.
- The dropdown lists variables from the mock payload (e.g. `[payload.user.name]`), preceding output variables, and database dictionary fields.
- Database fields relative paths are mapped from physical table IDs (e.g. `[clinic.doctors.name]` for `wp_skaaa_data_app_clinic_doctors`).
- Typing `cli` filters the list down to database columns belonging to the `clinic` app prefix.
- Left-hand side lists variable names, while the right-hand side displays badge labels (e.g. `PAYLOAD`, `FIELD`).

---

### Test Case 2: Dynamic Mock Payload & Preceding Node Variable Extraction
**Steps:**
- [ ] Drag a `RenderTemplateNode` to the canvas and select it.
- [ ] Locate the **Mock Payload (JSON)** editor inside the settings panel.
- [ ] Edit the JSON to add a new custom field:
   ```json
   {
     "payload": {
       "user": {
         "name": "Alex Johnson"
       },
       "custom_coupon_code": "DISCOUNT50"
     }
   }
   ```
- [ ] Click inside the **"Template HTML / Variable"** textarea.
- [ ] Type `[` and look for `[payload.custom_coupon_code]`.
- [ ] Select it by clicking on it.
- [ ] Now select another node (e.g. `DBQueryNode`), and change its **Result Variable** to `payload.patients_list`.
- [ ] Go back and select `ConditionNode`, click in the expression field, and type `[`.
- [ ] Verify that `[payload.patients_list]` is listed as a suggestion.

**Expected Results:**
- The autocomplete parses the `mockPayload` JSON reactively. Newly typed keys like `[payload.custom_coupon_code]` appear in the suggestions instantly.
- Output variables declared in other nodes (`payload.patients_list`) are dynamically extracted from the graph and suggested as output variables (`OUT` badge).
- Selecting a suggestion inserts the text, replaces the typed trigger/query, closes the overlay, and returns focus to the field.

---

### Test Case 3: Phát hiện ngữ cảnh vòng lặp (`$item` & `$index`)
**Các bước thực hiện:**
- [ ] Chọn một node gán dữ liệu `SetDataNode` trên canvas.
- [ ] Click vào ô nhập giá trị (value input) của bất kỳ phần gán biến nào, gõ ký tự `[` và xác nhận rằng gợi ý **không** xuất hiện hai biến `[$item]` và `[$index]`. *(Giải thích: Vì lúc này node đang ở ngoài cùng, không nằm trong vòng lặp nào, nên không có dữ liệu của phần tử hiện tại hay chỉ mục vòng lặp).*
- [ ] Kéo `SetDataNode` vào bên trong một `IteratorNode` (để nó trở thành node con nằm trong phạm vi lặp của Iterator).
- [ ] Kiểm tra trong Settings Panel bên phải để chắc chắn rằng trường **"Parent Node (Iterator)"** đã tự động điền ID của IteratorNode đó. *(Giải thích: Hệ thống tự động phát hiện mối quan hệ cha-con của đồ thị React Flow khi kéo thả).*
- [ ] Bây giờ, click vào ô nhập giá trị của phần gán biến và gõ ký tự `[`.
- [ ] Kiểm tra xem danh sách gợi ý (dropdown) có xuất hiện `[$item]` và `[$index]` hay không. *(Giải thích: Lúc này vì node đã nằm trong ngữ cảnh vòng lặp Iterator, hệ thống phải gợi ý 2 biến này).*
- [ ] Xóa liên kết node cha (bằng cách xóa giá trị trong ô nhập **"Parent Node"**) để đưa node trở lại cấp ngoài cùng (root level).
- [ ] Gõ lại ký tự `[` và xác minh rằng `[$item]` và `[$index]` **không còn** xuất hiện trong danh sách gợi ý nữa.

**Kết quả mong đợi:**
- Hai biến đặc biệt `[$item]` (giá trị phần tử lặp hiện tại) và `[$index]` (chỉ mục/số thứ tự vòng lặp, từ 0) **chỉ** được gợi ý khi node nằm bên trong phân cấp của một `IteratorNode` (nhận diện ngữ cảnh động).

---

### Test Case 4: Built-in Functions & Caret Placement
**Steps:**
- [ ] Select a `ConditionNode`.
- [ ] Clear the expression field. Type a single capital letter `I` (or `C`).
- [ ] Verify that `IF(` (or `CONCAT(`) is suggested.
- [ ] Select `IF(` by clicking it.
- [ ] Check where the text cursor caret is placed.

**Expected Results:**
- Typing capital letters matches starting prefixes of built-in functions (`IF(`, `CONCAT(`, `LIST_COL(`, `ROUND(`, `SUM(`).
- Selecting `IF(` inserts `IF()` into the input field and positions the cursor **inside** the parentheses `IF(|)` instead of at the end of the text, so the user can immediately type the condition.

---

### Test Case 5: Template Interpolation (`{{ }}`) Trigger
**Steps:**
- [ ] Select an `ApiNode` or `RenderTemplateNode`.
- [ ] Go to the **"Endpoint URL"** input field.
- [ ] Type `{` and verify nothing happens (waiting for double curly braces).
- [ ] Type another `{` (so the input has `{{`).
- [ ] Verify suggestions dropdown opens.
- [ ] Type `user` and select `payload.user.email`.

**Expected Results:**
- Double curly brace `{{` triggers the suggestions dropdown.
- Selecting `payload.user.email` inserts it and automatically appends the closing braces `}}` resulting in `{{ payload.user.email }}`. Caret is placed right after `}}`.

---

### Test Case 6: Keyboard Navigation
**Steps:**
- [ ] Click any input supporting autocomplete (e.g. If Condition).
- [ ] Type `[`.
- [ ] Press `ArrowDown` multiple times, then `ArrowUp`.
- [ ] Press `Enter` (or `Tab`) to select the highlighted suggestion.
- [ ] Clear it, type `[`, type a query, and then press `Escape` key.

**Expected Results:**
- `ArrowDown` and `ArrowUp` cycle active selection highlights smoothly.
- `Enter` / `Tab` inserts the selected item, closes the list, and focuses back.
- `Escape` closes the dropdown overlay instantly.

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Autocomplete dropdown does not appear** | Assets are cached in the browser or Vite dev build is not running. | Press Ctrl+F5 to force clear browser cache. If developing, ensure assets built successfully (`assets/js/admin-dag-builder.bundle.js` modified date should be current). |
| **No database tables or fields are suggested** | Database schema is empty or `window.SKAAA_DAG_CONTEXT.AVAILABLE_TABLES` is not populated. | Verify if WordPress page contains table schemas. In F12 browser console, type `window.SKAAA_DAG_CONTEXT` and verify `AVAILABLE_TABLES` array is not empty. |
| **JSON Error badge blocks payload parsing** | The JSON syntax in the mock payload editor is invalid. | Fix JSON syntax (ensure double quotes are used for keys and strings, no trailing commas). Autocomplete suggestions will reactivate once JSON is parsed successfully. |
