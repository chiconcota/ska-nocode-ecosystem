## 2024-06-15 - Missing Keyboard Navigation on Span Buttons
**Learning:** Custom interactive elements (like `span role="button"`) in this app frequently lack keyboard accessibility and ARIA labels. Inner SVGs/icons also miss `aria-hidden="true"` and `focusable="false"`.
**Action:** Always ensure `tabIndex={0}` and an `onKeyDown` handler (triggering on Enter/Space) are present when using `span` or `div` as a button. Hide inner decorative icons from screen readers.
