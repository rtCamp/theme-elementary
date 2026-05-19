/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const webpack = require( 'webpack' );
const rtlcss = require( 'rtlcss' );
const { optimize: svgoOptimize } = require( 'svgo' );

const isHot = process.argv.includes( '--hot' );
const isWatch =
	process.argv.includes( '--watch' ) || process.argv.includes( 'watch' ) || isHot;

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

const rootPath = ( ...parts ) => path.resolve( process.cwd(), ...parts );
const getPluginName = ( plugin ) => plugin.constructor.name;
const isPlugin = ( pluginName ) => ( plugin ) =>
	getPluginName( plugin ) === pluginName;
const isNotPlugin = ( pluginName ) => ( plugin ) =>
	getPluginName( plugin ) !== pluginName;
const isNotOneOfPlugins = ( pluginNames ) => ( plugin ) =>
	! pluginNames.includes( getPluginName( plugin ) );

/**
 * Context subdirectories scanned for entry points.
 *
 * @type {string[]}
 */
const CONTEXT_DIRS = [ 'frontend', 'admin', 'editor' ];
const ASSETS_BUILD_DIR = rootPath( 'assets', 'build' );
const JS_BUILD_DIR = rootPath( 'assets', 'build', 'js' );
const CSS_FILENAME = '../css/[name].css';
const FRONTEND_AND_ADMIN_DIRS = [ 'frontend', 'admin' ];
const EDITOR_DIRS = [ 'editor' ];
const MODULES_DIR = 'modules';
const STYLE_ONLY_IGNORED_PLUGINS = [
	'DependencyExtractionWebpackPlugin',
	'RtlCssPlugin',
];
const BROWSER_SYNC_FILES = [
	'assets/build/css/**/*.css',
	'assets/build/js/frontend/**/*.js',
	'assets/build/js/modules/**/*.js',
	'**/*.php',
	'!vendor/**',
	'!assets/build/**/*.php',
	'**/*.html',
	'!assets/build/**/*.map',
	'!assets/build/**/*.hot-update.*',
];

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
 * @param {string}   dir                 Base directory to scan.
 * @param {Object}   options             Options.
 * @param {string[]} options.excludeDirs Directory names to skip during recursion.
 * @param {string[]} options.contextDirs Context directory names to scan.
 * @return {Object} Object mapping entry names to file paths.
 */
const readAllFileEntries = (
	dir,
	{ contextDirs = CONTEXT_DIRS, excludeDirs = [] } = {},
) => {
	const entries = {};

	if ( ! fs.existsSync( dir ) ) {
		return entries;
	}

	const resolvedDir = path.resolve( dir );

	const contextPaths = contextDirs
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

class CleanBuildPlugin {
	static cleaned = false;

	apply( compiler ) {
		const clean = () => {
			if ( CleanBuildPlugin.cleaned ) {
				return;
			}

			CleanBuildPlugin.cleaned = true;
			fs.rmSync( ASSETS_BUILD_DIR, {
				force: true,
				recursive: true,
			} );
		};

		compiler.hooks.beforeRun.tap( 'CleanBuildPlugin', clean );
		compiler.hooks.watchRun.tap( 'CleanBuildPlugin', clean );
	}
}

class CssAssetRtlPlugin {
	apply( compiler ) {
		compiler.hooks.compilation.tap( 'CssAssetRtlPlugin', ( compilation ) => {
			compilation.hooks.processAssets.tap(
				{
					name: 'CssAssetRtlPlugin',
					stage: compilation.PROCESS_ASSETS_STAGE_OPTIMIZE,
				},
				() => {
					for ( const filename of Object.keys( compilation.assets ) ) {
						if (
							path.extname( filename ) !== '.css' ||
							filename.endsWith( '-rtl.css' )
						) {
							continue;
						}

						const rtlFilename = filename.replace(
							/\.css$/,
							'-rtl.css',
						);

						if ( compilation.assets[ rtlFilename ] ) {
							continue;
						}

						compilation.assets[ rtlFilename ] =
							new webpack.sources.RawSource(
								rtlcss.process(
									compilation.assets[ filename ].source(),
								),
							);
					}
				},
			);
		} );
	}
}

const setCssOutputPath = ( plugin ) => {
	if ( isPlugin( 'MiniCssExtractPlugin' )( plugin ) ) {
		plugin.options.filename = CSS_FILENAME;
	}

	return plugin;
};

const withoutFastRefresh = ( config ) => ( {
	...config,
	devServer: false,
	optimization: {
		...config.optimization,
		runtimeChunk: false,
	},
	plugins: config.plugins.filter( isNotPlugin( 'ReactRefreshPlugin' ) ),
} );

const getCopyPlugin = () =>
	new CopyWebpackPlugin( {
		patterns: [
			{
				from: rootPath( 'src', 'fonts' ),
				to: rootPath( 'assets', 'build', 'fonts' ),
				noErrorOnMissing: true,
				globOptions: { ignore: [ '**/.*' ] },
			},
			{
				from: rootPath( 'src', 'images', 'svg' ),
				to: rootPath( 'assets', 'build', 'images', 'svg' ),
				noErrorOnMissing: true,
				filter: ( resourcePath ) =>
					path.extname( resourcePath ).toLowerCase() === '.svg',
				transform: {
					transformer( content, absoluteFrom ) {
						try {
							const result = svgoOptimize( content.toString() );

							if (
								typeof result?.data === 'string' &&
								result.data.length > 0
							) {
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
	} );

const getBrowserSyncPlugins = () => {
	if ( ! isWatch ) {
		return [];
	}

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
				files: BROWSER_SYNC_FILES,
				notify: false,
				open: false,
				logSnippet: false,
			},
			{
				injectCss: true,
			},
		),
	];
};

// Extend the default config.
const sharedConfig = {
	...scriptConfig,
	output: {
		path: JS_BUILD_DIR,
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	// WDS v5 requires proxy as an array; since writeToDisk: true is set we don't need it.
	// allowedHosts: 'all' is needed for custom local domains (e.g. elementary.local).
	devServer: scriptConfig.devServer
		? { ...scriptConfig.devServer, proxy: undefined, allowedHosts: 'all' }
		: undefined,
	plugins: [
		new CleanBuildPlugin(),
		...scriptConfig.plugins.map( setCssOutputPath ),
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

const sharedNonHotConfig = withoutFastRefresh( sharedConfig );

// CSS / SCSS entry points from src/css/{frontend,admin,editor}/.
const styles = {
	...sharedNonHotConfig,
	entry: () => readAllFileEntries( './src/css' ),
	module: {
		...sharedNonHotConfig.module,
	},
	plugins: [
		...sharedNonHotConfig.plugins.filter(
			isNotOneOfPlugins( STYLE_ONLY_IGNORED_PLUGINS ),
		),
		new CssAssetRtlPlugin(),
	],
};

// Standard JS entry points from src/js/{frontend,admin,editor}/.
const scripts = {
	...sharedNonHotConfig,
	entry: () =>
		readAllFileEntries( './src/js', {
			contextDirs: FRONTEND_AND_ADMIN_DIRS,
			excludeDirs: [ MODULES_DIR ],
		} ),
	plugins: [
		...sharedNonHotConfig.plugins.filter( isNotPlugin( 'RtlCssPlugin' ) ),
		getCopyPlugin(),
		new CssAssetRtlPlugin(),
		...getBrowserSyncPlugins(),
	],
};

// Editor JS entry points keep webpack-dev-server HMR/Fast Refresh.
const editorScripts = {
	...sharedConfig,
	entry: () =>
		readAllFileEntries( './src/js', {
			contextDirs: EDITOR_DIRS,
			excludeDirs: [ MODULES_DIR ],
		} ),
	plugins: [
		...sharedConfig.plugins.filter( isNotPlugin( 'RtlCssPlugin' ) ),
		new CssAssetRtlPlugin(),
	],
};

// Interactivity API module entry points from src/js/frontend/modules/.
const moduleScripts = {
	...moduleConfig,
	devServer: false,
	entry: () => readAllFileEntries( './src/js/frontend/modules' ),
	output: {
		...moduleConfig.output,
		path: rootPath( 'assets', 'build', 'js', 'modules' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
};

module.exports = [ scripts, editorScripts, styles, moduleScripts ];
