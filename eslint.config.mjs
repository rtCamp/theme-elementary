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
import tseslint from 'typescript-eslint';

const TEST_FILES = [
	'**/__tests__/**/*.{js,ts,tsx}',
	'**/test/*.{js,ts,tsx}',
	'**/?(*.)test.{js,ts,tsx}',
	'tests/js/**/*.{js,ts,tsx}',
];

export default [
	{
		ignores: [
			'**/*.min.js',
			'**/node_modules/**',
			'**/vendor/**',
			'assets/build/**',
		],
	},

	...wordpressPlugin.configs[ 'recommended-with-formatting' ],

	// `recommended-with-formatting` (unlike `recommended`) doesn't register a
	// TypeScript config, so `.ts`/`.tsx` would be unmatched and skipped. Add the
	// TypeScript parser + the unused-vars handoff, mirroring the TS block in
	// @wordpress/eslint-plugin's `recommended` preset.
	{
		files: [ '**/*.ts', '**/*.tsx' ],
		languageOptions: {
			parser: tseslint.parser,
		},
		plugins: {
			'@typescript-eslint': tseslint.plugin,
		},
		rules: {
			'no-duplicate-imports': 'off',
			'jsdoc/require-param-type': 'off',
			'jsdoc/require-returns-type': 'off',
			'no-unused-vars': 'off',
			'@typescript-eslint/no-unused-vars': [
				'error',
				{ ignoreRestSiblings: true },
			],
		},
	},

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

	{
		settings: {
			'import/resolver': {
				typescript: {
					extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
				},
				node: true,
			},
		},
	},
];
