## 2026-04-23 - Prevent screen readers from speaking Material Symbol ligatures
**Learning:** Material Symbol ligatures (like 'settings_backup_restore') are read aloud by screen readers as the raw text, creating confusing experiences for visually impaired users.
**Action:** Always add `aria-hidden="true"` to decorative ligature icons to prevent screen readers from reading them.
