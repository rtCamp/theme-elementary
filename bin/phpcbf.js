#!/usr/bin/env node

/* eslint no-console: 0 */

const path = require( 'path' );
const fs = require( 'fs' );
const { execSync } = require( 'child_process' );
const { getRoot, info } = require( './util' );


// Path to the @root/vendor/bin/phpcbf script.
const pathToPhpcbf = path.resolve( getRoot(), 'vendor/bin/phpcbf' );

if ( ! fs.existsSync( pathToPhpcbf ) ) {
	console.log( info.error( 'phpcbf not found. Please run `composer install`.' ) );
	process.exit( 1 );
}

// Run the phpcbf script.
const command = `'${ pathToPhpcbf }' ${ process.argv.slice( 2 ).join( ' ' ) }`;
try {
	const phpcbfCommand = execSync( command );
	console.log( phpcbfCommand.toString() );

} catch ( error ) {
	console.log( error.stdout.toString() );
	process.exit( 1 );
}
