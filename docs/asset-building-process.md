# Asset Building Process

This document outlines the process of building and managing assets (CSS, JS, and modules) within the theme. It also explains how to add new scripts or modules into the build process.

## Overview

Our asset pipeline is managed by **Webpack**, using the configuration provided by WordPress and some additional optimizations. The build process involves the following steps:

1. **JS and CSS Files** are processed, concatenated, and minified for production.
2. **Modules** are handled separately to ensure they're loaded correctly.
3. **CSS/SCSS files** are extracted and moved to a dedicated `css` directory.

### Key Configuration Files

- **webpack.config.js**: This is the main configuration file for building assets.
- **package.json**: Contains the scripts and dependencies necessary for the build process.

### Directory Structure

- **assets/src/js**: Contains JavaScript files.
- **assets/src/css**: Contains CSS/SCSS files.
- **assets/src/js/modules**: Contains modular JavaScript files that are handled separately.
- **assets/build/js**: Where built JavaScript files are output.
- **assets/build/css**: Where built CSS files are output.

---

## How the Asset Building Works

### JS and CSS Build Process

1. **CSS Files**: All `.css` or `.scss` files in the `assets/src/css` directory are collected into the build process. They are extracted into a separate CSS file in the `assets/build/css` folder.
   
   - The main `webpack.config.js` file uses the `MiniCssExtractPlugin` to extract the CSS.
   - The extracted CSS files are minified using `CssMinimizerPlugin` in production builds.

2. **JS Files**: The JavaScript files are bundled into a single file or multiple files (for split chunks). The `assets/src/js` folder contains files like `core-navigation.js`, which are part of the main entry point.

   - JavaScript files are processed using Babel to ensure compatibility with different browsers.
   - We use `webpack-remove-empty-scripts` to remove any empty JavaScript files that do not have content.

3. **Modules**: Files located in `assets/src/js/modules` are treated as separate entry points. These are compiled into separate files and stored in the `assets/build/js/modules` directory.
   
   - The configuration for modules is handled through the `moduleScripts` entry in the `webpack.config.js`.

---

## Adding New Scripts or Modules

To add a new script or module to the build process, follow these steps:

### Adding a New Script

1. Place your JavaScript file in the `assets/src/js` directory or a subdirectory.
   
   Example: `assets/src/js/my-script.js`

2. Edit the `webpack.config.js` file and add your script to the `scripts` entry object.
   
   Example:
   ```js
   entry: {
     'my-new-script': path.resolve( process.cwd(), 'assets', 'src', 'js', 'my-script.js' ),
   },
   ```

3. If necessary, add any required dependencies or libraries to `assets/src/js` and import them in your new script.

4. Run the build script:

   ```bash
   npm run build:dev  # For development
   npm run build:prod # For production
   ```

---

### Adding a New Module

1. Place your module JavaScript file in the `assets/src/js/modules` directory.

   Example: `assets/src/js/modules/my-module.js`

2. The modules will automatically be included in the Webpack build process, but if you want to make sure it is included in the final output, ensure it’s referenced correctly in the entry object for modules:

   ```js
   entry: () => readAllFileEntries( './assets/src/js/modules' ),
   ```

3. Add any necessary logic in your module’s JavaScript code to ensure it functions correctly within the theme. Modules are usually self-contained and independent, so make sure to export and import dependencies as needed.

4. Run the build script:

   ```bash
   npm run build:dev  # For development
   npm run build:prod # For production
   ```

## Avoid Bundling Specific Files

For example, if you have a file like `_my-excluded-script.js` or `_my-excluded-styles.css`, Webpack will **ignore** it when bundling and it won't be included in the final output.

### How to Exclude Files

- **CSS/SCSS**: If you want to add a CSS file without bundling it, name it starting with an underscore.
  
  Example: `_my-excluded-styles.scss`

- **JavaScript**: Similarly, prefix JS files with an underscore to prevent bundling.
  
  Example: `_my-excluded-script.js`

By naming files with the underscore, we make sure they are excluded from the Webpack build process but can still be used elsewhere in the project.
