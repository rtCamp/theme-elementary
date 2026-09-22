# Asset builds

This document explains how the theme's asset pipeline builds and manages
CSS, JavaScript, and Interactivity API modules, and how to add a new file to
it.

You make your edits under `src/`; the build process compiles them into the
gitignored `assets/build/` directory, which you should never edit directly —
anything written there is overwritten on the next build.
[Local development](local-development.md) owns the actual watch/build
commands. The pipeline itself is Webpack, configured in `webpack.config.js`,
with its build scripts and dependencies living in `package.json`.

## Overview

The build process handles several kinds of assets:

- **CSS/SCSS files** under `src/css/frontend/`, `src/css/admin/`, and
  `src/css/editor/` are extracted with `MiniCssExtractPlugin` into their
  matching directories under `assets/build/css/`, and minified with
  `CssMinimizerPlugin` in production builds.
- **JavaScript files** under the matching `src/js/` context directories are
  bundled and processed through Babel for browser compatibility, then output
  to `assets/build/js/`. `webpack-remove-empty-scripts` drops any script
  entry that has no content, so a directory with only CSS doesn't emit an
  empty `.js` file.
- **Interactivity API modules** under `src/js/frontend/modules/` are treated
  as their own entry point, through a dedicated `moduleScripts` entry in
  `webpack.config.js`, and compiled into `assets/build/js/modules/`.
- **Fonts** are copied as-is from `src/fonts/` to `assets/build/fonts/`.
- **SVGs** are optimized with SVGO and copied from `src/images/svg/` to
  `assets/build/images/svg/`; an SVG that fails to optimize is copied
  unchanged rather than failing the build.

Fonts and SVGs go through `CopyWebpackPlugin` rather than the JS/CSS
compilers above — they're copied (and, for SVGs, optimized), not compiled.

New files under `src/js/` and `src/css/` are picked up automatically: a
`readAllFileEntries` helper scans each context directory at build time, so
nothing needs registering in `webpack.config.js` itself. Interactivity API
modules are discovered the same way, through the `moduleScripts` entry
mentioned above:

```js
entry: () => readAllFileEntries( './src/js/frontend/modules' ),
```

`frontend/`, `admin/`, and `editor/` are the three contexts under both
`src/css/` and `src/js/`, for public-facing, wp-admin, and block-editor code
respectively. Frontend and admin scripts share one build; editor scripts
build separately so they keep webpack-dev-server's Fast Refresh during
development.

### Key configuration files

- **`webpack.config.js`** is the main configuration file for building assets.
- **`package.json`** contains the build scripts and dependencies.

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

JavaScript asset metadata records dependencies and versions. Component CSS
also gets metadata; RTL styles are generated alongside the relevant
stylesheets. Keep generated files out of source edits.

## Add an asset

To add a new script or module to the build process:

1. Put a file in the appropriate source directory, for example
   `src/js/frontend/gallery.js` for a frontend script, or
   `src/js/frontend/modules/gallery.js` for an Interactivity API module.
2. No webpack configuration change is needed — the `readAllFileEntries`
   helper (or, for modules, the `moduleScripts` entry) discovers it
   automatically the next time you build.
3. Build it:
   ```bash
   npm run build:dev  # development
   npm run build:prod # production
   ```
   and confirm `assets/build/js/frontend/gallery.js` (or the matching
   modules path) exists.
4. Register and enqueue it through the theme's `inc/Core/Assets.php` on the
   appropriate hook, following the existing asset registrations. Discovery
   adds a file to the build; it does not automatically enqueue every new
   entry in WordPress. The framework's
   [asset-loader reference](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md)
   explains its methods.
5. Check the frontend or admin network panel for the file and its
   dependencies.

### Excluding a file from the build

Files prefixed with `_` are not standalone entries — Webpack excludes them
from the build so they can't be enqueued directly. Use this for imported
partials that other files pull in:

- **CSS/SCSS**: name the file `_my-partial.scss` to include it without
  bundling it on its own, for example `_variables.scss`.
- **JavaScript**: the same prefix works for JS, for example a shared
  `_helpers.js`.

Do not put ordinary scripts inside the `modules` directory: that directory is
reserved for Interactivity API modules.

## Components and blocks

A component's asset filename matches its directory, for example
`src/components/card/card.js` and `card.scss`. Its PHP render file stays in
`src/components/card/card.php`. The theme component loader connects
rendering and assets; see [Included features](features.md#supplied-examples).

Custom blocks have their own build and registration. Keep their metadata and
sources under `src/blocks/`, and point PHP registration at the corresponding
`assets/build/blocks/` output. The starter does not require a custom block to
render its standard block-theme templates.

## Styles and Tailwind

SCSS globals/mixins and shared imports live under `src/css/`. Optional
Tailwind uses its own entry and generates WordPress preset tokens at build
time; see [Tailwind](tailwind.md). Webpack is already configured for these
theme inputs.
