/**
 * External dependencies
 */
const fs = require('fs');
const os = require('os');
const path = require('path');

jest.mock('@wordpress/scripts/config/webpack.config', () => [
	{
		optimization: {
			minimizer: [],
			splitChunks: {},
		},
		plugins: [],
		module: { rules: [] },
	},
	{
		output: {},
	},
]);

const {
	getComponentEntries,
	readAllFileEntries,
	toPort,
} = require('../../webpack.config');

/**
 * Create a file (and its parent directories) under a base directory.
 *
 * @param {string} base     Base directory.
 * @param {string} relative Relative file path.
 * @return {string} Absolute path of the created file.
 */
const touch = (base, relative) => {
	const file = path.join(base, relative);
	fs.mkdirSync(path.dirname(file), { recursive: true });
	fs.writeFileSync(file, '');
	return file;
};

describe('webpack component entries', () => {
	let tmpDir;

	afterEach(() => {
		if (tmpDir) {
			fs.rmSync(tmpDir, { recursive: true, force: true });
			tmpDir = undefined;
		}
	});

	it('only matches files with the exact component basename', () => {
		tmpDir = fs.mkdtempSync(
			path.join(os.tmpdir(), 'elementary-webpack-components-')
		);
		const buttonDir = path.join(tmpDir, 'button');

		fs.mkdirSync(buttonDir);
		fs.writeFileSync(path.join(buttonDir, 'button.js'), '');
		fs.writeFileSync(path.join(buttonDir, 'button-extra.js'), '');
		fs.writeFileSync(path.join(buttonDir, 'button.test.js'), '');
		fs.writeFileSync(path.join(buttonDir, 'button_utils.js'), '');

		expect(getComponentEntries(tmpDir, /\.js$/)).toEqual({
			'components/button': path.join(buttonDir, 'button.js'),
		});
	});
});

describe('webpack component entries (edge cases)', () => {
	let tmpDir;

	afterEach(() => {
		if (tmpDir) {
			fs.rmSync(tmpDir, { recursive: true, force: true });
			tmpDir = undefined;
		}
	});

	it('returns no entries for a missing directory', () => {
		expect(
			getComponentEntries(
				path.join(os.tmpdir(), 'no-such-dir-xyz'),
				/\.js$/
			)
		).toEqual({});
	});

	it('skips private folders, loose files and folders without a matching entry', () => {
		tmpDir = fs.mkdtempSync(
			path.join(os.tmpdir(), 'elementary-webpack-components-')
		);
		touch(tmpDir, '_private/_private.js');
		touch(tmpDir, '.hidden/.hidden.js');
		touch(tmpDir, 'loose.js');
		touch(tmpDir, 'card/card.scss');
		const button = touch(tmpDir, 'button/button.js');

		expect(getComponentEntries(tmpDir, /\.js$/)).toEqual({
			'components/button': button,
		});
	});
});

describe('readAllFileEntries', () => {
	let tmpDir;

	beforeEach(() => {
		tmpDir = fs.mkdtempSync(
			path.join(os.tmpdir(), 'elementary-webpack-entries-')
		);
	});

	afterEach(() => {
		fs.rmSync(tmpDir, { recursive: true, force: true });
		jest.restoreAllMocks();
	});

	it('returns no entries for a missing directory', () => {
		expect(readAllFileEntries(path.join(tmpDir, 'missing'))).toEqual({});
	});

	it('namespaces entries by context directory and flattens nested files', () => {
		const front = touch(tmpDir, 'frontend/main.js');
		const nested = touch(tmpDir, 'frontend/blocks/hero.js');
		const editor = touch(tmpDir, 'editor/editor.js');

		expect(readAllFileEntries(tmpDir)).toEqual({
			'frontend/main': front,
			'frontend/blocks/hero': nested,
			'editor/editor': editor,
		});
	});

	it('scans the directory itself when no context directory exists', () => {
		const file = touch(tmpDir, 'nested/thing.js');

		expect(readAllFileEntries(tmpDir)).toEqual({ 'nested/thing': file });
	});

	it('skips underscore and dot files and excluded directories', () => {
		const kept = touch(tmpDir, 'frontend/kept.js');
		touch(tmpDir, 'frontend/_partial.js');
		touch(tmpDir, 'frontend/.secret.js');
		touch(tmpDir, 'frontend/modules/module.js');

		expect(
			readAllFileEntries(tmpDir, { excludeDirs: ['modules'] })
		).toEqual({
			'frontend/kept': kept,
		});
	});

	it('keeps the first file when two resolve to the same entry and warns', () => {
		const kept = touch(tmpDir, 'frontend/dup.js');
		const ignored = touch(tmpDir, 'frontend/dup.ts');

		const entries = readAllFileEntries(tmpDir);

		expect(Object.keys(entries)).toEqual(['frontend/dup']);
		expect(console).toHaveWarnedWith(
			`Duplicate webpack entry "frontend/dup" ignored: ${ignored} (keeping ${kept})`
		);
	});
});

describe('toPort', () => {
	it.each([
		['3002', 3002],
		['1', 1],
		['65535', 65535],
	])('accepts %s', (value, expected) => {
		expect(toPort(value, 3001)).toBe(expected);
	});

	it.each([
		[undefined],
		[''],
		['0'],
		['65536'],
		['abc'],
		['-5'],
		['8888foo'],
		['30.5'],
		['0x10'],
	])('falls back for %p', (value) => {
		expect(toPort(value, 3001)).toBe(3001);
	});
});
