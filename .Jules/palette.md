## 2023-10-27 - Action Buttons Accessibility
**Learning:** In `ska-no-code-design` component panels, custom action elements (`<span role="button">`) require manual implementation of keyboard interaction (Enter/Space to trigger) and `tabIndex` management for dynamic disabled states.
**Action:** When adding or updating custom button roles with conditional disabled states, ensure `tabIndex` toggles between `0` and `-1`, map disabled states to `aria-disabled`, attach `onKeyDown` handlers for Enter and Space keys, and hide decorative inner elements with `aria-hidden="true" focusable="false"`.
