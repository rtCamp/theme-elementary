#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const readline = require( 'readline' );

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

// Start with a prompt.
rl.question( 'Would you like to setup the theme? (Y/n) ', ( answer ) => {
	if ( 'n' === answer.toLowerCase() ) {
		console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
		process.exit( 0 );
	}
	rl.question( 'Enter theme name (shown in WordPress admin)*: ', ( themeName ) => {
		const themeInfo = setupTheme( themeName );
		rl.question( 'Confirm the Theme Details (Y/n) ', ( confirm ) => {
			if ( 'n' === confirm.toLowerCase() ) {
				console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
				process.exit( 0 );
			}
			initTheme( themeInfo );
			rl.close();
		} );
	} );
} );

rl.on( 'close', () => {
	process.exit( 0 );
} );

/**
 * Theme Setup
 *
 * @param {string} themeName
 *
 * @return {Object} themeInfo
 */
const setupTheme = ( themeName ) => {
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
		'Theme Version: ': `1.0.0`,
		'Text Domain: ': `${ themeInfo.kebabCase }`,
		'Package: ': `${ themeInfo.kebabCase }`,
		'Namespace: ': `${ themeInfo.pascalSnakeCase }`,
		'Function Prefix: ': `${ themeInfo.snakeCaseWithUnderscoreSuffix }`,
		'CSS Class Prefix: ': `${ themeInfo.kebabCaseWithHyphenSuffix }`,
		'PHP Variable Prefix: ': `${ themeInfo.snakeCaseWithUnderscoreSuffix }`,
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

	const files = [
		'composer.json',
		'functions.php',
		'index.php',
		'package.json',
		'package-lock.json',
		'phpcs.xml.dist',
		'README.md',
		'style.css',
	];
	const incDirFiles = getAllFiles( getRoot() + '/inc' );
	const templatesDirFiles = getAllFiles( getRoot() + '/templates' );

	// File name to replace in.
	const fileNameToReplace = {
		'class-elementary-theme.php': 'class-' + themeInfo.kebabCase + '.php',
	};

	// Concat all files.
	const allFiles = files.concat( incDirFiles ).concat( templatesDirFiles );

	// Replace files contents.
	console.log( info.success( '\nUpdating theme details in files...' ) );
	Object.keys( chunksToReplace ).forEach( ( key ) => {
		replaceFileContent( allFiles, key, chunksToReplace[ key ] );
	} );
	if ( ! fileContentUpdated ) {
		console.log( info.error( 'No file content updated.\n' ) );
	}

	// Replace file names
	console.log( info.success( '\nUpdating theme bootstrap file name...' ) );
	Object.keys( fileNameToReplace ).forEach( ( key ) => {
		replaceFileName( allFiles, key, fileNameToReplace[ key ] );
	} );
	if ( ! fileNameUpdated ) {
		console.log( info.error( 'No file name updated.\n' ) );
	}

	if ( fileContentUpdated || fileNameUpdated ) {
		console.log( info.success( '\nYour new theme is ready to go!' ), '✨' );
		// Docs link
		console.log( info.success( '\nFor more information on how to use this theme, please visit the following link: ' + info.warning( 'https://github.com/rtCamp/theme-elementary/blob/main/README.md' ) ) );
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
	const files = fs.readdirSync( dir );
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