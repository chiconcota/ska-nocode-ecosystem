
## 2024-05-20 - Adding Accessibility to TailwindPanel
**Learning:** Found multiple instances of buttons missing aria-labels (like "Add classes" button and class removal 'x' button) as well as decorative material icons being read by screen readers.
**Action:** Always verify if SVG elements or material icon spans need `aria-hidden="true"`, and make sure functional buttons without readable text have `aria-label`.
