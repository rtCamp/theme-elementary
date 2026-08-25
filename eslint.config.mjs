/**
 * ESLint configuration.
 *
 * Extends @rtcamp/eslint-config (the shared rtCamp toolchain, which bundles
 * @wordpress/eslint-plugin and the import/jsdoc/prettier rules) so this theme
 * lints identically to the plugin skeleton.
 */
import rtCampConfig from '@rtcamp/eslint-config';

export default [
	...rtCampConfig,
	{
		ignores: [ '**/*.min.js', '**/node_modules/**', '**/vendor/**', 'assets/build/**' ],
	},
];
