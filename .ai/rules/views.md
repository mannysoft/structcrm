---
paths:
  - 'resources/views/**'
---

# Views

## Use the brand design system, not raw indigo
The app uses Tailwind v4 with a CSS-first design system defined in resources/css/app.css (@theme tokens --color-brand-*, --color-accent-*, --font-sans Inter). Use brand-* utilities (e.g. bg-brand-600, text-brand-600, focus:ring-brand-600) for primary accents, never the old Breeze indigo-* classes. Cards use `bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl`. Buttons, badges, inputs, and tables are reusable components in resources/views/components/ (x-button, x-badge, x-text-input, x-select, x-textarea, x-table, x-page-header, x-empty-state, x-search-input). Prefer these components over raw HTML.
