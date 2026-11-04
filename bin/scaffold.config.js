/**
 * Scaffold config for theme-elementary, consumed by bin/init.js and handed to
 * the shared init engine in @rtcamp/wp-tooling.
 *
 * Search tokens are embedded verbatim; safe because the engine never
 * search-replaces files under bin/.
 */

// functions.php and the theme's Tailwind enable constant (derived from the
// resolved identity). functions.php defines it false by default; the feature
// flips it, and Assets.php enqueues off it.
const tailwindEntry = () => 'functions.php';
const tailwindConst = (api) => `${api.identity.constantPrefix}_ENABLE_TAILWIND`;

// Release automation (release-please). The "release" feature copies the workflow
// and config from bin/features/release/; seedRelease then gives a new project a
// release history of its own. The starter's own config and manifest live in
// .github/release-please/, which cleanup removes.
const RELEASE_MANIFEST = '.release-please-manifest.json';
const RELEASE_CONFIG = 'release-please-config.json';

// HEAD of the repository init runs in, or null without git or commits.
const gitHead = (root) => {
	try {
		return require('child_process')
			.execFileSync('git', ['rev-parse', '--verify', '-q', 'HEAD'], {
				cwd: root,
				encoding: 'utf8',
				stdio: ['ignore', 'pipe', 'ignore'],
			})
			.trim();
	} catch {
		return null;
	}
};

const seedRelease = (api) => {
	// The manifest records the last released version, so a project that turns the
	// feature off and on again keeps its history. "0.0.0" tells release-please
	// nothing is released yet.
	const fresh = !api.exists(RELEASE_MANIFEST);
	if (fresh) {
		api.editJson(
			RELEASE_MANIFEST,
			(manifest) => {
				manifest['.'] = '0.0.0';
			},
			{ create: true, indentFrom: RELEASE_CONFIG }
		);
	}
	const released = JSON.parse(api.read(RELEASE_MANIFEST))['.'];
	api.editJson(RELEASE_CONFIG, (config) => {
		// Until the first release, that release is the version init wrote.
		if ('0.0.0' === released) {
			config.packages['.']['initial-version'] = api.identity.version;
		}
		// Commits up to this one (the starter's history, when the clone keeps it)
		// stay out of the first changelog entry.
		const head = fresh ? gitHead(api.root) : null;
		if (head) {
			config['bootstrap-sha'] = head;
		}
	});
	// During first setup (no .wp-scaffold.json yet) CHANGELOG.md is still the
	// starter's. Drop it: release-please writes a fresh one on the first release.
	if (fresh && !api.exists('.wp-scaffold.json')) {
		api.remove('CHANGELOG.md');
	}
};

module.exports = {
	kind: 'theme',
	vendor: 'rtcamp',

	// Placeholder identity. Namespace and package are explicit since they don't follow the name pattern.
	source: {
		name: 'Elementary Theme',
		namespace: 'rtCamp\\Theme\\Elementary',
		package: 'rtcamp/theme-elementary',
	},

	// Derive the namespace and composer package from the chosen name.
	namespace: (id) => `rtCamp\\Theme\\${id.pascalSnake}`,
	package: (id) => `rtcamp/${id.kebab}`,

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
			description:
				'Tailwind v4 (opt-in). Adds the entry CSS, PostCSS config and deps, and flips the ENABLE_TAILWIND constant that gates the enqueue.',
			apply: {
				files: [
					{
						from: 'tailwind/tailwind.css',
						to: 'src/css/frontend/tailwind.css',
					},
					{
						from: 'tailwind/postcss.config.js',
						to: 'postcss.config.js',
					},
				],
				devDependencies: {
					'@rtcamp/tailwind-config': '^1.1.0',
					tailwindcss: '^4.3.0',
					'@tailwindcss/postcss': '^4.3.0',
				},
			},
			onEnable: (api) =>
				api.setDefine(tailwindEntry(api), tailwindConst(api), true),
			onDisable: (api) =>
				api.setDefine(tailwindEntry(api), tailwindConst(api), false),
			detect: (api) =>
				true === api.readDefine(tailwindEntry(api), tailwindConst(api)),
		},
		{
			key: 'release',
			defaultOn: true,
			label: 'Release automation (release-please)',
			description:
				'A release pull request kept up to date from Conventional Commit PR titles; merging it bumps style.css and package.json, writes CHANGELOG.md, tags vX.Y.Z and publishes the GitHub release. Default on. Needs squash merges with the PR title as the commit subject, and Actions allowed to create pull requests.',
			apply: {
				files: [
					{
						from: 'release/release.yml',
						to: '.github/workflows/release.yml',
					},
					{
						from: 'release/release-please-config.json',
						to: RELEASE_CONFIG,
					},
				],
			},
			onEnable: seedRelease,
			// Disabling removes the workflow and config (apply.files) and keeps the
			// manifest, the record of what was released.
			onDisable: () => {},
		},
		{
			key: 'hmr',
			defaultOn: true,
			label: 'HMR (BrowserSync live reload)',
			description:
				'Live reload in watch mode. Toggling flips ENABLE_HMR in .env.local, which webpack (BrowserSync server) and PHP (client enqueue) both honour. Default on; deps stay installed.',
			// No files or deps: the code lives in webpack.config.js + Assets.php
			// permanently and is gated on the flag. detect reads the live flag,
			// defaulting on when .env.local (gitignored) has no ENABLE_HMR.
			onEnable: (api) => api.setEnv('.env.local', 'ENABLE_HMR', 'true'),
			onDisable: (api) => api.setEnv('.env.local', 'ENABLE_HMR', 'false'),
			detect: (api) => {
				const value = api.readEnv('.env.local', 'ENABLE_HMR');
				return (
					null === value ||
					!['false', '0', 'no', 'off'].includes(value.toLowerCase())
				);
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
				strip: ['inc/Main.php'],
				remove: [
					'inc/Modules/BlockExtensions',
					'patterns/media-text-interactive.php',
					'src/js/frontend/modules/media-text.js',
					'tests/php/inc/Modules/BlockExtensions',
					'tests/js/media-text.test.js',
				],
			},
			{
				key: 'settings',
				label: 'Theme options settings page',
				marker: 'wp:example:settings',
				strip: ['inc/Main.php'],
				// Only the example page: FeaturesSettingsPage in the same directory is
				// core (Settings -> Features) and stays registered in Main::CLASSES.
				remove: [
					'inc/Modules/Settings/ThemeOptions.php',
					'tests/php/inc/Modules/Settings/ThemeOptionsTest.php',
				],
			},
			{
				key: 'shortcode',
				label: 'Author bio shortcode',
				marker: 'wp:example:shortcode',
				strip: ['inc/Main.php'],
				remove: [
					'inc/Modules/Shortcodes',
					'template-parts/author-bio.php',
					'tests/php/inc/Modules/Shortcodes',
				],
			},
			{
				key: 'components',
				label: 'Example components (button, card)',
				remove: ['src/components/button', 'src/components/card'],
			},
			{
				key: 'patterns',
				label: 'Page-creation pattern',
				remove: ['patterns/page-creation-pattern.php'],
			},
		],
	},

	cleanup: {
		targets: [
			'.github/copilot-instructions.md',
			'.github/prompts',
			'.github/instructions',
			'languages',
			// The starter's own release workflow, config and manifest. A project gets
			// its own from the "release" feature.
			'.github/release-please',
			'.github/workflows/release-starter.yml',
		],
	},

	docsUrl: 'https://github.com/rtCamp/theme-elementary/blob/main/README.md',
	repoUrl: 'https://github.com/rtCamp/theme-elementary',
};
