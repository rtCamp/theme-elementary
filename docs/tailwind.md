# Tailwind CSS (opt-in feature)

Tailwind v4 support is an **opt-in feature** managed through the theme's setup. When enabled,
[`@rtcamp/tailwind-config`](https://github.com/rtCamp/wp-tooling)'s `GenerateTailwindThemePlugin`
reads `theme.json` and generates `src/css/frontend/_tailwind-theme.css`, mapping WordPress preset
tokens (colors, font sizes, font families, spacing) to Tailwind v4 utility namespaces. It also
scaffolds `src/css/frontend/tailwind.css` (your editable entry point) on the first build.

## Enable / disable it

Tailwind is toggled by the feature manager — run init at any time:

```bash
npm run init
```

On a fresh theme this runs during initial setup; on an already-initialized theme it jumps straight
to the feature manager. Answer **yes** at the Tailwind prompt to enable it (or **no** to disable a
previously enabled one). Enabling:

- creates `postcss.config.js` (re-exporting `@rtcamp/tailwind-config/postcss`),
- sets `"features": { "tailwind": true }` in `.wp-tooling.json` (read by `webpack.config.js`),
- records the dev dependencies `@rtcamp/tailwind-config`, `tailwindcss`, `@tailwindcss/postcss`.

After enabling, install the dependencies and build:

```bash
npm install
npm start
```

`npm start` runs `GenerateTailwindThemePlugin`, which writes `src/css/frontend/tailwind.css` (edit
freely — committed) and `src/css/frontend/_tailwind-theme.css` (auto-generated on every build, kept
out of git via `.gitignore`).

Disabling (re-run `npm run init` and answer **no**) removes `postcss.config.js` and the generated
`_tailwind-theme.css`, clears the flag so webpack stops loading the plugin, and asks before deleting
your editable `tailwind.css`.

## How it works

- `webpack.config.js` reads `.wp-tooling.json` and only loads `GenerateTailwindThemePlugin` (from
  `@rtcamp/tailwind-config`) in the styles compiler when `features.tailwind` is `true`. Toggling the
  feature flips this flag — no manual webpack edits.
- The compiled `assets/build/css/frontend/tailwind.css` is enqueued only when
  `src/css/frontend/tailwind.css` exists (i.e. after the plugin has run at least once).
- `watchOptions.ignored` excludes `assets/build/**` so Tailwind v4's content detection does not
  trigger an infinite rebuild loop.

## Manual setup (advanced)

The feature manager is the supported path, but you can wire it by hand: install
`@rtcamp/tailwind-config tailwindcss @tailwindcss/postcss`, add
`module.exports = require('@rtcamp/tailwind-config/postcss');` to `postcss.config.js`, and set
`{ "features": { "tailwind": true } }` in `.wp-tooling.json`.
