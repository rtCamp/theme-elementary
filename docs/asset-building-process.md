# Asset builds

Edit source under `src/`; builds go under the gitignored `assets/build/`.
[Local development](local-development.md) owns the watch/build commands.

## Source to output

| Source | Output |
| --- | --- |
| `src/css/frontend/*.css` / `*.scss` | `assets/build/css/frontend/` |
| `src/css/admin/`, `src/css/editor/` | Matching directories under `assets/build/css/` |
| `src/js/frontend/`, `src/js/admin/`, `src/js/editor/` | Matching directories under `assets/build/js/` |
| `src/js/frontend/modules/*.js` | `assets/build/js/modules/` (Interactivity API modules) |
| `src/components/button/button.js` | `assets/build/js/components/button.js` |
| `src/components/button/button.scss` | `assets/build/css/components/button.css` |
| `src/fonts/` | `assets/build/fonts/` |
| `src/images/svg/` | Optimized SVGs under `assets/build/images/svg/` |
| `src/blocks/` | `assets/build/blocks/` via the separate block build |

JavaScript asset metadata records dependencies and versions. Component CSS also
gets metadata; RTL styles are generated alongside the relevant stylesheets.
Keep generated files out of source edits.

## Add an asset

1. Put a file in the appropriate source directory, for example
   `src/js/frontend/gallery.js`.
2. Build and confirm `assets/build/js/frontend/gallery.js` exists.
3. Register and enqueue it through the theme's `inc/Core/Assets.php` on the
   appropriate hook, following the existing asset registrations.
4. Check the frontend or admin network panel for the file and its dependencies.

Discovery adds a file to the build; it does not automatically enqueue every new
entry in WordPress. The framework's [asset-loader reference](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md)
explains its methods.

Files prefixed with `_` are not standalone entries. Use these for imported
helpers/partials. Do not put ordinary scripts inside the `modules` directory:
that directory is reserved for Interactivity API modules.

## Components and blocks

A component's asset filename matches its directory, for example
`src/components/card/card.js` and `card.scss`. Its PHP render file stays in
`src/components/card/card.php`. The theme component loader connects rendering
and assets; see [Included features](features.md#supplied-examples).

Custom blocks have their own build and registration. Keep their metadata and
sources under `src/blocks/`, and point PHP registration at the corresponding
`assets/build/blocks/` output. The starter does not require a custom block to
render its standard block-theme templates.

## Styles and Tailwind

SCSS globals/mixins and shared imports live under `src/css/`. Optional Tailwind
uses its own entry and generates WordPress preset tokens at build time; see
[Tailwind](tailwind.md). Webpack is already configured for these theme inputs.
