/** Check the wrapper's observable success, failure, and read-only behavior. */
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const wrapper = path.resolve(__dirname, '../../bin/init.js');

describe('init wrapper', () => {
	let root;
	let engine;
	beforeEach(() => {
		const logs = path.resolve(__dirname, '../logs');
		fs.mkdirSync(logs, { recursive: true });
		root = fs.mkdtempSync(path.join(logs, 'wrapper-'));
		fs.mkdirSync(path.join(root, 'bin'));
		fs.copyFileSync(wrapper, path.join(root, 'bin/init.js'));
		fs.writeFileSync(
			path.join(root, 'bin/scaffold.config.js'),
			'module.exports = {};'
		);
		fs.writeFileSync(
			path.join(root, 'bin/sync-ai.js'),
			"require('fs').writeFileSync('synced', 'yes');"
		);
		engine = path.join(root, 'node_modules/@rtcamp/wp-tooling');
		fs.mkdirSync(engine, { recursive: true });
		fs.writeFileSync(
			path.join(engine, 'package.json'),
			JSON.stringify({
				name: '@rtcamp/wp-tooling',
				exports: { './init': './init.js' },
			})
		);
	});
	afterEach(() => fs.rmSync(root, { recursive: true, force: true }));

	const run = (body, args = []) => {
		fs.writeFileSync(
			path.join(engine, 'init.js'),
			`exports.run = async () => { ${body} };`
		);
		return spawnSync(process.execPath, ['bin/init.js', ...args], {
			cwd: root,
			encoding: 'utf8',
			timeout: 5000,
		});
	};

	it('syncs after a successful mutation', () => {
		expect(run('').status).toBe(0);
		expect(fs.existsSync(path.join(root, 'synced'))).toBe(true);
	});
	it.each([1, 130])(
		'preserves exit %i and does not sync on failure/cancellation',
		(code) => {
			expect(run(`process.exitCode = ${code};`).status).toBe(code);
			expect(fs.existsSync(path.join(root, 'synced'))).toBe(false);
		}
	);
	it.each([['--list'], ['--list', '--json']])(
		'keeps status read-only with %j',
		(...args) => {
			const result = run(
				'console.log(JSON.stringify({ features: [] }));',
				args
			);
			expect(result.status).toBe(0);
			expect(JSON.parse(result.stdout)).toEqual({ features: [] });
			expect(fs.existsSync(path.join(root, 'synced'))).toBe(false);
		}
	);
	it('returns failure when the engine throws', () => {
		expect(run("throw new Error('fixture failure');").status).toBe(1);
		expect(fs.existsSync(path.join(root, 'synced'))).toBe(false);
	});
	it.each(['--help', '-h'])(
		'shows help without calling the engine for %s',
		(flag) => {
			const result = run("throw new Error('must not execute');", [flag]);
			expect(result.status).toBe(0);
			expect(result.stdout).toContain('dev-tools');
			expect(fs.existsSync(path.join(root, 'synced'))).toBe(false);
		}
	);
});
