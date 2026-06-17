/**
 * Scaffold config for theme-elementary, consumed by bin/init.js and handed to
 * the shared scaffold engine in rtcamp/wp-framework.
 *
 * Search tokens are embedded verbatim; safe because the engine never
 * search-replaces files under bin/.
 */

// functions.php and the theme's Tailwind enable constant (derived from the
// resolved identity). functions.php defines it false by default; the feature
// flips it, and Assets.php enqueues off it.
const tailwindEntry = () => 'functions.php';
const tailwindConst = ( api ) => `${ api.identity.constantPrefix }_ENABLE_TAILWIND`;

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

	// Identity fields shown in the review table and offered in the editor.
	fields: [
		{ key: 'name', label: 'Theme Name' },
		{ key: 'version', label: 'Version' },
		{ key: 'textDomain', label: 'Text Domain' },
		{ key: 'package', label: 'Package' },
		{ key: 'namespace', label: 'Namespace' },
		{ key: 'functionPrefix', label: 'Function Prefix' },
		{ key: 'constantPrefix', label: 'Constant Prefix' },
		{ key: 'cssPrefix', label: 'CSS Prefix' },
	],

	versionFiles: [
		{ path: 'style.css', kind: 'css-header' },
		{ path: 'package.json', kind: 'json', key: 'version' },
	],

	steps: { composer: true, cleanup: true, git: true, husky: true },

	// Optional features toggled in manage mode. Tailwind enqueue is gated on the
	// ELEMENTARY_THEME_ENABLE_TAILWIND constant in functions.php; the feature flips
	// it and adds/removes the entry CSS, PostCSS config and deps. webpack still
	// gates the theme.json token plugin on the entry file at build time.
	featuresDir: 'bin/features',
	features: [
		{
			key: 'tailwind',
			label: 'Tailwind CSS',
			description: 'Tailwind v4 (opt-in). Adds the entry CSS, PostCSS config and deps, and flips the ENABLE_TAILWIND constant that gates the enqueue.',
			apply: {
				files: [
					{ from: 'tailwind/tailwind.css', to: 'src/css/frontend/tailwind.css' },
					{ from: 'tailwind/postcss.config.js', to: 'postcss.config.js' },
				],
				devDependencies: {
					tailwindcss: '^4.3.0',
					'@tailwindcss/postcss': '^4.3.0',
				},
			},
			onEnable: ( api ) => api.setDefine( tailwindEntry( api ), tailwindConst( api ), true ),
			onDisable: ( api ) => api.setDefine( tailwindEntry( api ), tailwindConst( api ), false ),
			detect: ( api ) => true === api.readDefine( tailwindEntry( api ), tailwindConst( api ) ),
		},
	],

	cleanup: { targets: [ '.github', 'languages' ] },

	docsUrl: 'https://github.com/rtCamp/theme-elementary/blob/main/README.md',
	repoUrl: 'https://github.com/rtCamp/theme-elementary',
};
