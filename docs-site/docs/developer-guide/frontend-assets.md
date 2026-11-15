---
title: Frontend assets
sidebar_position: 10
description: Tailwind 4, Vite, the brand theme and the build commands.
---

# Frontend assets

## Vite

```js
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

| Entry | Notes |
| :--- | :--- |
| `resources/css/app.css` | Tailwind 4 import, `@theme` brand tokens, base layer |
| `resources/js/app.js` | Intentionally empty — Livewire and Alpine are injected by Livewire |

## Tailwind 4 theme

Brand colours live as CSS custom properties in `@theme`:

```css
@theme {
    --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

    /* Brand — electric blue */
    --color-brand-50: #eef5ff;
    --color-brand-600: #1f5cf0;
    --color-brand-700: #1747dc;
    /* … */

    /* Accent — amber */
    --color-accent-500: #f59e0b;
}
```

Use `text-brand-600`, `bg-accent-500`, etc. Do not hard-code hex values in Blade.

Tailwind 4 has no `tailwind.config.js` — configuration is CSS-first via `@theme`. If you
need new tokens, add them to `@theme` in `app.css`.

## Commands

| Command | Purpose |
| :--- | :--- |
| `npm run dev` | Vite dev server with HMR |
| `npm run build` | Production build into `public/build` |
| `composer run dev` | Server + queue + logs + Vite together |

## The manifest error

```text
Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest
```

Means assets were never built (or the manifest is stale). Run `npm run build` for a
production-style build, or `npm run dev` while developing. In CI the workflow builds assets
before running tests.

## Adding a new entrypoint

1. Add the file to `laravel({ input: [...] })` in `vite.config.js`.
2. Reference it with `@vite([...])` in the layout.
3. Rebuild.

## Notes for the docs site

`docs-site/` is a separate Node project with its own `package.json` (Docusaurus). It is not
part of the Laravel build and does not share the Tailwind theme — its palette is mirrored
manually in `docs-site/src/css/custom.css`. Keep the two in sync if branding changes.
