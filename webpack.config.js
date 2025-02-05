/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

/**
 * WordPress dependencies
 */
const [ scriptConfig, moduleConfig ] = require( '@wordpress/scripts/config/webpack.config' );

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
		...scriptConfig.plugins
			.map(
				( plugin ) => {
					if ( plugin.constructor.name === 'MiniCssExtractPlugin' ) {
						plugin.options.filename = '../css/[name].css';
					}
					return plugin;
				},
			),
		new RemoveEmptyScriptsPlugin(),
	],
	optimization: {
		...scriptConfig.optimization,
		splitChunks: {
			...scriptConfig.optimization.splitChunks,
		},
		minimizer: scriptConfig.optimization.minimizer.concat( [ new CssMinimizerPlugin() ] ),
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
			( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin',
		),
	],

};

const scripts = {
	...sharedConfig,
	entry: {
		'core-navigation': path.resolve( process.cwd(), 'assets', 'src', 'js', 'core-navigation.js' ),
	},
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
