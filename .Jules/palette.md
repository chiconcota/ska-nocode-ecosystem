## 2024-05-15 - ARIA Labels and SVG hidden attributes in WP
**Learning:** WordPress admin UI uses `@wordpress/components` but often needs explicit ARIA labels on icon buttons (like `Button` containing an `<svg>`). Inner SVGs must have `aria-hidden="true"` and `focusable="false"` to prevent screen readers from reading raw code or double-announcing.
**Action:** Always verify icon-only buttons have translation-wrapped `aria-label` strings (via `__`) and inner icons/SVGs are correctly hidden from screen readers.
