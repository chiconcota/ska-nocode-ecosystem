## 2024-05-15 - ARIA attributes for Material Symbols ligatures
**Learning:** Screen readers will mistakenly read out the ligature text used in Material Symbols (e.g., 'auto_awesome') if the span lacks an `aria-hidden` attribute, causing significant confusion.
**Action:** Always add `aria-hidden="true"` to any span containing ligature text for rendering icons, such as `<span className="material-symbols-outlined">`.
