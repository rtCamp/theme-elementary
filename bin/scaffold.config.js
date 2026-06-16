/**
 * Scaffold config for theme-elementary.
 *
 * Consumed by bin/init.js, which hands it to the shared scaffold engine in
 * rtcamp/wp-framework. Describes the placeholder identity to search-replace,
 * the explicit (non-pattern) tokens, the details table, version targets,
 * cleanup, and which optional steps run.
 *
 * NOTE: this file embeds the search tokens verbatim. It lives under bin/, which
 * the engine never search-replaces, so it is safe from self-corruption.
 */

module.exports = {
	kind: 'theme',
	vendor: 'rtcamp',

	// The starter's placeholder name. Every case variant is derived from this.
	source: { name: 'Elementary Theme' },

	// Explicit tokens that do not follow the name pattern.
	extraTokens: ( target ) => ( {
		// composer package name.
		'rtcamp/elementary': target.package,
		// PHP namespace as written in composer.json (escaped backslashes).
		'rtCamp\\\\Theme\\\\Elementary': `rtCamp\\\\Theme\\\\${ target.pascalSnake }`,
		// PHP namespace as written in source files (single backslashes).
		'rtCamp\\Theme\\Elementary': `rtCamp\\Theme\\${ target.pascalSnake }`,
	} ),

	// Canonical namespace persisted to .wp-scaffold.json.
	namespace: ( target ) => `rtCamp\\Theme\\${ target.pascalSnake }`,

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

	// Optional features toggled in manage mode (re-run `npm run init` once set up).
	// Each feature is data the engine interprets — see the Feature typedef in
	// rtcamp/wp-framework bin/scaffold/features.js. Example shape:
	//   {
	//     key: 'tailwind', label: 'Tailwind CSS', description: '...', defaultOn: false,
	//     apply: {
	//       files: [ { from: 'tailwind/tailwind.config.js', to: 'tailwind.config.js' } ],
	//       devDependencies: { tailwindcss: '^3.4.0', autoprefixer: '^10.4.0' },
	//       scripts: { 'build:tailwind': 'tailwindcss -i ... -o ...' },
	//     },
	//     detect: ( api ) => api.exists( 'tailwind.config.js' ) && api.hasDep( 'tailwindcss' ),
	//     onEnable:  ( api ) => api.writeFlag( 'tailwind', true ),   // webpack reads .wp-features.json
	//     onDisable: ( api ) => api.writeFlag( 'tailwind', false ),
	//   }
	// (Tailwind's webpack wiring lands with the Tailwind effort; the toggle engine is ready.)
	featuresDir: 'bin/features',
	features: [],

	cleanup: { targets: [ '.github', 'languages' ] },

	docsUrl: 'https://github.com/rtCamp/theme-elementary/blob/main/README.md',
	repoUrl: 'https://github.com/rtCamp/theme-elementary',
};
