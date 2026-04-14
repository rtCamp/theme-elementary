const path = require('path');

const DEV_SERVER_HOST = 'localhost';
const DEV_SERVER_PORT = 3000;
const DEV_SERVER_URL = `http://${DEV_SERVER_HOST}:${DEV_SERVER_PORT}`;
//const PROXY_TARGET = 'ADD_LOCAL_DOMAIN_HERE'; // e.g., http://testing.local
const BUILD_PATH = '/assets/build/js/';
const WS_PATH = '/ws';

module.exports = {
	mode: 'development',
	devtool: 'eval-source-map',

	entry: {
		'main-hmr': path.resolve(__dirname, 'assets/src/js/main-hmr.js'),
	},

	output: {
		filename: '[name].js',
		publicPath: `${DEV_SERVER_URL}${BUILD_PATH}`,
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: 'babel-loader',
			},
			{
				test: /\.s?css$/,
				use: [
					'style-loader',
					'css-loader',
					'postcss-loader',
					'sass-loader',
				],
			},
		],
	},

	devServer: {
		host: DEV_SERVER_HOST,
		port: DEV_SERVER_PORT,
		hot: true,
		liveReload: false,

		headers: {
			'Access-Control-Allow-Origin': '*',
		},

		allowedHosts: 'all',

		client: {
			webSocketURL: `ws://${DEV_SERVER_HOST}:${DEV_SERVER_PORT}${WS_PATH}`,
			overlay: true,
		},

		devMiddleware: {
			publicPath: BUILD_PATH,
		},

		proxy: [
			{
				context: (pathname) => {
					if (pathname.startsWith(BUILD_PATH)) return false;
					if (pathname.startsWith(WS_PATH)) return false;
					return true;
				},
				target: PROXY_TARGET,
				changeOrigin: true,
			},
		],
	},
};
