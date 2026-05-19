/**
 * WordPress dependencies
 */
import wordpressPlugin from '@wordpress/eslint-plugin';
/**
 * External dependencies
 */
import comments from '@eslint-community/eslint-plugin-eslint-comments/configs';
import jestPlugin from 'eslint-plugin-jest';
import globals from 'globals';

const TEST_FILES = [
	'**/__tests__/**/*.js',
	'**/test/*.js',
	'**/?(*.)test.js',
	'tests/js/**/*.js',
];

export default [
	{
		ignores: [ '**/*.min.js', '**/node_modules/**', '**/vendor/**', 'build/*' ],
	},

	...wordpressPlugin.configs[ 'recommended-with-formatting' ],

	// import plugin is already registered by the WordPress config above;
	// add the remaining rules from plugin:import/recommended without re-registering.
	{
		languageOptions: {
			sourceType: 'module',
		},
		rules: {
			'import/no-unresolved': 'error',
			'import/named': 'error',
			'import/namespace': 'error',
			'import/default': 'error',
			'import/export': 'error',
			'import/no-named-as-default': 'warn',
			'import/no-named-as-default-member': 'warn',
			'import/no-duplicates': 'warn',
		},
	},

	comments.recommended,

	{
		languageOptions: {
			globals: globals.browser,
		},
		rules: {
			'jsdoc/check-indentation': 'error',
			'@wordpress/dependency-group': 'error',
		},
	},

	{
		files: TEST_FILES,
		...jestPlugin.configs[ 'flat/all' ],
	},
];
