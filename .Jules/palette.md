## 2026-04-16 - Adding ARIA labels to Tailwind Panel Icon Buttons
**Learning:** The 'Remove Class' and 'Add Classes' (+) buttons in the Tailwind Panel lacked ARIA labels, making them inaccessible to screen readers. Furthermore, the inline SVGs within buttons should be hidden to avoid duplicate reading.
**Action:** Apply  to icon-only buttons using  from  for translatability, and ensure inline  tags have `aria-hidden="true" focusable="false"`.
## $(date +%Y-%m-%d) - Adding ARIA labels to Tailwind Panel Icon Buttons
**Learning:** The 'Remove Class' and 'Add Classes' (+) buttons in the Tailwind Panel lacked ARIA labels, making them inaccessible to screen readers. Furthermore, the inline SVGs within buttons should be hidden to avoid duplicate reading.
**Action:** Apply `aria-label` to icon-only buttons using `__` from `@wordpress/i18n` for translatability, and ensure inline `svg` tags have `aria-hidden="true" focusable="false"`.
