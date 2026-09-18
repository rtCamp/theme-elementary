# Tailwind CSS

Tailwind is optional. The theme connects WordPress design tokens to Tailwind
utilities and builds its stylesheet through the existing asset pipeline.

## Enable and build

From an initialized theme directory:

```bash
npm run init -- --enable=tailwind --yes
npm install
npm run build:prod
```

Init adds the entry and PostCSS configuration, declares the packages, and turns
on the theme's Tailwind enqueue constant. You do not need to edit webpack or
manually recreate the configuration. See [Initialization](initialization.md)
for managing the full feature selection.

## What to edit

| File | Purpose |
| --- | --- |
| `theme.json` | WordPress presets used to generate Tailwind tokens — color palette, font sizes, font families, spacing sizes, and shadow presets. |
| `src/css/frontend/tailwind.css` | Editable Tailwind entry; commit your changes. |
| `src/css/frontend/_tailwind-theme.css` | Generated token definitions; ignored by Git, do not edit. |
| `postcss.config.js` | Theme's connection to the shared PostCSS config. |
| `assets/build/css/frontend/tailwind.css` | Compiled stylesheet, enqueued while the feature is enabled. |

The entry scans the project for utility usage. It imports Tailwind's theme and
utilities without Preflight, avoiding an extra global reset in WordPress.
Package behavior belongs in the
[shared Tailwind reference](https://github.com/rtCamp/wp-tooling/blob/main/node-packages/tailwind-config/README.md).

## Verify a change

Add `class="underline"` to a text element in a source template, rebuild, and
confirm the frontend text is underlined. Put token-based utilities in source
files too: classes stored only in the WordPress database are not files scanned
by Tailwind.

If compilation fails, confirm npm installation completed and both the entry and
generated token file exist. Restart a running watcher after changing dependencies.
To disable Tailwind, use `npm run init -- --disable=tailwind --yes`, run
`npm install`, and rebuild. Review changes to your editable entry before disabling.
