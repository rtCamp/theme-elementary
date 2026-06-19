#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * Thin wrapper around the framework's sync tool.
 *
 * Runs vendor/rtcamp/wp-framework/bin/sync-ai-instructions.js if the framework
 * is installed, otherwise skips cleanly (for example before `composer install`
 * has fetched it). Forwards any arguments, so `npm run sync-ai -- --check` works.
 */

const fs = require( 'fs' );
const { spawnSync } = require( 'child_process' );

const script = 'vendor/rtcamp/wp-framework/bin/sync-ai-instructions.js';

if ( ! fs.existsSync( script ) ) {
	console.log( 'sync-ai: rtcamp/wp-framework is not installed yet; run `composer install` first. Skipping.' );
	process.exit( 0 );
}

process.exit( spawnSync( 'node', [ script, ...process.argv.slice( 2 ) ], { stdio: 'inherit' } ).status ?? 0 );
