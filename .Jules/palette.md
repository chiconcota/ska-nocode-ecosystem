## 2026-04-09 - Add ARIA labels to icon-only buttons
**Learning:** Found an accessibility issue pattern specific to this app's components, where icon-only buttons in the `TailwindPanel.js` component lacked `aria-label`s and `title`s.
**Action:** Always check icon-only buttons for accessibility labels in WordPress components like `@wordpress/components`.
