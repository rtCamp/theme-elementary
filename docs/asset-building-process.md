# Asset Building Process

This document explains how the theme's asset pipeline builds and manages CSS, JavaScript, and Interactivity API modules, and how to add a new file to it.

You make your edits under `src/`; the build process compiles them into the gitignored `assets/build/` directory, which you should never edit directly — anything written there is overwritten on the next build. [Local development](local-development.md) owns the actual watch/build commands. The pipeline itself is Webpack, configured in `webpack.config.js`, with its build scripts and dependencies living in `package.json`.

## Overview

Our asset pipeline is managed by **Webpack**. The build process involves the following steps:

1. **CSS/SCSS files** under `src/css/frontend/`, `src/css/admin/`, and `src/css/editor/` are extracted with `MiniCssExtractPlugin` into their matching directories under `assets/build/css/`, and minified with `CssMinimizerPlugin` in production builds.
2. **JavaScript files** under the matching `src/js/` context directories are bundled and processed through Babel for browser compatibility, then output to `assets/build/js/`. `webpack-remove-empty-scripts` removes any script entry that has no content, so a directory with only CSS doesn't emit an empty `.js` file.
3. **Modules** under `src/js/frontend/modules/` are handled separately, through a dedicated `moduleScripts` entry in `webpack.config.js`, and compiled into `assets/build/js/modules/` to ensure they're loaded correctly.
4. **Fonts** are copied from `src/fonts/` to `assets/build/fonts/`.
5. **SVGs** are optimized with SVGO and copied from `src/images/svg/` to `assets/build/images/svg/`; an SVG that fails to optimize is copied unchanged rather than failing the build.

`frontend/`, `admin/`, and `editor/` are the three contexts under both `src/css/` and `src/js/`, for public-facing, wp-admin, and block-editor code respectively. Frontend and admin scripts share one build; editor scripts build separately so they keep webpack-dev-server's Fast Refresh during development.

### Key Configuration Files

- **webpack.config.js**: This is the main configuration file for building assets.
- **package.json**: Contains the scripts and dependencies necessary for the build process.

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

JavaScript asset metadata records dependencies and versions. Component CSS also gets metadata; RTL styles are generated alongside the relevant stylesheets. Keep generated files out of source edits.

## Adding New Scripts or Modules

To add a new script or module to the build process, follow these steps:

### Adding a New Script

1. Place your JavaScript file in the appropriate context subdirectory under `src/js/`. Example: `src/js/frontend/my-script.js`.
2. The `readAllFileEntries` helper in `webpack.config.js` automatically discovers files in `src/js/frontend/`, `src/js/admin/`, and `src/js/editor/`. No webpack config changes are needed.
3. Run the build script:

   ```bash
   npm run build:dev  # For development
   npm run build:prod # For production
   ```

4. Register and enqueue it through the theme's `inc/Core/Assets.php` on the appropriate hook, following the existing asset registrations. Discovery adds a file to the build; it does not automatically enqueue every new entry in WordPress. The framework's [asset-loader reference](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md) explains its methods.

### Adding a New Module

1. Place your module JavaScript file in the `src/js/frontend/modules` directory. Example: `src/js/frontend/modules/my-module.js`.
2. The modules will automatically be included in the Webpack build process via the `readAllFileEntries` helper:

   ```js
   entry: () => readAllFileEntries( './src/js/frontend/modules' ),
   ```

3. Run the build script:

   ```bash
   npm run build:dev  # For development
   npm run build:prod # For production
   ```

   Do not put ordinary scripts inside the `modules` directory — it's reserved for Interactivity API modules.

## Avoid Bundling Specific Files

For example, if you have a file like `_my-excluded-script.js` or `_my-excluded-styles.css`, Webpack will **ignore** it when bundling and it won't be included in the final output.

### How to Exclude Files

- **CSS/SCSS**: If you want to add a CSS file without bundling it, name it starting with an underscore. Example: `_my-excluded-styles.scss`.
- **JavaScript**: Similarly, prefix JS files with an underscore to prevent bundling. Example: `_my-excluded-script.js`.

By naming files with the underscore, we make sure they are excluded from the Webpack build process but can still be used elsewhere in the project — for imported partials that other files pull in, such as `_variables.scss` or a shared `_helpers.js`.

## Components and blocks

A component's asset filename matches its directory, for example `src/components/card/card.js` and `card.scss`. Its PHP render file stays in `src/components/card/card.php`. The theme component loader connects rendering and assets; see [Included features](features.md#supplied-examples).

Custom blocks have their own build and registration. Keep their metadata and sources under `src/blocks/`, and point PHP registration at the corresponding `assets/build/blocks/` output. The starter does not require a custom block to render its standard block-theme templates.

## Styles and Tailwind

SCSS globals/mixins and shared imports live under `src/css/`. Optional Tailwind uses its own entry and generates WordPress preset tokens at build time; see [Tailwind](tailwind.md). Webpack is already configured for these theme inputs.
