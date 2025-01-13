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
	entry: () => {
		const entries = {};
		const dir = './assets/src/css';

		if ( ! fs.existsSync( dir ) ) {
			return entries;
		}

		if ( fs.readdirSync( dir ).length === 0 ) {
			return entries;
		}

		fs.readdirSync( dir ).forEach( ( fileName ) => {
			const fullPath = `${ dir }/${ fileName }`;
			if ( ! fs.lstatSync( fullPath ).isDirectory() ) {
				entries[ fileName.replace( /\.[^/.]+$/, '' ) ] = fullPath;
			}
		} );

		return entries;
	},
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
	entry: () => {
		const entries = {};
		const dir = './assets/src/js/modules';

		if ( ! fs.existsSync( dir ) ) {
			return entries;
		}

		if ( fs.readdirSync( dir ).length === 0 ) {
			return entries;
		}

		fs.readdirSync( dir ).forEach( ( fileName ) => {
			const fullPath = `${ dir }/${ fileName }`;
			if ( ! fs.lstatSync( fullPath ).isDirectory() ) {
				entries[ fileName.replace( /\.[^/.]+$/, '' ) ] = fullPath;
			}
		} );

		return entries;
	},
	output: {
		...moduleConfig.output,
		path: path.resolve( process.cwd(), 'assets', 'build', 'js', 'modules' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
};

module.exports = [ scripts, styles, moduleScripts ];
