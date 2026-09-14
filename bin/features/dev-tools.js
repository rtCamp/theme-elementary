/**
 * WP Dev Tools feature, declared for the @rtcamp/wp-tooling init engine.
 *
 * Writes committed manifest wiring (composer.json, package.json scripts) plus
 * local dev-environment state (.wp-env.override.json, gitignored). The
 * committed .wp-env.json is never touched, so enabling this never forces Query
 * Monitor and the MCP Adapter onto teammates.
 */

/**
 * External dependencies
 */
const path = require('path');

// Repo is rtCamp/wp-devtools, package is rtcamp/wp-dev-tools -- both correct.
const PACKAGE = 'rtcamp/wp-dev-tools';
// No `main` branch upstream. Once wp-devtools is published, this and REPOSITORY
// give way to a plain version constraint.
const CONSTRAINT = 'dev-release/v1.0.0';
const REPOSITORY = {
	type: 'vcs',
	url: 'https://github.com/rtCamp/wp-devtools.git',
	'no-api': true,
};

// Query Monitor collects; the MCP Adapter exposes. wp-env plugins, not deps.
// The MCP Adapter is pinned; Query Monitor uses its current stable download.
// Bump the adapter URL when a newer release is adopted.
const PLUGINS = [
	'https://downloads.wordpress.org/plugin/query-monitor.zip',
	'https://github.com/WordPress/mcp-adapter/releases/download/v0.5.0/mcp-adapter.zip',
];

const COMPOSER_FILE = 'composer.json';
const GITIGNORE_FILE = '.gitignore';
const WP_ENV_FILE = '.wp-env.json';
const WP_ENV_OVERRIDE = '.wp-env.override.json';

// The override half `detect` keys off. Shared with the config it writes, so a
// rename cannot leave detect silently reporting "disabled".
const DEV_MODE_KEY = 'RT_DEV_TOOLS_DEV_MODE';

// `--user=admin`: every ability is capability-checked and fails anonymously.
const CONNECT_SCRIPT =
	'claude mcp add wp-dev-tools -- npx wp-env run cli -- wp mcp-adapter serve --server=wp-dev-tools --user=admin';
const DISCONNECT_SCRIPT = 'claude mcp remove wp-dev-tools';

// `{}` for a missing file, null for malformed -- callers decide which matters.
const readJson = (api, rel) => {
	try {
		return JSON.parse(api.read(rel) || '{}');
	} catch {
		return null;
	}
};

// Containers stop carrying their own weight once emptied; drop them so disable
// leaves no hollow `"require-dev": {}` behind.
const deleteIfEmpty = (parent, key) => {
	if (parent[key] && !Object.keys(parent[key]).length) {
		delete parent[key];
	}
};

// wp-env mounts "." as the host folder name, not the text domain.
const containerRoot = (api) => {
	const bucket = 'theme' === api.identity.kind ? 'themes' : 'plugins';
	return `/var/www/html/wp-content/${bucket}/${path.basename(api.root)}`;
};

// wp-env replaces the plugins array rather than merging it, so the override has
// to re-emit whatever .wp-env.json already lists.
const basePlugins = (api) => {
	const wpEnv = readJson(api, WP_ENV_FILE) || {};
	const development = (wpEnv.env && wpEnv.env.development) || {};
	if (Array.isArray(development.plugins)) {
		return development.plugins;
	}
	const override = readJson(api, WP_ENV_OVERRIDE) || {};
	if (Array.isArray(override.plugins)) {
		return override.plugins;
	}
	if (Array.isArray(wpEnv.plugins)) {
		return wpEnv.plugins;
	}
	return 'theme' === api.identity.kind ? [] : ['.'];
};

// Override entries that are neither committed nor ours: a developer's own.
const developerPlugins = (api, development) => {
	const base = basePlugins(api);
	const listed = Array.isArray(development.plugins)
		? development.plugins
		: [];
	return listed.filter(
		(url) => !base.includes(url) && !PLUGINS.includes(url)
	);
};

// SAVEQUERIES is not part of the gate -- it is what gives Query Monitor the
// backtraces behind file:line. A wrong root pair makes every host_file null.
const overrideConfig = (api) => ({
	WP_ENVIRONMENT_TYPE: 'local',
	SAVEQUERIES: true,
	[DEV_MODE_KEY]: true,
	RT_DEV_TOOLS_TELEMETRY_CONTAINER_ROOT: containerRoot(api),
	RT_DEV_TOOLS_TELEMETRY_HOST_ROOT: api.root,
	RT_DEV_TOOLS_TELEMETRY_LOOPBACK_BASE: 'http://wordpress',
});

// Defensive, for forks that trimmed their .gitignore: the override holds
// absolute host paths and must never be committed.
const ensureGitignored = (api) => {
	const current = api.read(GITIGNORE_FILE);
	if (null === current) {
		api.write(GITIGNORE_FILE, `/${WP_ENV_OVERRIDE}\n`);
		return;
	}
	const listed = current
		.split('\n')
		.some((line) => line.trim().replace(/^\//, '') === WP_ENV_OVERRIDE);
	if (listed) {
		return;
	}
	const prefix = current.endsWith('\n') ? current : `${current}\n`;
	api.write(GITIGNORE_FILE, `${prefix}/${WP_ENV_OVERRIDE}\n`);
};

const addComposerEntry = (api) => {
	api.editJson(COMPOSER_FILE, (json) => {
		const repositories = json.repositories || [];
		const hasRepository = Object.values(repositories).some(
			(entry) => entry && entry.url === REPOSITORY.url
		);
		if (!hasRepository) {
			if (Array.isArray(repositories)) {
				repositories.push({ ...REPOSITORY });
			} else {
				let key = 'wp-dev-tools';
				let suffix = 2;
				while (Object.hasOwn(repositories, key)) {
					key = `wp-dev-tools-${suffix++}`;
				}
				repositories[key] = { ...REPOSITORY };
			}
		}
		json.repositories = repositories;
		json['require-dev'] = {
			...json['require-dev'],
			[PACKAGE]: CONSTRAINT,
		};
	});
};

const removeComposerEntry = (api) => {
	api.editJson(COMPOSER_FILE, (json) => {
		if (json['require-dev']) {
			delete json['require-dev'][PACKAGE];
			deleteIfEmpty(json, 'require-dev');
		}
		if (json.repositories) {
			const keep = (entry) => !entry || entry.url !== REPOSITORY.url;
			json.repositories = Array.isArray(json.repositories)
				? json.repositories.filter(keep)
				: Object.fromEntries(
						Object.entries(json.repositories).filter(([, entry]) =>
							keep(entry)
						)
					);
			deleteIfEmpty(json, 'repositories');
		}
	});
};

const writeOverride = (api) => {
	api.editJson(
		WP_ENV_OVERRIDE,
		(json) => {
			json.env = json.env || {};
			const development = json.env.development || {};
			// Rebuilt from the current committed list, so a .wp-env.json that
			// gained a plugin is picked up rather than masked.
			development.plugins = [
				...new Set([
					...basePlugins(api),
					...developerPlugins(api, development),
					...PLUGINS,
				]),
			];
			development.config = {
				...development.config,
				...overrideConfig(api),
			};
			json.env.development = development;
		},
		{ create: true, indentFrom: WP_ENV_FILE }
	);
};

// wp-env replaces this array, so anything left here pins the dev env. Drop it
// when only the committed list remains; a developer's own entry can be kept
// only by restating the base beside it.
const prunePlugins = (api, development) => {
	const kept = developerPlugins(api, development);
	if (!kept.length) {
		delete development.plugins;
		return;
	}
	development.plugins = [...new Set([...basePlugins(api), ...kept])];
};

// Prune ours, then drop the file only if nothing else is holding it up.
const pruneOverride = (api) => {
	if (!api.exists(WP_ENV_OVERRIDE)) {
		return;
	}

	api.editJson(WP_ENV_OVERRIDE, (json) => {
		const development = (json.env && json.env.development) || null;
		if (!development) {
			return;
		}
		if (development.config) {
			Object.keys(overrideConfig(api)).forEach(
				(key) => delete development.config[key]
			);
			deleteIfEmpty(development, 'config');
		}
		if (Array.isArray(development.plugins)) {
			prunePlugins(api, development);
		}
		deleteIfEmpty(json.env, 'development');
		deleteIfEmpty(json, 'env');
	});

	const remaining = readJson(api, WP_ENV_OVERRIDE) || {};
	if (!Object.keys(remaining).length) {
		api.remove(WP_ENV_OVERRIDE);
	}
};

const onEnable = (api) => {
	addComposerEntry(api);
	writeOverride(api);
	ensureGitignored(api);

	// `update`, not `install`: the lock has no entry for a just-added package.
	api.note(
		`wp-dev-tools: run \`composer update ${PACKAGE} -W\`, then \`npm run wp-env start\`.`
	);
	if ('theme' === api.identity.kind) {
		const theme =
			"'" + path.basename(api.root).replace(/'/g, "'\\''") + "'";
		api.note(
			`wp-dev-tools: activate this theme with \`npm run wp-env run cli -- wp theme activate ${theme}\`, then run \`npm run dev:connect\`.`
		);
	} else {
		api.note(
			'wp-dev-tools: run `npm run dev:connect` once the plugin is active.'
		);
	}
	api.note(
		`wp-dev-tools: ${WP_ENV_OVERRIDE} carries this machine's paths and is gitignored -- teammates run \`npm run init -- --enable=dev-tools\` to wire their own.`
	);
};

const onDisable = (api) => {
	removeComposerEntry(api);
	api.note(
		`wp-dev-tools: run \`composer update ${PACKAGE} -W\` to drop it from composer.lock and vendor/. Disconnect Claude with \`claude mcp remove wp-dev-tools\` if still connected.`
	);
	pruneOverride(api);
};

// Both halves required: the composer entry alone would read "enabled" on a
// clone where the gitignored override is missing and nothing actually works.
// Total by contract -- it also runs read-only under `init --list`.
const detect = (api) => {
	const composer = readJson(api, COMPOSER_FILE);
	const override = readJson(api, WP_ENV_OVERRIDE);
	if (!composer || !override) {
		return false;
	}
	const development = (override.env && override.env.development) || {};
	return (
		Boolean((composer['require-dev'] || {})[PACKAGE]) &&
		true === (development.config || {})[DEV_MODE_KEY]
	);
};

module.exports = {
	key: 'dev-tools',
	label: 'WP Dev Tools (runtime telemetry over MCP)',
	category: 'Developer Tooling',
	description:
		'Opt-in. Adds rtcamp/wp-dev-tools as a dev dependency and the dev:connect script, and writes the local wp-env override (Query Monitor + the MCP Adapter, the dev-mode gate, and the host path map). The committed .wp-env.json is left untouched.',
	apply: {
		scripts: {
			'dev:connect': CONNECT_SCRIPT,
			'dev:disconnect': DISCONNECT_SCRIPT,
		},
	},
	onEnable,
	onDisable,
	detect,
};
