/**
 * Scaffold config for theme-elementary, consumed by bin/init.js and handed to
 * the shared scaffold engine in rtcamp/wp-framework.
 *
 * Search tokens are embedded verbatim; safe because the engine never
 * search-replaces files under bin/.
 */

module.exports = {
	kind: 'theme',
	vendor: 'rtcamp',

	// Placeholder identity. Namespace and package are explicit since they don't follow the name pattern.
	source: {
		name: 'Elementary Theme',
		namespace: 'rtCamp\\Theme\\Elementary',
		package: 'rtcamp/elementary',
	},

	// Derive the namespace and composer package from the chosen name.
	namespace: ( id ) => `rtCamp\\Theme\\${ id.pascalSnake }`,
	package: ( id ) => `rtcamp/${ id.kebab }`,

	version: '1.0.0',

	details: ( id, extra ) => ( {
		'Theme Name': id.name,
		'Version': extra.version,
		'Text Domain': id.textDomain,
		'Package': id.package,
		'Namespace': extra.namespace,
		'Function Prefix': id.functionPrefix,
		'Constant Prefix': id.constantPrefix,
		'CSS Prefix': id.cssPrefix,
	} ),

	versionFiles: [
		{ path: 'style.css', kind: 'css-header' },
		{ path: 'package.json', kind: 'json', key: 'version' },
	],

	steps: { composer: true, cleanup: true, git: true, husky: true },

	// Optional features toggled in manage mode (none yet).
	featuresDir: 'bin/features',
	features: [],

	cleanup: { targets: [ '.github', 'languages' ] },

	docsUrl: 'https://github.com/rtCamp/theme-elementary/blob/main/README.md',
	repoUrl: 'https://github.com/rtCamp/theme-elementary',
};
