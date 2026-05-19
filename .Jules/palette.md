## 2024-05-19 - Screen Reader Confusion with Material Symbols Ligatures
**Learning:** When using Material Symbols icon fonts, the icon is rendered using ligatures (e.g., `<span class="material-symbols-outlined">star</span>`). By default, screen readers read the text content of the element ("star"), which can be confusing or redundant if the icon is purely decorative or is accompanied by visual text.
**Action:** Always add `aria-hidden="true"` to elements rendering decorative Material Symbols ligatures to prevent them from being announced by screen readers.
