/**
 * Dev-tools feature hooks for the theme init engine.
 *
 * Driven through the real FeatureApi rather than a stand-in, so the hooks are
 * exercised against the same editJson/read/write semantics the init engine
 * uses. The declarative `apply` half (scripts) is the engine's job and is
 * covered upstream; this file covers the theme's feature hooks.
 */

const fs = require('fs');
const path = require('path');

const { makeFeatureApi } = require('@rtcamp/wp-tooling/init');
const feature = require('../../bin/features/dev-tools');

const OVERRIDE = '.wp-env.override.json';
const WP_ENV = '.wp-env.json';
const QM = 'https://downloads.wordpress.org/plugin/query-monitor.zip';
const ADAPTER =
	'https://github.com/WordPress/mcp-adapter/releases/download/v0.5.0/mcp-adapter.zip';

const write = (root, rel, body) => fs.writeFileSync(path.join(root, rel), body);

const readJson = (root, rel) =>
	JSON.parse(fs.readFileSync(path.join(root, rel), 'utf8'));

const exists = (root, rel) => fs.existsSync(path.join(root, rel));

/**
 * Project fixture with a committed wp-env config listing `plugins`.
 *
 * @param {Array}  basePlugins - env.development.plugins in .wp-env.json.
 * @param {string} kind        - Consumer kind ('plugin' or 'theme').
 * @return {Object} { root, api }.
 */
const setup = (basePlugins, kind = 'theme') => {
	const fixtures = path.join(__dirname, '../logs');
	fs.mkdirSync(fixtures, { recursive: true });
	const root = fs.mkdtempSync(path.join(fixtures, 'dev-tools-'));
	const base = basePlugins
		? { env: { development: { plugins: basePlugins } } }
		: { core: null, themes: ['.'], env: { tests: { port: 5891 } } };
	write(root, WP_ENV, `${JSON.stringify(base, null, '\t')}\n`);
	write(root, 'composer.json', '{\n\t"name": "acme/demo"\n}\n');
	write(root, 'package.json', '{\n\t"name": "demo"\n}\n');
	write(root, '.gitignore', `/${OVERRIDE}\n`);

	const api = makeFeatureApi(
		root,
		{ kind, slug: 'demo' },
		{ info: () => {}, warn: () => {} }
	);
	return { root, api };
};

/**
 * Rewrite the committed plugin list, simulating a teammate adding one.
 *
 * @param {string} root    - Project root.
 * @param {Array}  plugins - New env.development.plugins.
 * @return {void}
 */
const setBasePlugins = (root, plugins) => {
	const json = readJson(root, WP_ENV);
	json.env.development.plugins = plugins;
	write(root, WP_ENV, `${JSON.stringify(json, null, '\t')}\n`);
};

describe('dev-tools feature', () => {
	let root;
	let api;

	afterEach(() => {
		if (root) {
			fs.rmSync(root, { recursive: true, force: true });
			root = null;
		}
	});

	describe('enable', () => {
		beforeEach(() => {
			({ root, api } = setup(['.']));
		});

		it('adds the composer dependency and its VCS repository', () => {
			feature.onEnable(api);

			const composer = readJson(root, 'composer.json');
			expect(composer['require-dev']).toHaveProperty(
				'rtcamp/wp-dev-tools'
			);
			expect(composer.repositories).toEqual([
				expect.objectContaining({
					type: 'vcs',
					url: 'https://github.com/rtCamp/wp-devtools.git',
				}),
			]);
		});

		it('leaves the committed .wp-env.json untouched', () => {
			const before = fs.readFileSync(path.join(root, WP_ENV), 'utf8');

			feature.onEnable(api);

			expect(fs.readFileSync(path.join(root, WP_ENV), 'utf8')).toBe(
				before
			);
		});

		it('writes the override with the base list plus the dev plugins', () => {
			feature.onEnable(api);

			const development = readJson(root, OVERRIDE).env.development;
			expect(development.plugins).toEqual(['.', QM, ADAPTER]);
			expect(development.config).toEqual({
				WP_ENVIRONMENT_TYPE: 'local',
				SAVEQUERIES: true,
				RT_DEV_TOOLS_DEV_MODE: true,
				RT_DEV_TOOLS_TELEMETRY_CONTAINER_ROOT: `/var/www/html/wp-content/themes/${path.basename(
					root
				)}`,
				RT_DEV_TOOLS_TELEMETRY_HOST_ROOT: root,
				RT_DEV_TOOLS_TELEMETRY_LOOPBACK_BASE: 'http://wordpress',
			});
		});

		it('never writes the tests environment', () => {
			feature.onEnable(api);

			expect(readJson(root, OVERRIDE).env.tests).toBeUndefined();
		});

		it('is idempotent -- no duplicate repository or plugin entries', () => {
			feature.onEnable(api);
			const composer = fs.readFileSync(
				path.join(root, 'composer.json'),
				'utf8'
			);

			feature.onEnable(api);

			expect(
				fs.readFileSync(path.join(root, 'composer.json'), 'utf8')
			).toBe(composer);
			expect(
				readJson(root, OVERRIDE).env.development.plugins
			).toHaveLength(3);
		});

		it('defers next steps rather than printing mid-spinner', () => {
			feature.onEnable(api);

			expect(api._notes.length).toBeGreaterThan(0);
			expect(api._notes[0]).toContain('composer update');
		});

		it('refreshes from the committed list on re-enable', () => {
			feature.onEnable(api);
			setBasePlugins(root, ['.', 'team-plugin.zip']);

			feature.onEnable(api);

			expect(readJson(root, OVERRIDE).env.development.plugins).toEqual([
				'.',
				'team-plugin.zip',
				QM,
				ADAPTER,
			]);
		});
	});

	describe('disable', () => {
		beforeEach(() => {
			({ root, api } = setup(['.']));
		});

		it('leaves no trace of itself', () => {
			feature.onEnable(api);

			feature.onDisable(api);

			const composer = readJson(root, 'composer.json');
			expect(composer['require-dev']).toBeUndefined();
			expect(composer.repositories).toBeUndefined();
			expect(exists(root, OVERRIDE)).toBe(false);
			expect(feature.detect(api)).toBe(false);
		});

		// The empty-container case above only happens in a bare fixture; a real
		// project has its own dev deps and VCS entries, which must survive.
		it("keeps the project's other dev deps and repositories", () => {
			api.editJson('composer.json', (json) => {
				json['require-dev'] = { 'phpunit/phpunit': '^9.6' };
				json.repositories = [
					{
						type: 'vcs',
						url: 'https://github.com/rtCamp/wp-framework.git',
					},
				];
			});
			feature.onEnable(api);

			feature.onDisable(api);

			const composer = readJson(root, 'composer.json');
			expect(composer['require-dev']).toEqual({
				'phpunit/phpunit': '^9.6',
			});
			expect(composer.repositories).toEqual([
				{
					type: 'vcs',
					url: 'https://github.com/rtCamp/wp-framework.git',
				},
			]);
		});

		it('says how to drop the package from the lock and vendor', () => {
			feature.onEnable(api);
			api._notes.length = 0;

			feature.onDisable(api);

			expect(api._notes.join('\n')).toContain('composer update');
		});

		// Regression: an exact base/remainder comparison kept `plugins: ["."]`
		// pinned in the override once .wp-env.json had moved on. wp-env replaces
		// that array, so the dev env silently lost the newly added plugin.
		it('drops the plugin pin when the committed list has moved on', () => {
			feature.onEnable(api);
			setBasePlugins(root, ['.', 'team-plugin.zip']);

			feature.onDisable(api);

			expect(exists(root, OVERRIDE)).toBe(false);
		});

		it("keeps a developer's own override plugin, refreshed against the base", () => {
			feature.onEnable(api);
			api.editJson(OVERRIDE, (json) => {
				json.env.development.plugins.push('my-own.zip');
			});
			setBasePlugins(root, ['.', 'team-plugin.zip']);

			feature.onDisable(api);

			expect(readJson(root, OVERRIDE).env.development).toEqual({
				plugins: ['.', 'team-plugin.zip', 'my-own.zip'],
			});
		});

		it("keeps a developer's own override config key", () => {
			feature.onEnable(api);
			api.editJson(OVERRIDE, (json) => {
				json.env.development.config.MY_OWN_KEY = 'keep me';
			});

			feature.onDisable(api);

			expect(readJson(root, OVERRIDE).env.development.config).toEqual({
				MY_OWN_KEY: 'keep me',
			});
		});

		it('is a no-op when the override was never written', () => {
			expect(() => feature.onDisable(api)).not.toThrow();
			expect(exists(root, OVERRIDE)).toBe(false);
		});
	});

	describe('themes', () => {
		it('tells theme users to activate the mounted directory before connecting', () => {
			({ root, api } = setup(null, 'theme'));
			feature.onEnable(api);
			expect(api._notes.join('\n')).toContain(
				`wp theme activate '${path.basename(root)}'`
			);
		});
		it('omits "." and targets the themes bucket', () => {
			({ root, api } = setup(null, 'theme'));

			feature.onEnable(api);

			const development = readJson(root, OVERRIDE).env.development;
			expect(development.plugins).toEqual([QM, ADAPTER]);
			expect(
				development.config.RT_DEV_TOOLS_TELEMETRY_CONTAINER_ROOT
			).toBe(`/var/www/html/wp-content/themes/${path.basename(root)}`);
		});
	});

	describe('local configuration edge cases', () => {
		beforeEach(() => {
			({ root, api } = setup(null));
		});
		it('adds a missing ignore entry once without losing existing patterns', () => {
			write(root, '.gitignore', '*.log');
			feature.onEnable(api);
			feature.onEnable(api);
			expect(api.read('.gitignore')).toBe(
				'*.log\n/.wp-env.override.json\n'
			);
		});
		it('creates a missing gitignore', () => {
			fs.rmSync(path.join(root, '.gitignore'));
			feature.onEnable(api);
			expect(api.read('.gitignore')).toBe('/.wp-env.override.json\n');
		});
		it('preserves existing test settings and unrelated development settings', () => {
			write(
				root,
				OVERRIDE,
				JSON.stringify({
					env: {
						tests: { port: 9981, config: { WP_DEBUG: false } },
						development: {
							port: 9980,
							config: { CUSTOM_FLAG: true },
						},
					},
				})
			);
			feature.onEnable(api);
			feature.onDisable(api);
			expect(readJson(root, OVERRIDE)).toEqual({
				env: {
					tests: { port: 9981, config: { WP_DEBUG: false } },
					development: { port: 9980, config: { CUSTOM_FLAG: true } },
				},
			});
		});
		it('preserves plugins declared at the top level of the committed config', () => {
			write(
				root,
				WP_ENV,
				JSON.stringify({ themes: ['.'], plugins: ['team.zip'] })
			);
			feature.onEnable(api);
			expect(readJson(root, OVERRIDE).env.development.plugins).toEqual([
				'team.zip',
				QM,
				ADAPTER,
			]);
		});
		it('preserves plugins declared at the top level of a local override', () => {
			write(root, OVERRIDE, JSON.stringify({ plugins: ['local.zip'] }));
			feature.onEnable(api);
			expect(readJson(root, OVERRIDE).env.development.plugins).toContain(
				'local.zip'
			);
			feature.onDisable(api);
			expect(readJson(root, OVERRIDE)).toEqual({
				plugins: ['local.zip'],
			});
		});
		it('supports named Composer repositories', () => {
			write(
				root,
				'composer.json',
				JSON.stringify({
					repositories: {
						framework: {
							type: 'vcs',
							url: 'https://github.com/rtCamp/wp-framework.git',
						},
					},
				})
			);
			feature.onEnable(api);
			feature.onDisable(api);
			expect(readJson(root, 'composer.json').repositories).toEqual({
				framework: {
					type: 'vcs',
					url: 'https://github.com/rtCamp/wp-framework.git',
				},
			});
		});
	});

	describe('detect', () => {
		beforeEach(() => {
			({ root, api } = setup(['.']));
		});

		it('is true only when both halves are present', () => {
			expect(feature.detect(api)).toBe(false);

			feature.onEnable(api);

			expect(feature.detect(api)).toBe(true);
		});

		// A teammate's fresh clone has the committed dependency but not the
		// gitignored override, and nothing actually works there.
		it('is false with the composer entry alone', () => {
			feature.onEnable(api);
			fs.rmSync(path.join(root, OVERRIDE));

			expect(feature.detect(api)).toBe(false);
		});

		it('is false with the override alone', () => {
			feature.onEnable(api);
			api.editJson('composer.json', (json) => {
				delete json['require-dev']['rtcamp/wp-dev-tools'];
			});

			expect(feature.detect(api)).toBe(false);
		});

		// detect also runs read-only under `init --list`, before any rename.
		it('is total against an empty project and malformed JSON', () => {
			const bare = fs.mkdtempSync(path.join(__dirname, '../logs/bare-'));
			try {
				const bareApi = makeFeatureApi(
					bare,
					{ kind: 'theme', slug: 'demo' },
					{}
				);
				expect(feature.detect(bareApi)).toBe(false);

				write(bare, 'composer.json', '{ not json');
				expect(() => feature.detect(bareApi)).not.toThrow();
				expect(feature.detect(bareApi)).toBe(false);
			} finally {
				fs.rmSync(bare, { recursive: true, force: true });
			}
		});
	});
});
