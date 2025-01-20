/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		// Ensure changes to PHP, html, JS, JSX files and theme.json trigger a rebuild.
		'./**/*.{php,html,js,jsx}',
		'./theme.json',
	],
	theme: {
		extend: {},
	},
	plugins: [],
};
