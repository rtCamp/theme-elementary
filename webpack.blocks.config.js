/**
 * Wrapper config for the `start:blocks` invocation.
 *
 * Layered on top of wp-scripts' default config to fix the dev-server issue that
 * bites during `wp-scripts start --hot` for blocks in this stack:
 *
 * webpack-dev-server v5 (resolved at the top level for the CVE-2025-30359 /
 * CVE-2025-30360 fix) is what `webpack serve` loads, but `@wordpress/scripts`
 * still defines `devServer.proxy` in the webpack-dev-server v4 object form, so
 * v5's schema rejects it with "options.proxy should be an array". The bundled
 * `/build` rewrite has no `target`, isn't needed for block HMR, and would fail
 * at runtime even if converted to the v5 array form, so we remove it.
 *
 * All dev-server knobs are read from `.env.local` so they can change without
 * editing this file.
 */

/**
 * External dependencies
 */
require( 'dotenv' ).config( { path: '.env.local' } );

/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/webpack.config' );

const DEFAULT_DEV_SERVER_PORT = 8887;
const devServerPort =
	parseInt( process.env.BLOCKS_DEV_SERVER_PORT, 10 ) ||
	DEFAULT_DEV_SERVER_PORT;

/**
 * Apply the dev-server fixes to a single webpack config.
 *
 * @param {Object} singleConfig A webpack configuration object.
 * @return {Object} The same config, dev-server v5 compatible.
 */
const fixDevServer = ( singleConfig ) => {
	// Only the script config carries a devServer; the module config sets it to
	// `false` (or omits it) and needs no changes.
	if ( ! singleConfig || ! singleConfig.devServer ) {
		return singleConfig;
	}

	// webpack-dev-server v5 requires `proxy` to be an array; wp-scripts ships a
	// v4-style object. Not needed for block HMR, so drop it.
	delete singleConfig.devServer.proxy;

	// Multiple sites can run the dev server at once, so the port is read from
	// .env.local (BLOCKS_DEV_SERVER_PORT) rather than hardcoded.
	singleConfig.devServer.port = devServerPort;

	return singleConfig;
};

// `--experimental-modules` makes the stock config export an array of configs
// ([ scriptConfig, moduleConfig ]); otherwise it's a single object.
module.exports = Array.isArray( config )
	? config.map( fixDevServer )
	: fixDevServer( config );
