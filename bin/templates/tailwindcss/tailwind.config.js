/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		// Ensure changes to PHP, html, JS files and theme.json trigger a rebuild.
		'./**/*.{php,html}',
		'./src/**/*.{scss,css,js,jsx}',
		'./theme.json',
	],
	theme: {
		extend: {},
	},
	plugins: [],
};
