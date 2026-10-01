/**
 * Wrapper config for the `start:blocks` invocation.
 *
 * Layered on top of wp-scripts' default config to fix the dev-server issue that
 * bites during `wp-scripts start --hot` for blocks in this stack:
 *
 * webpack-dev-server 5 and later (forced at the top level by package.json
 * overrides for security fixes, starting with CVE-2025-30359 / CVE-2025-30360)
 * is what `webpack serve` loads, but `@wordpress/scripts`
 * still defines `devServer.proxy` in the webpack-dev-server v4 object form, so
 * their schema rejects it with "options.proxy should be an array". The bundled
 * `/build` rewrite has no `target`, isn't needed for block HMR, and would fail
 * at runtime even if converted to the array form, so we remove it.
 *
 * All dev-server knobs are read from `.env.local` so they can change without
 * editing this file.
 */

/**
 * External dependencies
 */
/*
 * `quiet: true` suppresses dotenv's per-run "injecting env" banner, which is
 * noisy on every rebuild in watch mode.
 */
require('dotenv').config({ path: '.env.local', quiet: true });

/**
 * WordPress dependencies
 */
const config = require('@wordpress/scripts/config/webpack.config');

const DEFAULT_DEV_SERVER_PORT = 8887;

/**
 * Parse a TCP port from an env value, falling back when it is missing or not a
 * valid port number (1–65535).
 *
 * @param {string|undefined} value    Raw env value.
 * @param {number}           fallback Port to use when `value` is invalid.
 * @return {number} A valid port.
 */
const toPort = (value, fallback) => {
	// Digits only: parseInt would read '8888foo' as 8888, and Number would accept hex.
	const port = /^\d+$/.test(String(value ?? '')) ? Number(value) : NaN;
	return Number.isInteger(port) && port >= 1 && port <= 65535
		? port
		: fallback;
};

const devServerPort = toPort(
	process.env.BLOCKS_DEV_SERVER_PORT,
	DEFAULT_DEV_SERVER_PORT
);

/**
 * Apply the dev-server fixes to a single webpack config.
 *
 * @param {Object} singleConfig A webpack configuration object.
 * @return {Object} The same config, compatible with webpack-dev-server 5 and later.
 */
const fixDevServer = (singleConfig) => {
	/*
	 * Only the script config carries a devServer; the module config sets it to
	 * `false` (or omits it) and needs no changes.
	 */
	if (!singleConfig || !singleConfig.devServer) {
		return singleConfig;
	}

	/*
	 * webpack-dev-server 5 and later require `proxy` to be an array; wp-scripts ships a
	 * v4-style object. Not needed for block HMR, so drop it.
	 */
	delete singleConfig.devServer.proxy;

	/*
	 * Multiple sites can run the dev server at once, so the port is read from
	 * .env.local (BLOCKS_DEV_SERVER_PORT) rather than hardcoded.
	 */
	singleConfig.devServer.port = devServerPort;

	/*
	 * Restrict host checks to localhost (and the configured local host) instead
	 * of the blanket `--allowed-hosts all`, to avoid DNS-rebinding exposure if
	 * the port is reachable on the network.
	 */
	singleConfig.devServer.allowedHosts = [
		'localhost',
		...(process.env.WP_HOST ? [process.env.WP_HOST] : []),
	];

	return singleConfig;
};

/*
 * `--experimental-modules` makes the stock config export an array of configs
 * ([ scriptConfig, moduleConfig ]); otherwise it's a single object.
 */
module.exports = Array.isArray(config)
	? config.map(fixDevServer)
	: fixDevServer(config);
