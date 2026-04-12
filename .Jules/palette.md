## 2025-04-12 - ARIA labels for icon-only SVGs
**Learning:** When using standard SVG icons inside buttons, screen readers can sometimes read the raw SVG or ignore the button's intended label if the SVG isn't explicitly hidden.
**Action:** Always add `aria-hidden="true"` and `focusable="false"` to SVG elements inside icon-only buttons, alongside adding the `aria-label` to the parent button, to guarantee clean accessibility announcements.
