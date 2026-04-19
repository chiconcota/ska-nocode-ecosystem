## 2024-04-19 - Material Symbols Accessibility
**Learning:** Material Symbols use text ligatures (e.g. `apps`, `star`) within `<span class="material-symbols-outlined">`. Without explicit `aria-hidden="true"`, screen readers will confuse users by reading out the literal ligature text instead of treating the icon as decorative.
**Action:** Always add `aria-hidden="true"` to any span implementing a Material Symbols ligature icon unless it provides critical functional meaning (in which case an `aria-label` or visually hidden text might be more appropriate).
