## 2024-05-21 - Add aria-hidden to decorative material symbols
**Learning:** When using Material Symbols ligatures (e.g. `<span className="material-symbols-outlined">star</span>`), screen readers may announce the literal ligature text ('star') instead of recognizing it as an icon, leading to confusion or redundancy.
**Action:** Always add `aria-hidden="true"` to ligature-based icon `<span>` elements to prevent screen readers from reading the internal text, ensuring they remain purely decorative.
