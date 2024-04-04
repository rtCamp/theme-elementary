#!/usr/bin/env node

/**
 * External dependencies
 */
const { join } = require( 'node:path' );
const { argv } = require( 'node:process' );
const { spawn } = require( 'node:child_process' );
const { accessSync, constants } = require( 'node:fs' );

const args = argv.slice( 2 );
const scriptPath = join( __dirname, '..', 'vendor', 'bin', 'phpcbf' );

try {
	accessSync( scriptPath, constants.F_OK );
} catch (e) {
	// eslint-disable-next-line no-console
	console.error(
		'\x1b[31m%s\x1b[0m',
		'Error: vendor/bin/phpcbf is not found or not executable. Please run `composer install` first.'
	);
	process.exit( 1 );
}

const phpcbfProcess = spawn( scriptPath, args );

phpcbfProcess.stdout.on( 'data', ( data ) => {
	process.stdout.write( data );
});

phpcbfProcess.stderr.on( 'data', ( data ) => {
	process.stderr.write( data );
});

process.on( 'SIGINT', () => {
	phpcbfProcess.kill();
});

process.on( 'SIGTERM', () => {
	phpcbfProcess.kill();
});

phpcbfProcess.on( 'exit', ( code ) => {
	process.exit( 1 === code	 ? 0 : code );
});
