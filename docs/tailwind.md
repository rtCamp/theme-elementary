# Tailwind CSS (opt-in)

Tailwind v4 support is opt-in. When enabled, `GenerateTailwindThemePlugin` reads `theme.json` and generates a `_tailwind-theme.css` file that maps WordPress preset tokens (colors, font sizes, font families, spacing) to Tailwind v4 utility namespaces.

## Setup

**1. Install the plugin package**
```bash
npm install --save-dev @rtcamp/tailwind-config tailwindcss @tailwindcss/postcss
```

**2. Configure PostCSS**

Create or update `postcss.config.js` in the theme root:
```js
module.exports = require( '@rtcamp/tailwind-config/postcss' );
```

**3. Add the plugin to `webpack.config.js`**

In the `styles` compiler config, add `GenerateTailwindThemePlugin` to the plugins array:
```js
const { GenerateTailwindThemePlugin } = require( '@rtcamp/tailwind-config' );

// inside the styles config:
plugins: [
    new GenerateTailwindThemePlugin(),
    // ...existing plugins
],
```

**4. Generate and commit the entry file**
```bash
npm start
```

This scaffolds `src/css/frontend/tailwind.css` (your editable entry point) and generates `src/css/frontend/_tailwind-theme.css` (auto-generated, do not edit). Commit `tailwind.css` — `_tailwind-theme.css` is gitignored and regenerated on every build.
