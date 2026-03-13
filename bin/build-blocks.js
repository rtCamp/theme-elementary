#!/usr/bin/env node

/**
 * Build blocks script.
 *
 * Runs wp-scripts build for blocks only when block source directories exist
 * inside assets/src/blocks/. Exits silently with success if no blocks are
 * found, preventing an empty assets/build/blocks/ directory from being created.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

const blocksSourceDir = path.join( process.cwd(), 'assets', 'src', 'blocks' );

/**
 * Check if the source blocks directory contains at least one buildable block.
 * Directories prefixed with '_' are intentionally excluded and do not count.
 *
 * @return {boolean}
 */
const hasSourceBlocks = () => {
	if ( ! fs.existsSync( blocksSourceDir ) ) {
		return false;
	}

	const entries = fs.readdirSync( blocksSourceDir, { withFileTypes: true } );

	return entries
		.filter( ( entry ) => entry.isDirectory() )
		.some( ( dir ) => ! dir.name.startsWith( '_' ) );
};

if ( ! hasSourceBlocks() ) {
	console.log( 'No block sources found. Skipping block build.' );
	process.exit( 0 );
}

const result = spawnSync(
	'npx',
	[
		'wp-scripts',
		'build',
		'--config',
		'./node_modules/@wordpress/scripts/config/webpack.config.js',
		'--webpack-src-dir=./assets/src/blocks/',
		'--output-path=./assets/build/blocks/',
	],
	{ stdio: 'inherit', cwd: process.cwd() }
);

if ( result.error ) {
	throw result.error;
}

process.exit( result.status ?? 0 );
