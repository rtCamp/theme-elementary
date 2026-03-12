#!/usr/bin/env node

/**
 * Build blocks manifest script.
 *
 * Runs wp-scripts build-blocks-manifest only when compiled block directories
 * exist inside assets/build/blocks/. Exits silently with success if no blocks
 * are found, preventing build:prod from failing on a fresh clone.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

const blocksBuildDir = path.join( process.cwd(), 'assets', 'build', 'blocks' );

/**
 * Check if the build blocks directory contains at least one compiled block.json.
 *
 * @return {boolean}
 */
const hasCompiledBlocks = () => {
	if ( ! fs.existsSync( blocksBuildDir ) ) {
		return false;
	}

	const entries = fs.readdirSync( blocksBuildDir, { withFileTypes: true } );

	return entries
		.filter( ( entry ) => entry.isDirectory() )
		.some( ( dir ) =>
			fs.existsSync( path.join( blocksBuildDir, dir.name, 'block.json' ) )
		);
};

if ( ! hasCompiledBlocks() ) {
	console.log( 'No compiled blocks found. Skipping manifest generation.' );
	process.exit( 0 );
}

const result = spawnSync(
	'npx',
	[
		'wp-scripts',
		'build-blocks-manifest',
		`--input=${ blocksBuildDir }`,
		`--output=${ path.join( blocksBuildDir, 'blocks-manifest.php' ) }`,
	],
	{ stdio: 'inherit', cwd: process.cwd() }
);

if ( result.error ) {
	throw result.error;
}

process.exit( result.status ?? 0 );
