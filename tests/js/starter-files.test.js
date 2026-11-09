/** Keep the starter-only files out of every install route. */
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '../..');
// A theme made from the starter has run init, which removes .gitattributes and
// this test; skip there in case it was kept.
const inStarter = !fs.existsSync(path.join(root, '.wp-scaffold.json'));

(inStarter ? describe : describe.skip)('starter-only files', () => {
	test('every export-ignore path is also an init cleanup target', () => {
		const ignored = fs
			.readFileSync(path.join(root, '.gitattributes'), 'utf8')
			.split('\n')
			.map((line) => line.trim())
			.filter((line) => /\sexport-ignore$/.test(line))
			.map((line) => line.split(/\s+/)[0].replace(/^\//, ''));
		const { cleanup } = require('../../bin/scaffold.config.js');

		expect(ignored.length).toBeGreaterThan(0);
		expect(cleanup.targets).toEqual(expect.arrayContaining(ignored));
	});
});
