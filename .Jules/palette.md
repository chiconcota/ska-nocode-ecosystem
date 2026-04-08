## 2025-04-07 - Add ARIA Labels and Keyboard Focus to Admin Actions
**Learning:** Icon-only actionable elements in the custom admin UI were implemented using <span> tags with onclick handlers, causing severe accessibility issues (not keyboard focusable, unannounced by screen readers, and hidden focus states).
**Action:** Always refactor clickable <span> icons into <button type="button"> elements with aria-label, apply focus-visible:ring-2 for keyboard visibility, and ensure focus:opacity-100 is used for elements that are hidden until hovered/focused.
