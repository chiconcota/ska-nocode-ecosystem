## YYYY-MM-DD - [Title]
**Learning:** [UX/a11y insight]
**Action:** [How to apply next time]
## 2026-05-09 - Ensure material-symbols-outlined is aria-hidden
**Learning:** Material Symbols ligatures (e.g., `<span className="material-symbols-outlined">icon_name</span>`) can confuse screen readers by reading the ligature text aloud as content.
**Action:** When using decorative Material Symbols ligatures, always add `aria-hidden="true"` and `focusable="false"` to prevent them from being announced confusingly by screen readers.
