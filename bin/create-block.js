#!/usr/bin/env node

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

/**
 * Default arguments passed to @wordpress/create-block.
 * Stored as a Map to make insertion order and intent explicit.
 */
const DEFAULT_ARGS = new Map( [
	[ '--variant', 'static' ],
	[ '--namespace', 'elementary-theme' ],
] );

/**
 * Validate block slug.
 * Must start and end with a lowercase letter or number.
 * May contain hyphens in between, but not at the start or end.
 *
 * @param {string} blockName Block slug.
 * @return {boolean}
 */
const isValidBlockName = ( blockName ) => {
	return /^[a-z0-9][a-z0-9-]*[a-z0-9]$/.test( blockName );
};

/**
 * Print usage instructions to stderr.
 *
 * @return {void}
 */
const printUsage = () => {
	console.error( 'Usage: node create-block.js <block-name> [options]' );
	console.error( 'Example: node create-block.js my-block --variant=dynamic' );
};

/**
 * Parse CLI arguments, merging user-provided args with defaults.
 * User-provided values always take precedence over defaults.
 *
 * Handles both flag-style args (--no-plugin) and key=value args (--variant=static).
 *
 * @param {Array} args CLI args passed by the user.
 * @return {Array} Merged array of arguments.
 */
const parseArgs = ( args ) => {
	const parsedArgs = [];
	const seenKeys = new Set();

	// Add user-provided args, tracking which keys have been supplied.
	args.forEach( ( arg ) => {
		// Extract the key portion regardless of whether arg has a value or not.
		const key = arg.startsWith( '--' ) ? arg.split( '=' )[ 0 ] : arg;
		seenKeys.add( key );
		parsedArgs.push( arg );
	} );

	// Add defaults only for keys the user did not supply.
	DEFAULT_ARGS.forEach( ( value, key ) => {
		if ( ! seenKeys.has( key ) ) {
			parsedArgs.push( `${ key }=${ value }` );
		}
	} );

	return parsedArgs;
};

/**
 * Run @wordpress/create-block CLI and verify the output directory was created.
 *
 * --target-dir only accepts a path relative to cwd, not an absolute path.
 * We set cwd to the project root and pass a relative target-dir so
 * create-block scaffolds files in the correct location.
 *
 * @param {string} blockName Block slug.
 * @param {Array}  args      Merged CLI args.
 * @return {void}
 * @throws {Error} If the block directory already exists, the command fails,
 *                 or the block directory is not created after the run.
 */
const createBlock = ( blockName, args ) => {
	const projectRoot = process.cwd();

	// Relative path from project root — required by --target-dir.
	const relativeBlockDir = path.join( 'assets', 'src', 'blocks', blockName );

	// Absolute path used for pre-run existence check and post-run verification.
	const absoluteBlockDir = path.join( projectRoot, relativeBlockDir );

	// Guard against silently overwriting an existing block.
	if ( fs.existsSync( absoluteBlockDir ) ) {
		throw new Error(
			`Block "${ blockName }" already exists at: ${ absoluteBlockDir }`
		);
	}

	const cliArgs = [
		'@wordpress/create-block',
		blockName,
		`--target-dir=${ relativeBlockDir }`,
		'--no-plugin',
		...args,
	];

	console.log( `Creating block "${ blockName }"...` );

	// spawnSync passes args as an array — no shell interpolation, no injection risk.
	// cwd ensures create-block resolves --target-dir relative to the project root.
	const result = spawnSync( 'npx', cliArgs, {
		stdio: 'inherit',
		cwd: projectRoot,
	} );

	if ( result.error ) {
		throw result.error;
	}

	if ( result.status !== 0 ) {
		// result.status is null when the process was killed by a signal.
		const exitInfo =
			result.status !== null
				? `status ${ result.status }`
				: `signal ${ result.signal }`;
		throw new Error( `create-block exited with ${ exitInfo }.` );
	}

	if ( ! fs.existsSync( absoluteBlockDir ) ) {
		throw new Error(
			`Block directory was not created at: ${ absoluteBlockDir }`
		);
	}

	console.log( `Block created successfully at ${ absoluteBlockDir }` );
};

/**
 * Entry point.
 *
 * @return {void}
 */
const init = () => {
	try {
		const args = process.argv.slice( 2 );
		const blockName = args.shift();

		if ( ! blockName ) {
			console.error( 'Error: Block name is required.' );
			printUsage();
			process.exit( 1 );
		}

		if ( ! isValidBlockName( blockName ) ) {
			console.error(
				'Error: Block name must start and end with a lowercase letter or number, ' +
				'and may only contain lowercase letters, numbers, and hyphens.'
			);
			printUsage();
			process.exit( 1 );
		}

		const parsedArgs = parseArgs( args );

		createBlock( blockName, parsedArgs );
	} catch ( error ) {
		console.error( 'Error creating block:', error.message );
		process.exit( 1 );
	}
};

init();
