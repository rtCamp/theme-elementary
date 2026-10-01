module.exports = {
	rootDir: '../../',
	// Mirrors what @wordpress/jest-preset-default provided before
	// @wordpress/scripts 36 dropped it: a DOM, the same test discovery,
	// and style imports mapped to a mock. babel-jest is Jest's default
	// transform and reads babel.config.js.
	testEnvironment: 'jsdom',
	testMatch: [
		'**/__tests__/**/*.[jt]s?(x)',
		'**/test/*.[jt]s?(x)',
		'**/?(*.)test.[jt]s?(x)',
	],
	moduleNameMapper: {
		'\\.(scss|css)$': '<rootDir>/tests/js/mocks/style-mock.js',
	},
	testPathIgnorePatterns: [
		'<rootDir>/.git',
		'<rootDir>/node_modules',
		'<rootDir>/assets/build',
		'<rootDir>/vendor',
		// Add more specific patterns here if needed.
	],
	coveragePathIgnorePatterns: [
		'<rootDir>/node_modules',
		'<rootDir>/assets/build/',
		// Add more specific patterns here if needed.
	],
	modulePathIgnorePatterns: [
		// Add more specific patterns here if needed.
	],
	coverageReporters: ['lcov'],
	coverageDirectory: '<rootDir>/tests/logs',
	reporters: [['jest-silent-reporter', { useDots: true }], 'github-actions'],
};
