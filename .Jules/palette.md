## 2024-05-17 - Material Symbols Accessibility
**Learning:** Material Symbols use text ligatures inside span tags (e.g., `<span class="material-symbols-outlined">auto_awesome</span>`). Without `aria-hidden="true"`, screen readers will confusingly read the ligature text aloud ("auto awesome") instead of treating it as an icon.
**Action:** Always add `aria-hidden="true"` to `material-symbols-outlined` spans when used purely for visual decoration.
