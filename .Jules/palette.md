## 2026-04-17 - Hide Material Symbols from screen readers
**Learning:** Material Symbols ligatures (like 'auto_awesome') are read aloud by screen readers as text if not explicitly hidden, causing confusing announcements.
**Action:** Always add `aria-hidden="true"` to decorative material symbols, and use `aria-label` on the parent button if the icon is interactive.
