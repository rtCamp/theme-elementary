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

/**
 * Internal dependencies
 */
const config = require( './scaffold.config' );

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
	.then( () => process.exit( process.exitCode || 0 ) )
	.catch( ( err ) => {
		console.error( err );
		process.exit( 1 );
	} );
