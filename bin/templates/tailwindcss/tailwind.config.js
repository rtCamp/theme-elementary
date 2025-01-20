/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
            // Ensure changes to PHP, html files and theme.json trigger a rebuild.
            './**/*.{php,html}',
            './theme.json',
        ],
    theme: {
        extend: {},
    },
    plugins: [],
}
