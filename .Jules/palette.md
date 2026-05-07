## 2024-05-18 - [Accessibility: Material Symbols and Icon Buttons]
**Learning:** Found that material symbols used in ska-builder-core lacked aria-hidden attributes, and icon-only buttons (like those for adding/removing Tailwind classes) lacked aria-labels, potentially causing screen reader confusion.
**Action:** Always add aria-hidden="true" to decorative material-symbols-outlined spans and aria-label attributes to icon-only buttons in WordPress block components.
