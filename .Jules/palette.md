## 2026-04-18 - Material Symbols Ligature Accessibility
**Learning:** Screen readers can confusingly read out the string text of Material Symbol ligatures (e.g., 'settings_backup_restore') as literal text when used inside interactive elements like buttons, providing no actual meaning or context to the user.
**Action:** Always append `aria-hidden="true"` to any `<span class="material-symbols-outlined">` elements used for icons to ensure screen readers skip reading the ligature string.
