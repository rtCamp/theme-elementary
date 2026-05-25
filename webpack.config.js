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
 * @param {string}   dir                  Base directory to scan.
 * @param {Object}   options              Options.
 * @param {string[]} options.excludeDirs  Directory names to skip during recursion.
 * @return {Object} Object mapping entry names to file paths.
 */
const readAllFileEntries = ( dir, { excludeDirs = [] } = {} ) => {
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
				if ( excludeDirs.includes( entry.name ) ) {
					return;
				}
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

/**
 * Read component file entries from a components directory.
 *
 * @param {string} dir Base directory to scan (e.g., './src/components').
 * @param {RegExp} extFilter Regex to match file extensions.
 * @return {Object} Object mapping entry names to file paths.
 */
const getComponentEntries = ( dir, extFilter ) => {
	const entries = {};
	if ( ! fs.existsSync( dir ) ) {
		return entries;
	}
	const resolvedDir = path.resolve( dir );
	fs.readdirSync( resolvedDir, { withFileTypes: true } ).forEach( ( entry ) => {
		if ( entry.isDirectory() && ! entry.name.startsWith( '_' ) && ! entry.name.startsWith( '.' ) ) {
			const compName = entry.name;
			const compDir = path.join( resolvedDir, compName );
			fs.readdirSync( compDir ).forEach( ( file ) => {
				if ( file.match( extFilter ) && path.parse( file ).name === compName ) {
					const entryName = `components/${ compName.toLowerCase() }`;
					entries[ entryName ] = path.join( compDir, file );
				}
			} );
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

// CSS / SCSS entry points from src/css/{frontend,admin,editor}/.
const styles = {
	...sharedConfig,
	entry: () => ( {
		...readAllFileEntries( './src/css' ),
		...getComponentEntries( './src/components', /\.(sc|sa|c)ss$/ ),
	} ),
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
	entry: () => ( {
		...readAllFileEntries( './src/js', { excludeDirs: [ 'modules' ] } ),
		...getComponentEntries( './src/components', /\.js$/ ),
	} ),
	plugins: [
		...sharedConfig.plugins,
		new CopyWebpackPlugin( {
			patterns: [
				{
					from: path.resolve( process.cwd(), 'src', 'fonts' ),
					to: path.resolve( process.cwd(), 'assets', 'build', 'fonts' ),
					noErrorOnMissing: true,
					globOptions: { ignore: [ '**/.*' ] },
				},
				{
					from: path.resolve( process.cwd(), 'src', 'images', 'svg' ),
					to: path.resolve( process.cwd(), 'assets', 'build', 'images', 'svg' ),
					noErrorOnMissing: true,
					filter: ( resourcePath ) =>
						path.extname( resourcePath ).toLowerCase() === '.svg',
					transform: {
						transformer( content, absoluteFrom ) {
							try {
								const result = svgoOptimize( content.toString() );

								if ( typeof result?.data === 'string' && result.data.length > 0 ) {
									return result.data;
								}

								console.warn(
									`SVGO produced no optimized output for ${ absoluteFrom }. Copying original content instead.`,
								);
								return content;
							} catch ( error ) {
								console.warn(
									`SVGO failed for ${ absoluteFrom }: ${ error.message }. Copying original content instead.`,
								);
								return content;
							}
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

const configs = [ scripts, styles, moduleScripts ];

Object.defineProperty( configs, 'getComponentEntries', {
	value: getComponentEntries,
} );

module.exports = configs;
