#!/usr/bin/env node

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { execSync } = require( 'child_process' );

/**
 * Parse user inputed arguments.
 * @param {Array} args User inputed arguments
 * @return {string} Parsed arguments
 */
const parseUserInputedArgs = ( args ) => {
	const expectedArgs = {
		'--title': '',
		'--variant': 'static',
		'--namespace': '',
		'--category': '',
		'--icon': '',
		'--keywords': '',
		'--short-description': '',
	};

	let parsedArgs = '';

	args.forEach( ( arg ) => {
		const [ key, value ] = arg.split( '=' );
		if ( key in expectedArgs ) {
			parsedArgs += `${ key }=${ value } `;
		}
	} );

	return parsedArgs;
};

/**
 * Initialize the script.
 * @return {void}
 * @throws {Error} If an error occurs.
 */
const init = () => {
	try {
		// Capture command-line arguments
		const args = process.argv.slice( 2 ); // Get all arguments passed after 'node ./bin/create-block.js'

		const blockName = args.shift();

		if ( ! blockName ) {
			console.error( 'Error: You must specify a block name as the first argument.' );
			process.exit( 1 );
		}

		const blockDir = path.join( 'assets', 'src', 'blocks', blockName );
		const userInputedArgs = parseUserInputedArgs( args );

		// Run @wordpress/create-block with provided arguments
		execSync( `npx @wordpress/create-block ${ blockName } --target-dir=${ blockDir } --no-plugin --namespace=elementary-theme ${ userInputedArgs }`, { stdio: 'inherit' } );

		// Check if the block directory exists
		if ( fs.existsSync( blockDir ) ) {
			console.log( `Block created successfully at ${ blockDir }` );
		} else {
			console.error( `Block directory "${ blockDir }" not found. Ensure the block was created successfully.` );
		}
	} catch ( error ) {
		console.error( 'Error creating block:', error.message );
		process.exit( 1 );
	}
};

// Run the script.
init();
