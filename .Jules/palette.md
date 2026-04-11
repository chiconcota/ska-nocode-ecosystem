## 2026-04-11 - [Added ARIA Labels to Dynamically Generated Tags]
**Learning:** Icon-only buttons used within dynamic component loops (like tags in the Tailwind UI) often miss screen reader support because they reuse a generic visual `x` or SVG. A screen reader can't convey context without an explicit label. In WordPress block interfaces, ensure small utility buttons are also wrapped in the translation function `__` and clearly describe the action.
**Action:** Always verify that mapped/looped components with nested icon actions contain an `aria-label` on the button.
