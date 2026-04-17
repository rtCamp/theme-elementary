/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const { optimize: svgoOptimize } = require( 'svgo' );

/**
 * WordPress dependencies
 */
const [ scriptConfig, moduleConfig ] = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Context subdirectories scanned for entry points.
 *
 * @type {string[]}
 */
const CONTEXT_DIRS = [ 'frontend', 'admin', 'editor' ];

/**
 * Read all file entries by scanning context subdirectories.
 *
 * Recurses one level into each context subdirectory (frontend/, admin/,
 * editor/) inside the given directory, collecting every file whose name
 * does not start with `_` or `.`. If none of the context subdirectories
 * exist, the directory itself is scanned instead.
 *
 * @param {string} dir Base directory to scan.
 * @return {Object} Object mapping entry names to file paths.
 */
const readAllFileEntries = ( dir ) => {
	const entries = {};

	if ( ! fs.existsSync( dir ) ) {
		return entries;
	}

	const resolvedDir = path.resolve( dir );

	const contextPaths = CONTEXT_DIRS
		.map( ( ctx ) => path.join( resolvedDir, ctx ) )
		.filter( ( ctxPath ) => fs.existsSync( ctxPath ) );

	const dirsToScan = contextPaths.length > 0 ? contextPaths : [ resolvedDir ];

	const useNamespace = contextPaths.length > 0;

	for ( const scanDir of dirsToScan ) {
		const prefix = useNamespace ? `${ path.basename( scanDir ) }/` : '';

		fs.readdirSync( scanDir ).forEach( ( fileName ) => {
			const fullPath = path.join( scanDir, fileName );
			if (
				! fs.lstatSync( fullPath ).isDirectory() &&
				! fileName.startsWith( '_' ) &&
				! fileName.startsWith( '.' )
			) {
				entries[ `${ prefix }${ fileName.replace( /\.[^/.]+$/, '' ) }` ] = fullPath;
			}
		} );
	}

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

// CSS / SCSS entry points from src/css/{frontend,admin,editor}/.
const styles = {
	...sharedConfig,
	entry: () => readAllFileEntries( './src/css' ),
	module: {
		...sharedConfig.module,
	},
	plugins: [
		...sharedConfig.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin',
		),
	],
};

// Standard JS entry points from src/js/{frontend,admin,editor}/.
const scripts = {
	...sharedConfig,
	entry: () => readAllFileEntries( './src/js' ),
	plugins: [
		...sharedConfig.plugins,
		new CopyWebpackPlugin( {
			patterns: [
				{
					from: path.resolve( process.cwd(), 'src', 'fonts' ),
					to: path.resolve( process.cwd(), 'assets', 'build', 'fonts' ),
					noErrorOnMissing: true,
				},
				{
					from: path.resolve( process.cwd(), 'src', 'images', 'svg' ),
					to: path.resolve( process.cwd(), 'assets', 'build', 'images', 'svg' ),
					noErrorOnMissing: true,
					transform: {
						transformer( content ) {
							return svgoOptimize( content.toString() ).data;
						},
					},
				},
			],
		} ),
	],
};

// Interactivity API module entry points from src/js/frontend/modules/.
const moduleScripts = {
	...moduleConfig,
	entry: () => readAllFileEntries( './src/js/frontend/modules' ),
	output: {
		...moduleConfig.output,
		path: path.resolve( process.cwd(), 'assets', 'build', 'js', 'modules' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
};

module.exports = [ scripts, styles, moduleScripts ];
