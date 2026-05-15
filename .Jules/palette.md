## 2024-05-24 - Accessibility: Decorative Material Symbols Ligatures
**Learning:** Decorative icons rendered using Material Symbols ligatures (e.g. `<span className="material-symbols-outlined">icon_name</span>`) can cause screen readers to read the ligature text aloud (e.g. "play arrow" or "star") when they are purely decorative or redundant to text.
**Action:** Always add `aria-hidden="true"` to ligature spans that are purely decorative or used alongside text to prevent screen readers from reading the ligature text.
