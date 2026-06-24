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

	steps: { composer: true, cleanup: true, git: true, hooks: true },

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
					'@rtcamp/tailwind-config': '^0.1.0',
					tailwindcss: '^4.3.0',
					'@tailwindcss/postcss': '^4.3.0',
				},
			},
			onEnable: ( api ) => api.setDefine( tailwindEntry( api ), tailwindConst( api ), true ),
			onDisable: ( api ) => api.setDefine( tailwindEntry( api ), tailwindConst( api ), false ),
			detect: ( api ) => true === api.readDefine( tailwindEntry( api ), tailwindConst( api ) ),
		},
		{
			key: 'hmr',
			label: 'HMR (BrowserSync live reload)',
			description: 'Live reload in watch mode. Toggling flips ENABLE_HMR in .env.local, which webpack (BrowserSync server) and PHP (client enqueue) both honour. Default on; deps stay installed.',
			// No files or deps: the code lives in webpack.config.js + Assets.php
			// permanently and is gated on the flag. detect reads the live flag,
			// defaulting on when .env.local (gitignored) has no ENABLE_HMR.
			onEnable: ( api ) => api.setEnv( '.env.local', 'ENABLE_HMR', 'true' ),
			onDisable: ( api ) => api.setEnv( '.env.local', 'ENABLE_HMR', 'false' ),
			detect: ( api ) => {
				const value = api.readEnv( '.env.local', 'ENABLE_HMR' );
				return null === value || ! [ 'false', '0', 'no', 'off' ].includes( value.toLowerCase() );
			},
		},
	],

	// First-run "which example sets to remove?" prompt. The three module groups
	// share inc/Main.php, so each scopes its own region with a keyed marker
	// (wp:example:<key>); components and patterns are delete-only (auto-discovered,
	// nothing to strip). Markers are stripped either way; code/files drop on remove.
	examples: {
		marker: 'wp:example',
		groups: [
			{
				key: 'block-extension',
				label: 'Media-text block extension',
				marker: 'wp:example:block-extension',
				strip: [ 'inc/Main.php' ],
				remove: [ 'inc/Modules/BlockExtensions', 'patterns/media-text-interactive.php', 'src/js/frontend/modules/media-text.js' ],
			},
			{
				key: 'settings',
				label: 'Theme options settings page',
				marker: 'wp:example:settings',
				strip: [ 'inc/Main.php' ],
				remove: [ 'inc/Modules/Settings' ],
			},
			{
				key: 'shortcode',
				label: 'Author bio shortcode',
				marker: 'wp:example:shortcode',
				strip: [ 'inc/Main.php' ],
				remove: [ 'inc/Modules/Shortcodes', 'tests/php/inc/Modules/Shortcodes' ],
			},
			{
				key: 'components',
				label: 'Example components (button, card)',
				remove: [ 'src/components/button', 'src/components/card' ],
			},
			{
				key: 'patterns',
				label: 'Page-creation pattern',
				remove: [ 'patterns/page-creation-pattern.php' ],
			},
		],
	},

	cleanup: { targets: [ '.github', 'languages' ] },

	docsUrl: 'https://github.com/rtCamp/theme-elementary/blob/main/README.md',
	repoUrl: 'https://github.com/rtCamp/theme-elementary',
};
