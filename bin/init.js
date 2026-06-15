#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * Theme setup -- thin wrapper.
 *
 * Delegates to the shared scaffold engine shipped by rtcamp/wp-framework,
 * passing this theme's bin/scaffold.config.js. The engine renders all prompts
 * via the wp-tooling UI kit.
 *
 * Requires `composer install` (for the engine) and `npm install` (for the
 * wp-tooling UI kit) to have run. Invoke with `npm run init`.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );

/**
 * Internal dependencies
 */
const config = require( './scaffold.config' );

const root = path.resolve( __dirname, '..' );
const enginePath = path.join( root, 'vendor', 'rtcamp', 'wp-framework', 'bin', 'scaffold.js' );

if ( ! fs.existsSync( enginePath ) ) {
	console.error( '\nScaffold engine not found at vendor/rtcamp/wp-framework.' );
	console.error( 'Run `composer install` first, then `npm run init`.\n' );
	process.exit( 1 );
}

let run;
try {
	( { run } = require( enginePath ) );
} catch ( err ) {
	console.error( '\nCould not load the scaffold engine.' );
	console.error( 'Ensure dependencies are installed (`composer install` && `npm install`).\n' );
	console.error( err.message );
	process.exit( 1 );
}

run( config, { root } )
	.then( () => process.exit( process.exitCode || 0 ) )
	.catch( ( err ) => {
		console.error( err );
		process.exit( 1 );
	} );
