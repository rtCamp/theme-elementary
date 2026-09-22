# Tailwind CSS

Tailwind is optional. When enabled, the theme connects WordPress design
tokens to Tailwind utilities and builds the resulting stylesheet through the
existing asset pipeline — no separate build tool or config to manage by hand.

Under the hood, `GenerateTailwindThemePlugin` reads `theme.json` and
generates a `_tailwind-theme.css` file that maps WordPress preset tokens to
Tailwind v4 utility namespaces, so a color, font size, or spacing value you
set once in `theme.json` is usable as a Tailwind utility class too.

## Enable and build

From an initialized theme directory:

```bash
npm run init -- --enable=tailwind --yes
npm install
npm run build:prod
```

Init adds the entry and PostCSS configuration, declares the packages, and
turns on the theme's Tailwind enqueue constant — you don't need to edit
webpack or manually recreate the configuration yourself. See
[Initialization](initialization.md) for managing the full feature selection.

## What to edit

| File | Purpose |
| --- | --- |
| `theme.json` | WordPress presets used to generate Tailwind tokens — color palette, font sizes, font families, spacing sizes, and shadow presets. |
| `src/css/frontend/tailwind.css` | Editable Tailwind entry; commit your changes. |
| `src/css/frontend/_tailwind-theme.css` | Generated token definitions; ignored by Git, do not edit — it's regenerated on every build. |
| `postcss.config.js` | Theme's connection to the shared PostCSS config. |
| `assets/build/css/frontend/tailwind.css` | Compiled stylesheet, enqueued while the feature is enabled. |

The entry scans the project for utility usage. It imports Tailwind's theme
and utilities without Preflight — Preflight is a CSS reset that conflicts
with the block editor's own base styles and `wp-block-styles`. Package
behavior belongs in the
[shared Tailwind reference](https://github.com/rtCamp/wp-tooling/blob/main/node-packages/tailwind-config/README.md).

## Verify a change

Add `class="underline"` to a text element in a source template, rebuild, and
confirm the frontend text is underlined. Put token-based utilities in source
files too: classes stored only in the WordPress database are not files
scanned by Tailwind.

If compilation fails, confirm npm installation completed and both the entry
and generated token file exist. Restart a running watcher after changing
dependencies. To disable Tailwind, use
`npm run init -- --disable=tailwind --yes`, run `npm install`, and rebuild.
Review changes to your editable entry before disabling.
