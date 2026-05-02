## 2024-05-24 - Accessibility Issue with Material Symbols Ligatures
**Learning:** Material Symbols ligature text (e.g., 'play_arrow', 'shopping_cart') will be read literally by screen readers unless hidden, causing confusion when the text is decorative or already described by surrounding button text.
**Action:** Always add `aria-hidden="true"` to `<span>` elements containing Material Symbols ligatures to prevent screen readers from reading the internal text, while still visually rendering the icon.
