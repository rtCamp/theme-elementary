#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * Theme setup; thin wrapper that delegates to the shared init engine in
 * @rtcamp/wp-tooling, passing this theme's bin/scaffold.config.js.
 *
 * Requires `npm install`. Invoke with `npm run init`.
 */

/**
 * External dependencies
 */
const path = require( 'path' );
const { execFileSync } = require( 'child_process' );

/**
 * Internal dependencies
 */
const config = require( './scaffold.config' );

const argv = process.argv.slice( 2 );

if ( argv.includes( '--help' ) || argv.includes( '-h' ) ) {
	console.log(
		`Usage: npm run init -- [options]   (or: node bin/init.js [options])

Set up or manage this theme via the shared @rtcamp/wp-tooling init engine.
Runs npm run sync-ai automatically on success.

Options:
  --name="Theme Name"       Theme name (required with --yes).
  --version=X.Y.Z           Initial version (default 1.0.0).
  --yes                     Non-interactive; requires --name.
  --keep-examples           Keep every example set.
  --remove-examples[=a,b]   Remove all (no value) or the listed example keys.
  --features=a,b            Set the exact enabled feature set (e.g. hmr,tailwind).
  --enable=a / --disable=b  Toggle a single feature.
  --list                    Show example set / feature status, then exit.
  --reinit / --clean        Re-run / reset helpers.
  --help, -h                Show this help.`
	);
	process.exit( 0 );
}

let run;
try {
	( { run } = require( '@rtcamp/wp-tooling/init' ) );
} catch ( err ) {
	console.error( '\nCould not load the init engine from @rtcamp/wp-tooling.' );
	console.error( 'Ensure dependencies are installed (`npm install`).\n' );
	console.error( err.message );
	process.exit( 1 );
}

run( config, { root: path.resolve( __dirname, '..' ) } )
	.then( () => {
		// Keep the shared AI instruction files in sync after a successful init.
		// Done here (not in the npm script) so `npm run init -- <flags>` forwards
		// the flags to this script instead of to a chained `sync-ai`.
		try {
			execFileSync( process.execPath, [ path.join( __dirname, 'sync-ai.js' ) ], { stdio: 'inherit' } );
		} catch ( err ) {
			console.error( '\ninit succeeded but `sync-ai` failed; run `npm run sync-ai` manually.' );
			console.error( err.message );
		}
		process.exit( process.exitCode || 0 );
	} )
	.catch( ( err ) => {
		console.error( err );
		process.exit( 1 );
	} );
