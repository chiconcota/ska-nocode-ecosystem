## 2024-05-19 - Screen Readers and Material Symbols Ligatures
**Learning:** Decorative Material Symbols ligatures using `material-symbols-outlined` (e.g. `<span class="material-symbols-outlined">home</span>`) will cause screen readers to read the ligature text aloud ("home") out of context if not hidden, creating auditory clutter. This pattern is prevalent in this app's icon components.
**Action:** Always add `aria-hidden="true"` to elements using `material-symbols-outlined` for icons.
