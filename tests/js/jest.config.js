module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFiles: [
		'<rootDir>/tests/js/setup-globals',
	],
	preset: '@wordpress/jest-preset-default',
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
	coverageReporters: [ 'lcov' ],
	coverageDirectory: '<rootDir>/tests/logs',
	reporters: [
		[ 'jest-silent-reporter', { useDots: true } ],
		'<rootDir>/node_modules/@wordpress/scripts/config/jest-github-actions-reporter',
	],
};
