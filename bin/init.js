#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const readline = require( 'readline' );
const { promisify } = require( 'util' );
const { execSync } = require( 'child_process' );

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

/**
 * Return root directory
 *
 * @return {string} root directory
 */
const getRoot = () => {
	return path.resolve( __dirname, '../' );
};

let fileContentUpdated = false;
let fileNameUpdated = false;
let themeCleanup = false;
let isGitInitialized = false;

const args = process.argv.slice( 2 );

if ( 0 === args.length ) {
	rl.question( 'Would you like to setup the theme? (y/n) ', ( answer ) => {

		if ( 'n' === answer.toLowerCase() ) {
			console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
			rl.close();
		}

		rl.question( 'Enter theme name (shown in WordPress admin)*: ', ( themeName ) => {

			rl.question( 'Would you like to add TailwindCSS support? (y/n) ', ( tailwindAnswer ) => {

				if ( 'y' === tailwindAnswer.toLowerCase() ) {

					tailwindcssSetup();
				}
				rl.close();
				
				const themeInfo = renderThemeDetails( themeName );
				
				rl.question( 'Confirm the Theme Details (y/n) ', ( confirm ) => {
					
					if ( 'n' === confirm.toLowerCase() ) {
						console.log( info.warning( '\nTheme Setup Cancelled.\n' ) );
						rl.close();
					}
					
					initTheme( themeInfo );
					
					rl.question( 'Would you like to initialize git (Note: It will delete any `.git` folder already in current directory)? (y/n) ', async ( initialize ) => {
						if ( 'n' === initialize.toLowerCase() ) {
							console.log( info.warning( '\nExiting without initializing GitHub.\n' ) );
							await askQuestionForHuskyInstallation();
						} else {
							await initializeGit()
						}
						themeCleanupQuestion();
					} );
				} );
			} );
		} );
	} );
} else if ( ( args.includes( '--clean' ) || args.includes( '-c' ) ) && 1 === args.length ) {
	themeCleanupQuestion();
} else {
	console.log( info.error( '\nInvalid arguments.\n' ) );
	rl.close();
}
rl.on( 'close', () => {
	process.exit( 0 );
} );

/**
 * Setup the TailwindCSS files.
 */
const tailwindcssSetup = () => {

	const tailwindFiles = [
		{
			path: path.resolve( getRoot(), 'tailwind.config.js' ),
			source: path.resolve( getRoot(), 'bin/templates/tailwindcss/tailwind.config.js' ),
		},
		{
			path: path.resolve( getRoot(), 'postcss.config.js' ),
			source: path.resolve( getRoot(), 'bin/templates/tailwindcss/postcss.config.js' ),
		},
		{
			path: path.resolve( getRoot(), 'assets/src/css/tailwind.scss' ),
			source: path.resolve( getRoot(), 'bin/templates/tailwindcss/tailwind.scss' ),
		}
	];

	// Install the required packages and create the necessary files.
	console.log( info.success( '\nInstalling TailwindCSS and its dependencies...' ) );
	execSync( 'npm install tailwindcss postcss autoprefixer --save-dev' );
	execSync( 'npx tailwindcss init -p' );
	
	tailwindFiles.forEach( ( file ) => {
		copyFileContents( file.path, file.source );
	} );

	console.log( info.success( '\nTailwindCSS setup completed!' ), '✨' );
};

/**
 * Creates a file with the given content.
 * @param {string} filePath Path to the file.
 * @param {string} source  Path to the source file.
 */
const copyFileContents = ( filePath, source ) => {
	// copy the file contents.
	fs.copyFileSync( source, filePath );
};

/**
 * Update composer.json file.
 *
 * @return {void}
 */
const updateComposerJson = () => {
	console.log( info.message( '\nRemoving post-install-cmd script from the composer.json...' ) );
	const composerJsonPath = path.resolve( getRoot(), 'composer.json' );

	try {
		if ( ! fs.existsSync( composerJsonPath ) ) {
			return;
		}

		const composerJson = JSON.parse( fs.readFileSync( composerJsonPath ) );

		// Remove scripts.
		delete composerJson.scripts['post-install-cmd'];

		// Commit the changes to file.
		fs.writeFileSync( composerJsonPath, JSON.stringify( composerJson, null, 2 ) );
		console.log( info.success( '\ncomposer.json updated successfully!' ), '✨' );
	} catch ( error ) {
		console.log( info.error( `Error while updating composer.json: ${ error.message }` ) );
		console.log( info.message( 'Please remove post-install-cmd script from the composer.json file manually.' ) );
	}
}

/**
 * Update package.json file.
 *
 * @return {void}
 */
const updatePackageJson = () => {
	console.log( info.message( '\nRemoving init script from the package.json...' ) );
	const packageJsonPath = path.resolve( getRoot(), 'package.json' );

	try {
		if ( ! fs.existsSync( packageJsonPath ) ) {
			return;
		}

		const packageJson = JSON.parse( fs.readFileSync( packageJsonPath ) );

		delete packageJson.scripts['init'];

		if ( ! packageJson.scripts['prepare'] ) {
			return;
		}

		const prepareScript = packageJson.scripts['prepare'];

		// Check if 'npm run init' is part of the prepare script.
		if ( ! prepareScript.includes( 'npm run init' ) ) {
			return;
		}

		// Split the prepare script into an array of individual scripts.
		const prepareScriptArray = prepareScript.split( '&&' ).map( ( script ) => script.trim() );

		// Find the index of 'npm run init' in the array.
		const initScriptIndex = prepareScriptArray.indexOf( 'npm run init' );

		// Remove 'npm run init' from the array if it exists.
		if ( -1 !== initScriptIndex ) {
			prepareScriptArray.splice( initScriptIndex, 1 );
		}
		// Join the array back into a string and update the 'prepare' script in packageJson.
		packageJson.scripts['prepare'] = prepareScriptArray.join( ' && ' );

		// Commit the changes to file.
		fs.writeFileSync( packageJsonPath, JSON.stringify( packageJson, null, 2 ) );
		console.log( info.success( '\npackage.json updated successfully!' ), '✨' );
	} catch ( error ) {
		console.log( info.error( `Error while updating package.json: ${ error.message }` ) );
		console.log( info.message( 'Please remove init script and remove npm run init command from the prepare script from the package.json file manually.' ) );
	}
}

/**
 * Ask theme claenup question.
 *
 * @return {void}
 */
function themeCleanupQuestion() {
	rl.question( 'Would you like to run the theme cleanup? (y/n) ', ( cleanup ) => {
		if ( 'n' === cleanup.toLowerCase() ) {
			console.log( info.warning( '\nExiting without running theme cleanup.\n' ) );
		} else {
			updateComposerJson();
			updatePackageJson();
			runThemeCleanup();
		}
		rl.close();
	} );
}

/**
 * Initialize Git.
 *
 * @return {void}
 */
const initializeGit = async () => {
	// Initialize git.
	console.log( info.success( '\nInitializing git...' ) );

	// Check if .git file exists.
	const gitDir = path.resolve( getRoot(), '.git' );
	try {
		if ( fs.existsSync( gitDir ) ) {
			// Remove .git directory.
			fs.rmSync( gitDir, {
				recursive: true,
			} );
		}
	} catch ( error ) {

	}

	const pathToRoot = path.resolve( getRoot() );
	const gitInitCommand = `git init '${ pathToRoot }'`;
	const pathToAllFiles = path.resolve( getRoot(), '.' );
	const gitAddCommand = `git add '${ pathToAllFiles }'`;
	// Apply --no-verify flag to skip husky pre-commit hook.
	const gitCommit = `git commit -m 'Initialize project using https://github.com/rtCamp/theme-elementary' --no-verify`;

	try {
		// Execute git init command in the root directory.
		execSync( gitInitCommand );
		console.log( info.success( '\nGit initialized successfully!' ), '✨' );
		isGitInitialized = true;

		await askQuestionForHuskyInstallation();

		// Execute git add command in the root directory.
		execSync( gitAddCommand );

		// Execute git commit command in the root directory.
		execSync( gitCommit );
	} catch ( error ) {
		console.log( info.error( 'Error while installing Git. Please check above for the logs.' ) );
	}
};

/**
 * Ask Question for Husky Installation.
 *
 * @return {void}
 */
const askQuestionForHuskyInstallation = async () => {

	// Promisify the question function for this instance only as this question is in between another question so code after this was getting executed before this is completed.
	// There is readlinePromises Interface introduced in v17.0.0 and is in Experimental phase so we are using promisify for now.
	// In future we can use readlinePromises Interface.
	const question = promisify( rl.question ).bind( rl );
	const install = await question( 'Would you like to install Husky? (y/n) ' );
	if ( 'n' === install.toLowerCase() ) {
		console.log(info.warning('\nExiting without installing Husky.\n'));
		return;
	}
	installHusky();
}

/**
 * Install Husky.
 *
 * @return {void}
 */
const installHusky = () => {

	// Search if .git directory exists.
	const gitDir = path.resolve( getRoot(), '.git' );
	if ( ! fs.existsSync( gitDir ) ) {
		console.log( info.warning( '\nGit is not initialized. Please initialize git first.\n' ) );
		return;
	}

	// Install Husky.
	console.log( info.success( '\nInstalling Husky...' ) );

	const pathToRoot = path.resolve( getRoot() );
	const huskyInstallCommand = `npm install husky@9.0.1 --save-dev --prefix '${ pathToRoot }'`;

	try {
		// Execute Husky install command in the root directory.
		execSync( huskyInstallCommand );

		const pathToPackageJson = path.resolve( getRoot(), 'package.json' );

		let prepareScript = '';

		// Extracting the prepare script from package.json before husky installation ovrwrites it.
		if ( fs.existsSync( pathToPackageJson ) ) {
			const packageJson = JSON.parse( fs.readFileSync( pathToPackageJson ) );

			if ( packageJson.scripts && packageJson.scripts.prepare ) {
				prepareScript = packageJson.scripts.prepare;
			}
		}

		execSync( 'npx husky init' );
		execSync( 'echo "npm run lint:staged" > .husky/pre-commit' );

		if ( '' === prepareScript ) {
			return;
		}

		// Update the prepare script with the old prepare script after husky installation overwrites it.
		if ( fs.existsSync( pathToPackageJson ) ) {
			const packageJson = JSON.parse( fs.readFileSync( pathToPackageJson ) );

			if ( packageJson.scripts && packageJson.scripts.prepare ) {
				packageJson.scripts.prepare += ` && ${ prepareScript }`;

				fs.writeFileSync( pathToPackageJson, JSON.stringify( packageJson, null, 2 ) );
			}
		}

		console.log( info.success( '\nHusky installed successfully!' ), '✨' );
	} catch ( error ) {
		console.log( error );
		console.log( info.error( 'Error while installing husky. Please check above for the logs.' ) );
	}
}

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

	try {
		const result = execSync( 'composer dump-autoload' );
		console.log( info.success( result ) );
	} catch ( error ) {
		console.log( info.error( `Error while executing composer dump-autoload: ${error}` ) );
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
		'bin/templates',
		'bin/init.js',
		'languages',
	];

	if ( ! isGitInitialized ) {
		deleteDirs.push( '.git' );
	}

	deleteDirs.forEach( ( dir ) => {
		const dirPath = path.resolve( getRoot(), dir );
		try {
			if ( fs.existsSync( dirPath ) ) {
				let isDirectory = false;

				if ( true === fs.lstatSync( dirPath ).isDirectory() ) {
					isDirectory = true;
				}

				// rmSync function introduced in Node v14.14.0. It can delete files and directories recursively.
				fs.rmSync( dirPath, {
					recursive: true,
				} );

				if ( isDirectory ) {
					console.log( info.success( `Removed directory [${ info.message( dir ) }]` ) );
				} else {
					console.log( info.success( `Removed file [${ info.message( dir ) }]` ) );
				}
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
