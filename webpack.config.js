/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const WebpackWatchedGlobEntries = require( 'webpack-watched-glob-entries-plugin' );

/**
 * WordPress dependencies
 */
const [scriptConfig, moduleConfig] = require('@wordpress/scripts/config/webpack.config');

/**
 * Read all file entries in a directory.
 */
const readAllFileEntries = (dir) => {
	const entries = {};

	if (!fs.existsSync(dir)) {
		return entries;
	}

	fs.readdirSync(dir).forEach((fileName) => {
		const fullPath = `${dir}/${fileName}`;

		if (!fs.lstatSync(fullPath).isDirectory() && !fileName.startsWith('_')) {
			entries[fileName.replace(/\.[^/.]+$/, '')] = fullPath;
		}
	});

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

// CSS build
const styles = {
	...sharedConfig,
	entry: WebpackWatchedGlobEntries.getEntries(
		[
			path.resolve( __dirname, `assets/src/css/*.scss` ),
		],
		{
			ignore: [
				path.resolve( __dirname, `assets/src/css/**/_*.scss` ),
			],
		},
	),
	output: {
		...sharedConfig.output,
		path: path.resolve( process.cwd(), 'assets', 'build', 'css' ),
	},
	plugins: [
		...sharedConfig.plugins.filter(
			( plugin ) => {
				return plugin.constructor.name !== 'DependencyExtractionWebpackPlugin' && plugin.constructor.name !== 'CopyPlugin';
			},
		),
	],
};

// JS build
const scripts = {
	...sharedConfig,
	entry: {
		...sharedConfig.entry(),
		...WebpackWatchedGlobEntries.getEntries(
			[
				path.resolve( __dirname, `assets/src/js/*.js` ),
			],
			{
				ignore: [
					path.resolve( __dirname, `assets/src/js/_*.js` ),
				],
			},
		)(),
	},
};

// module scripts.
const moduleScripts = {
	...moduleConfig,
	entry: () => readAllFileEntries('./assets/src/js/modules'),
	output: {
		...moduleConfig.output,
		path: path.resolve(process.cwd(), 'assets', 'build', 'js', 'modules'),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
};

module.exports = [ scripts, styles, moduleScripts ];
