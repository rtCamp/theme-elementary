/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		// Ensure changes to PHP, html, JS files and theme.json trigger a rebuild.
		'./**/*.{php,html}',
		'./src/**/*.js',
		'./theme.json',
	],
	theme: {
		extend: {},
	},
	plugins: [],
};
