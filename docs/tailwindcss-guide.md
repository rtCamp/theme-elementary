# TailwindCSS Setup for WordPress Theme

This documentation provides a guide for setting up and using TailwindCSS in your WordPress theme. The configuration files and integration steps are outlined below to ensure a seamless development experience.

---

## **Setup Overview**

### **Key Files and Locations**
- **Tailwind Configuration**: `tailwind.config.js`  
- **PostCSS Configuration**: `postcss.config.js`  
- **Main Tailwind CSS File**: `/assets/src/css/tailwind.scss`

### **Automatic Enqueueing**
The main Tailwind CSS file (`tailwind.scss`) is automatically enqueued if TailwindCSS is enabled in the project. You can directly use TailwindCSS classes in your PHP, HTML, and JS files.

---

## **Configuration**

### **`tailwind.config.js`**
This file is pre-configured to watch for changes in PHP, HTML, and JS files, as well as the `theme.json`. You can extend the configuration as needed.

Example `tailwind.config.js`:
```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './**/*.{php,html,js,jsx}', // Monitors all PHP, HTML, and JS files
        './theme.json', // Watches for changes in the theme.json
    ],
    theme: {
        extend: {}, // Extend or override default Tailwind styles here
    },
    plugins: [], // Add TailwindCSS plugins here
};
```

### **`postcss.config.js`**
This file ensures that TailwindCSS is processed correctly with PostCSS. If additional PostCSS plugins are required, you can add them here.

---

## **Usage**

### **Using TailwindCSS in PHP/HTML/JS**
You can use TailwindCSS classes directly in your:
- PHP files
- HTML templates
- JavaScript files (including JSX)

Example in PHP:
```php
<div class="bg-blue-500 text-white p-4">
    <?php echo esc_html__('Hello, TailwindCSS!', 'your-theme'); ?>
</div>
```

---

## **Compiling TailwindCSS**

To compile the TailwindCSS into the build folder, use the following commands:

### **Commands**
- **Start Development Server**:
  ```bash
  npm run start
  ```
  This will initiate the development server and watch for changes.

- **Build for Development**:
  ```bash
  npm run build:dev
  ```
  This compiles the CSS for development use.

- **Build for Production**:
  ```bash
  npm run build:prod
  ```
  This compiles and optimizes the CSS for production.

The compiled Tailwind CSS file will be placed in the `build` folder.

---

## **Extending Functionality**

To customize the theme, add configurations directly to the `tailwind.config.js` or `postcss.config.js` files. For example:
- Add custom colors or fonts in `theme.extend` within `tailwind.config.js`.
- Integrate additional PostCSS plugins like `autoprefixer` or `cssnano`.

Example: Adding Custom Colors
```javascript
module.exports = {
    theme: {
        extend: {
            colors: {
                primary: '#1a202c',
                secondary: '#2d3748',
            },
        },
    },
};
```

---

## **Additional Notes**

- Any of the above mentioned build commands will compile the TailiwindCSS.
