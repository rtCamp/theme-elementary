#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const readline = require( 'readline' );
const { promisify } = require( 'util' );
const exec = promisify( require( 'child_process' ).exec );

/**
 * Define Constants
 */
const rl = readline.createInterface( {
	input: process.stdin,
	output: process.stdout,
} );

const info = {
	error: ( message ) => {
		return `\x1b[31m${ message }\x1b[0m`;
	},
	success: ( message ) => {
		return `\x1b[32m${ message }\x1b[0m`;
	},
	warning: ( message ) => {
		return `\x1b[33m${ message }\x1b[0m`;
	},
	message: ( message ) => {
		return `\x1b[34m${ message }\x1b[0m`;
	},
};

let fileContentUpdated = false;
let fileNameUpdated = false;
let themeCleanup = false;
let isGitInitialized = false;

const args = process.argv.slice( 2 );

/**
 * Ask theme claenup question.
 *
 * @return {void}
 */
const themeCleanupQuestion = () => {
	rl.question( 'Would you like to run the theme cleanup? (y/n) ', ( cleanup ) => {
		if ( 'n' === cleanup.toLowerCase() ) {
			console.log( info.warning( '\nExiting without running theme cleanup.\n' ) );
		} else {
			runThemeCleanup();
		}

		// Run husky setup from JS file to avoid husky install error.
		runHuskySetup();
		rl.close();
	} );
};

if ( 0 === args.length ) {
	rl.question( 'Would you like to setup the theme? (y/n) ', ( answer ) => {
		if ( 'n' === answer.toLowerCase() ) {
			console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
			rl.close();
		}

		rl.question( 'Enter theme name (shown in WordPress admin)*: ', ( themeName ) => {
			const themeInfo = renderThemeDetails( themeName );
			rl.question( 'Confirm the Theme Details (y/n) ', ( confirm ) => {
				if ( 'n' === confirm.toLowerCase() ) {
					console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
					rl.close();
				}
				initTheme( themeInfo );

				// Provide await for the git initialization to complete before running theme cleanup.
				rl.question( 'Would you like to initialize git? (y/n) ', async ( initialize ) => {
					if ( 'n' === initialize.toLowerCase() ) {
						console.log( info.warning( '\nExiting without initializing GitHub.\n' ) );
					} else {
						await initializeGit();
					}
					themeCleanupQuestion();
				} );
			} );
		} );
	} );
} else if ( ( args.includes( '--clean' ) || args.includes( '-c' ) ) && 1 === args.length ) {
	themeCleanupQuestion();
} else {
	console.log( info.error( '\nInvalid arguments.\n' ) );
	process.exit( 0 );
}
rl.on( 'close', () => {
	process.exit( 0 );
} );

/**
 * Run husky setup
 *
 * @return {void}
 */
const runHuskySetup = () => {
	console.log( info.success( '\nInstalling husky...' ) );

	// Check if .git file exists.
	const gitDir = path.resolve( getRoot(), '.git' );
	if ( ! fs.existsSync( gitDir ) ) {
		console.log( info.warning( '\n.git directory does not exists.\nExiting without installing husky.' ) );
		return;
	}

	// Run husky install command.
	const huskyInstallCommand = `husky install`;
	exec( huskyInstallCommand )
		.then( ( result ) => {
			console.log( info.success( `stdout: ${ result.stdout }` ) );
			console.log( info.success( '\nHusky installed successfully!' ), '✨' );
		} )
		.catch( ( error ) => {
			console.log( info.error( `error: ${ error.message }` ) );
			if ( error.stderr ) {
				console.log( info.error( `stderr: ${ error.stderr }` ) );
			}
		} );
};

/**
 * Initialize Git.
 *
 * @return {Promise<void>}
 */
const initializeGit = async () => {
	// Initialize git.
	console.log( info.success( '\nInitializing git...' ) );

	// Check if .git file exists.
	const gitDir = path.resolve( getRoot(), '.git' );
	if ( fs.existsSync( gitDir ) ) {
		console.log( info.warning( '\n.git directory already exists.\n' ) );
		return;
	}

	const pathToRoot = path.resolve( getRoot() );
	const gitInitCommand = `git init '${ pathToRoot }'`;
	const pathToAllFiles = path.resolve( getRoot(), '.' );
	const gitAddCommand = `git add '${ pathToAllFiles }'`;
	const gitCommit = `git commit -m 'Initialize project using rtCamp/theme-elementary'`;

	try {
		// Execute git init command in the root directory and await for the response.
		const initResult = await exec( gitInitCommand );
		console.log( info.success( `stdout: ${ initResult.stdout }` ) );

		// Execute git add command in the root directory and await for the response.
		const addResult = await exec( gitAddCommand );
		console.log( info.success( `stdout: ${ addResult.stdout }` ) );

		// Execute git commit command in the root directory and await for the response.
		const commitResult = await exec( gitCommit );
		console.log( info.success( `stdout: ${ commitResult.stdout }` ) );

		console.log( info.success( '\nGit initialized successfully!' ), '✨' );
		isGitInitialized = true;
	} catch ( error ) {
		console.log( info.error( `error: ${ error.message }` ) );
		if ( error.stderr ) {
			console.log( info.error( `stderr: ${ error.stderr }` ) );
		}
	}
};

/**
 * Renders the theme setup modal with all necessary information related to the search-replace.
 *
 * @param {string} themeName
 *
 * @return {Object} themeInfo
 */
const renderThemeDetails = ( themeName ) => {
	console.log( info.success( '\nFiring up the theme setup...' ) );

	// Ask theme name.
	if ( ! themeName ) {
		console.log( info.error( '\nTheme name is required.\n' ) );
		process.exit( 0 );
	}

	// Generate theme info.
	const themeInfo = generateThemeInfo( themeName );

	const themeDetails = {
		'Theme Name: ': `${ themeInfo.themeName }`,
		'Theme Version: ': '1.0.0',
		'Text Domain: ': themeInfo.kebabCase,
		'Package: ': themeInfo.trainCase,
		'Namespace: ': themeInfo.pascalSnakeCase,
		'Function Prefix: ': themeInfo.snakeCaseWithUnderscoreSuffix,
		'CSS Class Prefix: ': themeInfo.kebabCaseWithHyphenSuffix,
		'PHP Variable Prefix: ': themeInfo.snakeCaseWithUnderscoreSuffix,
		'Version Constant: ': `${ themeInfo.macroCase }_VERSION`,
		'Theme Directory Constant: ': `${ themeInfo.macroCase }_TEMP_DIR`,
		'Theme Build Directory Constant: ': `${ themeInfo.macroCase }_BUILD_DIR`,
		'Theme Build Directory URI Constant: ': `${ themeInfo.macroCase }_BUILD_URI`,
	};

	const biggestStringLength = themeDetails[ 'Theme Build Directory URI Constant: ' ].length + 'Theme Build Directory URI Constant: '.length;

	console.log( info.success( '\nTheme Details:' ) );
	console.log(
		info.warning( '┌' + '─'.repeat( biggestStringLength + 2 ) + '┐' ),
	);
	Object.keys( themeDetails ).forEach( ( key ) => {
		console.log(
			info.warning( '│' + ' ' + info.success( key ) + info.message( themeDetails[ key ] ) + ' ' + ' '.repeat( biggestStringLength - ( themeDetails[ key ].length + key.length ) ) + info.warning( '│' ) ),
		);
	} );
	console.log(
		info.warning( '└' + '─'.repeat( biggestStringLength + 2 ) + '┘' ),
	);

	return themeInfo;
};

/**
 * Initialize new theme
 *
 * @param {Object} themeInfo
 */
const initTheme = ( themeInfo ) => {
	const chunksToReplace = {
		'rtcamp/elementary': themeInfo.packageName, // Specifically targets composer.json file.
		'elementary theme': themeInfo.themeNameLowerCase,
		'Elementary Theme': themeInfo.themeName,
		'ELEMENTARY THEME': themeInfo.themeNameCobolCase,
		'elementary-theme': themeInfo.kebabCase,
		'Elementary-Theme': themeInfo.trainCase,
		'ELEMENTARY-THEME': themeInfo.cobolCase,
		elementary_theme: themeInfo.snakeCase,
		Elementary_Theme: themeInfo.pascalSnakeCase,
		ELEMENTARY_THEME: themeInfo.macroCase,
		'elementary-theme-': themeInfo.kebabCaseWithHyphenSuffix,
		'Elementary-Theme-': themeInfo.trainCaseWithHyphenSuffix,
		'ELEMENTARY-THEME-': themeInfo.cobolCaseWithHyphenSuffix,
		elementary_theme_: themeInfo.snakeCaseWithUnderscoreSuffix,
		Elementary_Theme_: themeInfo.pascalSnakeCaseWithUnderscoreSuffix,
		ELEMENTARY_THEME_: themeInfo.macroCaseWithUnderscoreSuffix,
	};

	const files = getAllFiles( getRoot() );

	// File name to replace in.
	const fileNameToReplace = {};
	files.forEach( ( file ) => {
		const fileName = path.basename( file );
		Object.keys( chunksToReplace ).forEach( ( key ) => {
			if ( fileName.includes( key ) ) {
				fileNameToReplace[ fileName ] = fileName.replace( key, chunksToReplace[ key ] );
			}
		} );
	} );

	// Replace files contents.
	console.log( info.success( '\nUpdating theme details in file(s)...' ) );
	Object.keys( chunksToReplace ).forEach( ( key ) => {
		replaceFileContent( files, key, chunksToReplace[ key ] );
	} );

	if ( ! fileContentUpdated ) {
		console.log( info.error( 'No file content updated.\n' ) );
	}

	// Replace file names
	console.log( info.success( '\nUpdating theme file(s) name...' ) );
	Object.keys( fileNameToReplace ).forEach( ( key ) => {
		replaceFileName( files, key, fileNameToReplace[ key ] );
	} );
	if ( ! fileNameUpdated ) {
		console.log( info.error( 'No file name updated.\n' ) );
	}

	if ( fileContentUpdated || fileNameUpdated ) {
		console.log( info.success( '\nYour new theme is ready to go!' ), '✨' );
		// Docs link
		console.log( info.success( '\nFor more information on how to use this theme, please visit the following link: ' + info.warning( 'https://github.com/rtCamp/theme-elementary/blob/main/README.md\n' ) ) );
	} else {
		console.log( info.warning( '\nNo changes were made to your theme.\n' ) );
	}
};

/**
 * Get all files in a directory
 *
 * @param {Array} dir - Directory to search
 */
const getAllFiles = ( dir ) => {
	const dirOrFilesIgnore = [
		'.git',
		'.github',
		'node_modules',
		'vendor',
	];

	try {
		let files = fs.readdirSync( dir );
		files = files.filter( ( fileOrDir ) => ! dirOrFilesIgnore.includes( fileOrDir ) );

		const allFiles = [];
		files.forEach( ( file ) => {
			const filePath = path.join( dir, file );
			const stat = fs.statSync( filePath );
			if ( stat.isDirectory() ) {
				allFiles.push( ...getAllFiles( filePath ) );
			} else {
				allFiles.push( filePath );
			}
		} );
		return allFiles;
	} catch ( err ) {
		console.log( info.error( err ) );
	}
};

/**
 * Replace content in file
 *
 * @param {Array}  files           Files to search
 * @param {string} chunksToReplace String to replace
 * @param {string} newChunk        New string to replace with
 */
const replaceFileContent = ( files, chunksToReplace, newChunk ) => {
	files.forEach( ( file ) => {
		const filePath = path.resolve( getRoot(), file );

		try {
			let content = fs.readFileSync( filePath, 'utf8' );
			const regex = new RegExp( chunksToReplace, 'g' );
			content = content.replace( regex, newChunk );
			if ( content !== fs.readFileSync( filePath, 'utf8' ) ) {
				fs.writeFileSync( filePath, content, 'utf8' );
				console.log( info.success( `Updated [${ info.message( chunksToReplace ) }] ${ info.success( 'to' ) } [${ info.message( newChunk ) }] ${ info.success( 'in file' ) } [${ info.message( path.basename( file ) ) }]` ) );
				fileContentUpdated = true;
			}
		} catch ( err ) {
			console.log( info.error( `\nError: ${ err }` ) );
		}
	} );
};

/**
 * Change File Name
 *
 * @param {Array}  files       Files to search
 * @param {string} oldFileName Old file name
 * @param {string} newFileName New file name
 */
const replaceFileName = ( files, oldFileName, newFileName ) => {
	files.forEach( ( file ) => {
		if ( oldFileName !== path.basename( file ) ) {
			return;
		}
		const filePath = path.resolve( getRoot(), file );
		const newFilePath = path.resolve( getRoot(), file.replace( oldFileName, newFileName ) );
		try {
			fs.renameSync( filePath, newFilePath );
			console.log( info.success( `Updated file [${ info.message( path.basename( filePath ) ) }] ${ info.success( 'to' ) } [${ info.message( path.basename( newFilePath ) ) }]` ) );
			fileNameUpdated = true;
		} catch ( err ) {
			console.log( info.error( `\nError: ${ err }` ) );
		}
	} );
};

/**
 * Generate Theme Info from Theme Name
 *
 * @param {string} themeName
 */
const generateThemeInfo = ( themeName ) => {
	const themeNameLowerCase = themeName.toLowerCase();

	const kebabCase = themeName.replace( /\s+/g, '-' ).toLowerCase();
	const packageName = `rtcamp/${ kebabCase }`;
	const snakeCase = kebabCase.replace( /\-/g, '_' );
	const kebabCaseWithHyphenSuffix = kebabCase + '-';
	const snakeCaseWithUnderscoreSuffix = snakeCase + '_';

	const trainCase = kebabCase.replace( /\b\w/g, ( l ) => {
		return l.toUpperCase();
	} );
	const themeNameTrainCase = trainCase.replace( /\-/g, ' ' );
	const pascalSnakeCase = trainCase.replace( /\-/g, '_' );
	const trainCaseWithHyphenSuffix = trainCase + '-';
	const pascalSnakeCaseWithUnderscoreSuffix = pascalSnakeCase + '_';

	const cobolCase = kebabCase.toUpperCase();
	const themeNameCobolCase = themeNameTrainCase.toUpperCase();
	const macroCase = snakeCase.toUpperCase();
	const cobolCaseWithHyphenSuffix = cobolCase + '-';
	const macroCaseWithUnderscoreSuffix = macroCase + '_';

	return {
		themeName,
		packageName,
		themeNameLowerCase,
		kebabCase,
		snakeCase,
		kebabCaseWithHyphenSuffix,
		snakeCaseWithUnderscoreSuffix,
		trainCase,
		themeNameTrainCase,
		pascalSnakeCase,
		trainCaseWithHyphenSuffix,
		pascalSnakeCaseWithUnderscoreSuffix,
		cobolCase,
		themeNameCobolCase,
		macroCase,
		cobolCaseWithHyphenSuffix,
		macroCaseWithUnderscoreSuffix,
	};
};

/**
 * Return root directory
 *
 * @return {string} root directory
 */
const getRoot = () => {
	return path.resolve( __dirname, '../' );
};

/**
 * Run theme cleanup to delete files and directories
 *
 * It will remove following directories and files:
 * 1. .git
 * 2. .github
 * 3. bin
 * 4. languages
 */
const runThemeCleanup = () => {
	const deleteDirs = [
		'.github',
		'bin',
		'languages',
	];

	if ( ! isGitInitialized ) {
		deleteDirs.push( '.git' );
	}

	deleteDirs.forEach( ( dir ) => {
		const dirPath = path.resolve( getRoot(), dir );
		try {
			if ( fs.existsSync( dirPath ) ) {
				fs.rmdirSync( dirPath, {
					recursive: true,
				} );
				console.log( info.success( `Deleted directory [${ info.message( dir ) }]` ) );
				themeCleanup = true;
			}
		} catch ( err ) {
			console.log( info.error( `\nError: ${ err }` ) );
		}
	} );

	if ( themeCleanup ) {
		console.log( info.success( '\nTheme cleanup completed!' ), '✨' );
	} else {
		console.log( info.warning( '\nNo theme cleanup required!\n' ) );
	}
};
