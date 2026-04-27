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
 * Recursively scans each context subdirectory (frontend/, admin/, editor/)
 * inside the given directory, collecting files whose names do not start with
 * `_` or `.`. If none of the context subdirectories exist, the directory
 * itself is scanned recursively instead.
 *
 * If two files resolve to the same entry key, the first file is kept and a
 * warning is emitted.
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

	const addEntry = ( entryName, fullPath ) => {
		if ( entries[ entryName ] ) {
			// Keep first discovery stable, but surface collisions for debugging.
			console.warn(
				`Duplicate webpack entry "${ entryName }" ignored: ${ fullPath } (keeping ${ entries[ entryName ] })`,
			);
			return;
		}

		entries[ entryName ] = fullPath;
	};

	const scanDirectory = ( scanRoot, currentDir, entryPrefix = '' ) => {
		fs.readdirSync( currentDir, { withFileTypes: true } ).forEach( ( entry ) => {
			if ( entry.name.startsWith( '_' ) || entry.name.startsWith( '.' ) ) {
				return;
			}

			const fullPath = path.join( currentDir, entry.name );

			if ( entry.isDirectory() ) {
				scanDirectory( scanRoot, fullPath, entryPrefix );
				return;
			}

			const relativePath = path
				.relative( scanRoot, fullPath )
				.replace( /\.[^/.]+$/, '' )
				.split( path.sep )
				.join( '/' );

			addEntry( `${ entryPrefix }${ relativePath }`, fullPath );
		} );
	};

	for ( const scanDir of dirsToScan ) {
		const prefix = useNamespace ? `${ path.basename( scanDir ) }/` : '';
		scanDirectory( scanDir, scanDir, prefix );
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
