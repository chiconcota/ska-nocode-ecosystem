## 2026-05-16 - Keyboard Accessibility on custom controls
**Learning:** When using custom `span` elements as buttons in WordPress components, they often lack proper keyboard handlers, requiring users to rely on mouse interaction.
**Action:** Always verify custom elements with `role="button"` have both `tabIndex={0}` and an `onKeyDown` handler for 'Enter' and 'Space' keys.
