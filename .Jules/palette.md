## 2026-04-28 - Adding ARIA labels and roles to decorative icons
**Learning:** Found several decorative SVG icons acting as buttons or interactive elements (e.g., removing a tag in `TailwindPanel.js`) missing `aria-label` and interactive icons using `material-symbols-outlined` lacking `aria-hidden='true'` which can cause screen readers to announce the ligature text (like 'auto_awesome' or 'apps').
**Action:** Always add `aria-hidden='true'` to Material Symbols ligatures and add explicit `aria-label` attributes to icon-only buttons for screen reader accessibility.
