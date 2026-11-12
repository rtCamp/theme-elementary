/**
 * ESLint configuration.
 *
 * Extends the Jest entry of `@rtcamp/eslint-config` (the shared rtCamp
 * toolchain, which bundles the WordPress ESLint plugin, the import/jsdoc/prettier
 * rules and the Jest rules for test files) so this theme lints identically to
 * the plugin skeleton.
 */
import rtCampConfig from '@rtcamp/eslint-config/jest';

export default [
	...rtCampConfig,
	{
		ignores: [
			'**/*.min.js',
			'**/node_modules/**',
			'**/vendor/**',
			'assets/build/**',
			// Jest coverage output (coverageDirectory in tests/js/jest.config.js).
			'coverage/**',
			'tests/logs/**',
		],
	},
];
