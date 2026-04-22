## 2026-04-22 - Material Symbols Screen Reader Accessibility
**Learning:** Material Symbols use text ligatures (e.g., 'play_arrow', 'auto_awesome') which are read aloud by screen readers if not explicitly hidden. This leads to confusing auditory experiences for visually impaired users.
**Action:** Always add `aria-hidden="true"` to `<span>` or `<i>` tags rendering Material Symbols to prevent screen readers from reading the ligature text.
