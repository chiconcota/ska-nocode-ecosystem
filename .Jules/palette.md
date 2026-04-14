## 2026-04-14 - Add ARIA labels to icon-only buttons
**Learning:** Found multiple icon-only buttons in the `TailwindPanel.js` component missing accessible names. Adding `aria-label` and `title` to these buttons, along with `aria-hidden="true"` and `focusable="false"` on inner SVGs, provides a quick and robust accessibility improvement without altering the visual design or application logic.
**Action:** Always verify icon-only buttons have an accessible name (`aria-label`) and that nested SVGs are hidden from screen readers to avoid redundant announcements.
