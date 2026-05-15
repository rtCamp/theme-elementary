/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

const isWatch =
	process.argv.includes( '--watch' ) || process.argv.includes( 'watch' );

if ( isWatch ) {
	require( 'dotenv' ).config( { path: '.env.local' } );
}

const bsPort = parseInt( process.env.BS_PORT, 10 ) || 3000;

/**
 * WordPress dependencies
 */
const [
	scriptConfig,
	moduleConfig,
] = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Read all file entries in a directory.
 * @param {string} dir Directory to read.
 * @return {Object} Object with file entries.
 */
const readAllFileEntries = ( dir ) => {
	const entries = {};

	if ( ! fs.existsSync( dir ) ) {
		return entries;
	}

	if ( fs.readdirSync( dir ).length === 0 ) {
		return entries;
	}

	fs.readdirSync( dir ).forEach( ( fileName ) => {
		const fullPath = `${ dir }/${ fileName }`;
		if ( ! fs.lstatSync( fullPath ).isDirectory() && ! fileName.startsWith( '_' ) ) {
			entries[ fileName.replace( /\.[^/.]+$/, '' ) ] = fullPath;
		}
	} );

	return entries;
};

// Extend the default config.
const sharedConfig = {
	...scriptConfig,
	output: {
		path: path.resolve( process.cwd(), 'assets', 'build', 'js' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...scriptConfig.plugins.map( ( plugin ) => {
			if ( plugin.constructor.name === 'MiniCssExtractPlugin' ) {
				plugin.options.filename = '../css/[name].css';
			}
			return plugin;
		} ),
		new RemoveEmptyScriptsPlugin(),
	],
	optimization: {
		...scriptConfig.optimization,
		splitChunks: {
			...scriptConfig.optimization.splitChunks,
		},
		minimizer: scriptConfig.optimization.minimizer.concat( [
			new CssMinimizerPlugin(),
		] ),
	},
};

// Generate a webpack config which includes setup for CSS extraction.
// Look for css/scss files and extract them into a build/css directory.
const styles = {
	...sharedConfig,
	entry: () => readAllFileEntries( './assets/src/css' ),
	module: {
		...sharedConfig.module,
	},
	plugins: [
		...sharedConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin',
		),
	],
};

const scripts = {
	...sharedConfig,
	entry: {
		'core-navigation': path.resolve(
			process.cwd(),
			'assets',
			'src',
			'js',
			'core-navigation.js',
		),
	},
	plugins: [
		...sharedConfig.plugins,
		...( isWatch
			? ( () => {
				const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );
				return [
					new BrowserSyncPlugin(
						{
							port: bsPort,
							...( process.env.WP_HOST ? { host: process.env.WP_HOST } : {} ),
							...( process.env.WP_SSL_KEY && process.env.WP_SSL_CERT
								? {
									https: {
										key: process.env.WP_SSL_KEY,
										cert: process.env.WP_SSL_CERT,
									},
								}
								: {} ),
							files: [
								'assets/build/**/*',
								'**/*.php',
								'!vendor/**',
								'**/*.html',
							],
							notify: false,
							open: false,
							logSnippet: false,
						},
						{
							injectCss: true,
						},
					),
				];
			} )()
			: [] ),
	],
};

const moduleScripts = {
	...moduleConfig,
	entry: () => readAllFileEntries( './assets/src/js/modules' ),
	output: {
		...moduleConfig.output,
		path: path.resolve( process.cwd(), 'assets', 'build', 'js', 'modules' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
};

module.exports = [ scripts, styles, moduleScripts ];
