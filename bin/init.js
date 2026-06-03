#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * Theme setup / feature manager.
 *
 * First run (no `.wp-tooling.json`): rename the starter theme to your theme
 * name, persist the choices, pick optional features (Tailwind, ...), and
 * optionally set up git + git hooks. Later runs jump straight to the feature manager,
 * so features can be toggled any time with `npm run init`.
 *
 * The whole flow runs on the wp-tooling TTY UI kit (text / confirm / spinner),
 * in a single process.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { execSync } = require( 'child_process' );
const { text, confirm, spinner, style, CancelledError } = require( '@rtcamp/wp-tooling/ui' );

// Persisted project config. Its presence flips init from "scaffold mode" to
// "feature-manager mode"; it is also read by webpack and the wp-tooling
// scaffold engine (namespace / feature flags).
const CONFIG_FILE = '.wp-tooling.json';

/**
 * @return {string} The theme root directory.
 */
const getRoot = () => path.resolve( __dirname, '../' );

// Set when git is initialized in this session, so cleanup keeps the new .git.
let isGitInitialized = false;

/**
 * Entry point.
 *
 * @return {Promise<void>}
 */
const main = async () => {
	const args = process.argv.slice( 2 );

	if ( args.includes( '--clean' ) || args.includes( '-c' ) ) {
		await themeCleanupFlow();
		return;
	}

	if ( args.length > 0 ) {
		console.log( style.error( '\nInvalid arguments.\n' ) );
		return;
	}

	if ( fs.existsSync( path.resolve( getRoot(), CONFIG_FILE ) ) ) {
		// Manage mode: already set up — toggle features only.
		console.log( style.success( '\nTheme already initialized — opening the feature manager.\n' ) );
		await runFeatureManager( { install: true } );
		return;
	}

	await scaffoldFlow();
};

/**
 * First-run scaffold flow.
 *
 * @return {Promise<void>}
 */
const scaffoldFlow = async () => {
	if ( ! ( await confirm( { message: 'Would you like to set up the theme?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nTheme setup cancelled.\n' ) );
		return;
	}

	const themeName = await text( {
		message: 'Enter theme name (shown in WordPress admin)',
		validate: required,
	} );

	// Show the derived details; declining opens a per-field editor and loops
	// until the developer confirms.
	let fields = defaultFields( themeName.trim() );
	while ( true ) {
		renderThemeDetails( fields );
		if ( await confirm( { message: 'Confirm the theme details?', defaultValue: true } ) ) {
			break;
		}
		console.log( style.info( '\nCustomize each field (press Enter to keep the shown value):' ) );
		fields = await customizeFields( fields );
	}

	applyThemeName( fields );
	applyVersion( fields.version );
	writeProjectConfig( fields );

	// Pick features (Tailwind, ...). On first run we are usually inside
	// `npm install` (the prepare hook), so defer the nested npm install.
	console.log( style.success( '\nChoose optional features for your theme:' ) );
	await runFeatureManager( { install: false } );

	if ( await confirm( {
		message: 'Initialize git? (Note: deletes any `.git` folder already in this directory)',
		defaultValue: false,
	} ) ) {
		await initializeGit();
	} else {
		console.log( style.warning( '\nSkipping git initialization.' ) );
		await askHooks();
	}

	await themeCleanupFlow();

	console.log( style.success(
		'\nAll set! If you enabled any features, run ' + style.warning( '`npm install`' ) +
		style.success( ' once to install their dependencies.' ),
	) );
	console.log( style.success(
		'Re-run ' + style.warning( '`npm run init`' ) +
		style.success( ' any time to toggle features on or off.\n' ),
	) );
};

/**
 * Split a value into lower-cased words on spaces, hyphens, underscores, and
 * camelCase boundaries. Shared by every case helper below.
 *
 * @param {string} value
 * @return {string[]} Lower-cased words.
 */
const words = ( value ) =>
	String( value )
		.trim()
		.replace( /([a-z0-9])([A-Z])/g, '$1 $2' )
		.split( /[\s\-_]+/ )
		.filter( Boolean )
		.map( ( word ) => word.toLowerCase() );

const cap = ( word ) => word.charAt( 0 ).toUpperCase() + word.slice( 1 );

const toKebab = ( value ) => words( value ).join( '-' );
const toSnake = ( value ) => words( value ).join( '_' );
const toTrain = ( value ) => words( value ).map( cap ).join( '-' );
const toCobol = ( value ) => words( value ).join( '-' ).toUpperCase();
const toPascal = ( value ) => words( value ).map( cap ).join( '' );
const toPascalSnake = ( value ) => words( value ).map( cap ).join( '_' );

/** A non-empty validator for text() prompts. */
const required = ( value ) =>
	value && value.trim() ? undefined : 'This field is required.';

/**
 * Editable source fields for a theme, defaulted from the theme name. Every
 * other detail (constants, CSS prefix, case variants) is derived from these.
 *
 * @param {string} themeName
 * @return {Object} Source fields.
 */
const defaultFields = ( themeName ) => ( {
	themeName,
	version: '1.0.0',
	textDomain: toKebab( themeName ),
	namespace: `rtCamp\\Theme\\${ toPascal( themeName ) }`,
	functionPrefix: toSnake( themeName ),
	packageName: `rtcamp/${ toKebab( themeName ) }`,
} );

/**
 * Canonical identity derived from the (possibly edited) source fields. Display,
 * search-replace, and the saved config all go through this, keeping them in sync.
 *
 * @param {Object} f Source fields.
 * @return {Object} Canonical identity values.
 */
const resolveIdentity = ( f ) => {
	const textDomain = toKebab( f.textDomain );
	const prefix = toSnake( f.functionPrefix );
	return {
		themeName: f.themeName,
		version: f.version,
		textDomain,
		namespace: f.namespace.trim(),
		packageName: f.packageName.trim(),
		functionPrefix: `${ prefix }_`,
		cssClassPrefix: `${ textDomain }-`,
		constantPrefix: prefix.toUpperCase(),
	};
};

/**
 * Prompt for each editable field, defaulting to its current value (Enter keeps
 * it). Text domain and prefix are normalised to kebab/snake so generated slugs
 * and identifiers stay valid. No cascade: editing one field never rewrites
 * another — the table re-renders so the final set is reviewed before confirm.
 *
 * @param {Object} f Current source fields.
 * @return {Promise<Object>} Updated source fields.
 */
const customizeFields = async ( f ) => ( {
	themeName: ( await text( { message: 'Theme name', defaultValue: f.themeName, validate: required } ) ).trim(),
	version: ( await text( { message: 'Theme version', defaultValue: f.version, validate: required } ) ).trim(),
	textDomain: toKebab( await text( { message: 'Text domain', defaultValue: f.textDomain, validate: required } ) ),
	namespace: ( await text( { message: 'PHP namespace', defaultValue: f.namespace, validate: required } ) ).trim(),
	functionPrefix: toSnake( await text( { message: 'Function / constant prefix', defaultValue: f.functionPrefix, validate: required } ) ),
	packageName: ( await text( { message: 'Composer package name', defaultValue: f.packageName, validate: required } ) ).trim(),
} );

/**
 * Ordered [search, replacement] pairs applied literally across the project.
 *
 * Literal `split().join()` (not RegExp) needs no escaping, so the back-slashed
 * PHP namespace renames safely. Each token family maps to its own field, so the
 * fields can be customised independently.
 *
 * @param {Object} f Source fields.
 * @return {Array<[string, string]>} Replacement pairs.
 */
const deriveReplacements = ( f ) => {
	const textDomain = toKebab( f.textDomain );
	const prefix = toSnake( f.functionPrefix );
	const namespace = f.namespace.trim();
	return [
		// PHP namespace uses single back-slashes; composer.json stores them
		// JSON-escaped (doubled). Cover both so the namespace renames everywhere.
		[ 'rtCamp\\Theme\\Elementary', namespace ],
		[ 'rtCamp\\\\Theme\\\\Elementary', namespace.replace( /\\/g, '\\\\' ) ],
		// Composer package name.
		[ 'rtcamp/elementary', f.packageName.trim() ],
		// Human-readable name.
		[ 'elementary theme', f.themeName.toLowerCase() ],
		[ 'Elementary Theme', f.themeName ],
		[ 'ELEMENTARY THEME', f.themeName.toUpperCase() ],
		// Slug / text domain / CSS prefix.
		[ 'elementary-theme', textDomain ],
		[ 'Elementary-Theme', toTrain( textDomain ) ],
		[ 'ELEMENTARY-THEME', toCobol( textDomain ) ],
		// snake_case identifiers, function prefix, constants.
		[ 'elementary_theme', prefix ],
		[ 'Elementary_Theme', toPascalSnake( prefix ) ],
		[ 'ELEMENTARY_THEME', prefix.toUpperCase() ],
		// Bare-"elementary" identifiers (run after the compound tokens above;
		// specific so the repo URLs and elementary.local examples stay untouched).
		[ 'Theme Elementary', f.themeName ],
		[ 'elementary-media-text-interactive', `${ textDomain }-media-text-interactive` ],
		[ 'elementary/media-text', `${ textDomain }/media-text` ], // Interactivity store namespace
		[ 'elementary-featured', `${ textDomain }-featured` ],
		[ 'elementary-browser-sync', `${ textDomain }-browser-sync` ],
		[ 'elementary-settings', `${ textDomain }-settings` ],
		[ 'elementary_main_section', `${ prefix }_main_section` ],
		[ 'elementary_', `${ prefix }_` ], // option-key prefix
		[ 'Elementary', f.themeName ], // bare label — keep last
	];
};

/**
 * Render the theme details table for confirmation. Values come from
 * {@link resolveIdentity}, so the table shows exactly what will be applied and
 * saved (and what the scaffold engine later reuses).
 *
 * @param {Object} fields Source fields.
 * @return {void}
 */
const renderThemeDetails = ( fields ) => {
	const id = resolveIdentity( fields );
	const details = {
		'Theme Name: ': id.themeName,
		'Theme Version: ': id.version,
		'Text Domain: ': id.textDomain,
		'Package: ': id.packageName,
		'Namespace: ': id.namespace,
		'Function Prefix: ': id.functionPrefix,
		'CSS Class Prefix: ': id.cssClassPrefix,
		'Version Constant: ': `${ id.constantPrefix }_VERSION`,
		'Build Dir Constant: ': `${ id.constantPrefix }_BUILD_DIR`,
		'Build URI Constant: ': `${ id.constantPrefix }_BUILD_URI`,
	};

	const width = Math.max(
		...Object.entries( details ).map( ( [ k, v ] ) => k.length + v.length ),
	);

	console.log( style.success( '\nTheme Details:' ) );
	console.log( style.warning( '┌' + '─'.repeat( width + 2 ) + '┐' ) );
	for ( const [ key, value ] of Object.entries( details ) ) {
		const pad = ' '.repeat( width - ( key.length + value.length ) );
		console.log(
			style.warning( '│ ' ) + style.success( key ) + style.info( value ) + pad + style.warning( ' │' ),
		);
	}
	console.log( style.warning( '└' + '─'.repeat( width + 2 ) + '┘' ) );
};

/**
 * Apply the theme identity across every project file (contents + filenames),
 * then regenerate the Composer autoloader for the new namespace.
 *
 * @param {Object} fields Source fields.
 * @return {void}
 */
const applyThemeName = ( fields ) => {
	const replacements = deriveReplacements( fields );
	const files = getAllFiles( getRoot() );

	const s = spinner( 'Applying your theme name across the project…' );
	s.start();

	let filesChanged = 0;
	let filesRenamed = 0;

	try {
		// 1. Replace file contents.
		for ( const file of files ) {
			const original = fs.readFileSync( file, 'utf8' );
			let updated = original;
			for ( const [ search, replacement ] of replacements ) {
				updated = updated.split( search ).join( replacement );
			}
			if ( updated !== original ) {
				fs.writeFileSync( file, updated, 'utf8' );
				filesChanged++;
			}
		}

		// 2. Rename files whose name carries a token (e.g. elementary-theme.pot).
		for ( const file of files ) {
			const base = path.basename( file );
			let nextBase = base;
			for ( const [ search, replacement ] of replacements ) {
				nextBase = nextBase.split( search ).join( replacement );
			}
			if ( nextBase !== base ) {
				fs.renameSync( file, path.join( path.dirname( file ), nextBase ) );
				filesRenamed++;
			}
		}

		s.succeed(
			`Theme name applied — ${ filesChanged } file(s) updated, ${ filesRenamed } renamed.`,
		);
	} catch ( error ) {
		s.fail( `Failed to apply theme name: ${ error.message }` );
		throw error;
	}

	const dump = spinner( 'Regenerating Composer autoloader…' );
	dump.start();
	try {
		execSync( 'composer dump-autoload', { cwd: getRoot(), stdio: 'pipe' } );
		dump.succeed( 'Composer autoloader regenerated.' );
	} catch ( error ) {
		dump.fail( 'Could not run `composer dump-autoload` — run it manually after installing dependencies.' );
	}
};

/**
 * Apply the chosen version to the style.css header and package.json, surgically
 * (regex, no reformatting). Version is not a search-replace token, so it is set
 * here after the rename.
 *
 * @param {string} version
 * @return {void}
 */
const applyVersion = ( version ) => {
	const styleCss = path.resolve( getRoot(), 'style.css' );
	try {
		if ( fs.existsSync( styleCss ) ) {
			const css = fs
				.readFileSync( styleCss, 'utf8' )
				.replace( /(^\s*\*?\s*Version:\s*).*$/m, `$1${ version }` );
			fs.writeFileSync( styleCss, css, 'utf8' );
		}
	} catch ( error ) {
		console.log( style.error( `Error while setting version in style.css: ${ error.message }` ) );
	}

	const packageJson = path.resolve( getRoot(), 'package.json' );
	try {
		if ( fs.existsSync( packageJson ) ) {
			const json = fs
				.readFileSync( packageJson, 'utf8' )
				.replace( /("version"\s*:\s*")[^"]*"/, `$1${ version }"` );
			fs.writeFileSync( packageJson, json, 'utf8' );
		}
	} catch ( error ) {
		console.log( style.error( `Error while setting version in package.json: ${ error.message }` ) );
	}
};

/**
 * Persist the canonical project identity to `.wp-tooling.json`. This is the
 * single source of truth future scaffolds reuse via `discover_from: config:<key>`
 * (namespace is also discoverable from composer.json). The `features` block is
 * owned by the feature manager.
 *
 * @param {Object} fields Source fields.
 * @return {void}
 */
const writeProjectConfig = ( fields ) => {
	const id = resolveIdentity( fields );
	const config = {
		name: id.themeName,
		version: id.version,
		textDomain: id.textDomain,
		namespace: id.namespace,
		packageName: id.packageName,
		functionPrefix: id.functionPrefix,
		cssClassPrefix: id.cssClassPrefix,
		constantPrefix: id.constantPrefix,
		features: {},
	};

	try {
		fs.writeFileSync(
			path.resolve( getRoot(), CONFIG_FILE ),
			JSON.stringify( config, null, 2 ) + '\n',
			'utf8',
		);
		console.log( style.success( `Saved project config to ${ CONFIG_FILE }` ), '✨' );
	} catch ( error ) {
		console.log( style.error( `Error while writing ${ CONFIG_FILE }: ${ error.message }` ) );
	}
};

/**
 * Run the wp-tooling feature manager in-process (same TTY UI session as init).
 *
 * @param {Object}  options         Options.
 * @param {boolean} options.install Whether the feature manager may run npm install.
 * @return {Promise<void>}
 */
const runFeatureManager = async ( { install = true } = {} ) => {
	try {
		const { runFeatures } = require( '@rtcamp/wp-tooling/features' );
		await runFeatures( { cwd: getRoot(), install } );
	} catch ( error ) {
		if ( error && error.name === 'CancelledError' ) {
			throw error; // handled by main()'s top-level catch
		}
		console.log( style.warning(
			'\nCould not run the feature manager. Run `npx wp-tooling features` later to manage features.\n',
		) );
	}
};

/**
 * Initialize git, install git hooks between init and the first commit.
 *
 * @return {Promise<void>}
 */
const initializeGit = async () => {
	const gitDir = path.resolve( getRoot(), '.git' );
	try {
		if ( fs.existsSync( gitDir ) ) {
			fs.rmSync( gitDir, { recursive: true } );
		}
	} catch ( error ) {
		// Ignore — git init below will surface any real problem.
	}

	const root = path.resolve( getRoot() );
	const s = spinner( 'Initializing git…' );
	s.start();
	try {
		execSync( `git init '${ root }'`, { stdio: 'pipe' } );
		isGitInitialized = true;
		s.succeed( 'Git initialized.' );

		await askHooks();

		execSync( `git add '${ root }'`, { stdio: 'pipe' } );
		// --no-verify so the commit-msg / pre-commit hooks do not block the first commit.
		execSync(
			"git commit -m 'Initialize project using https://github.com/rtCamp/theme-elementary' --no-verify",
			{ cwd: root, stdio: 'pipe' },
		);
	} catch ( error ) {
		s.fail( 'Error while initializing git. Please check the logs above.' );
	}
};

/**
 * Ask whether to install git hooks, then install them.
 *
 * @return {Promise<void>}
 */
const askHooks = async () => {
	if ( ! ( await confirm( { message: 'Would you like to install git hooks (pre-commit lint + commit-msg)?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nSkipping git hooks.\n' ) );
		return;
	}
	await installGitHooks();
};

/**
 * Install the wp-tooling git hooks (pre-commit runs `lint:staged`; commit-msg
 * validates Conventional Commits) into `.git/hooks`, and point `prepare` at
 * `wp-tooling install-hooks` so they reinstall after a fresh clone.
 *
 * @return {Promise<void>}
 */
const installGitHooks = async () => {
	const root = path.resolve( getRoot() );
	if ( ! fs.existsSync( path.resolve( root, '.git' ) ) ) {
		console.log( style.warning( '\nGit is not initialized. Please initialize git first.\n' ) );
		return;
	}

	const s = spinner( 'Installing git hooks…' );
	s.start();
	try {
		const { installHooks } = require( '@rtcamp/wp-tooling/hooks' );
		await installHooks( root, { force: true } );
		setPrepareScript( root );
		s.succeed( 'Git hooks installed (pre-commit + commit-msg).' );
	} catch ( error ) {
		s.fail( `Could not install git hooks: ${ error.message }` );
	}
};

/**
 * Point the `prepare` lifecycle script at `wp-tooling install-hooks` so hooks
 * reinstall after a fresh clone (`.git/hooks` is never committed). `|| true`
 * keeps `npm install` from failing where there is no git repo (CI / tarball).
 *
 * @param {string} root Theme root.
 * @return {void}
 */
const setPrepareScript = ( root ) => {
	const packageJsonPath = path.resolve( root, 'package.json' );
	try {
		const pkg = JSON.parse( fs.readFileSync( packageJsonPath, 'utf8' ) );
		pkg.scripts = pkg.scripts || {};
		pkg.scripts.prepare = 'wp-tooling install-hooks || true';
		fs.writeFileSync( packageJsonPath, JSON.stringify( pkg, null, 2 ), 'utf8' );
	} catch ( error ) {
		// Non-fatal: hooks are still installed for this clone.
	}
};

/**
 * Ask whether to run the theme cleanup, then run it.
 *
 * @return {Promise<void>}
 */
const themeCleanupFlow = async () => {
	if ( ! ( await confirm( { message: 'Would you like to run the theme cleanup?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nExiting without running theme cleanup.\n' ) );
		return;
	}
	updateComposerJson();
	updatePackageJson();
	runThemeCleanup();
};

/**
 * Remove the Composer post-install-cmd helper script.
 *
 * @return {void}
 */
const updateComposerJson = () => {
	const composerJsonPath = path.resolve( getRoot(), 'composer.json' );
	try {
		if ( ! fs.existsSync( composerJsonPath ) ) {
			return;
		}
		const composerJson = JSON.parse( fs.readFileSync( composerJsonPath, 'utf8' ) );
		if ( composerJson.scripts ) {
			delete composerJson.scripts[ 'post-install-cmd' ];
		}
		fs.writeFileSync( composerJsonPath, JSON.stringify( composerJson, null, 2 ), 'utf8' );
	} catch ( error ) {
		console.log( style.error( `Error while updating composer.json: ${ error.message }` ) );
	}
};

/**
 * Drop the `prepare` auto-trigger (so `npm install` no longer re-runs init) but
 * keep the `init` script so the feature manager stays re-runnable.
 *
 * @return {void}
 */
const updatePackageJson = () => {
	const packageJsonPath = path.resolve( getRoot(), 'package.json' );
	try {
		if ( ! fs.existsSync( packageJsonPath ) ) {
			return;
		}
		const packageJson = JSON.parse( fs.readFileSync( packageJsonPath, 'utf8' ) );
		const prepare = packageJson.scripts && packageJson.scripts.prepare;
		if ( ! prepare || ! prepare.includes( 'npm run init' ) ) {
			return;
		}
		const remaining = prepare
			.split( '&&' )
			.map( ( script ) => script.trim() )
			.filter( ( script ) => script !== 'npm run init' );

		if ( remaining.length === 0 ) {
			delete packageJson.scripts.prepare;
		} else {
			packageJson.scripts.prepare = remaining.join( ' && ' );
		}
		fs.writeFileSync( packageJsonPath, JSON.stringify( packageJson, null, 2 ), 'utf8' );
	} catch ( error ) {
		console.log( style.error( `Error while updating package.json: ${ error.message }` ) );
	}
};

/**
 * Delete first-run-only files. Keeps `bin/init.js` and `.wp-tooling.json` so the
 * feature manager remains re-runnable.
 *
 * @return {void}
 */
const runThemeCleanup = () => {
	const toRemove = [ '.github', 'languages' ];
	if ( ! isGitInitialized ) {
		toRemove.push( '.git' );
	}

	let removed = 0;
	for ( const entry of toRemove ) {
		const target = path.resolve( getRoot(), entry );
		try {
			if ( fs.existsSync( target ) ) {
				fs.rmSync( target, { recursive: true } );
				removed++;
			}
		} catch ( error ) {
			console.log( style.error( `\nError removing ${ entry }: ${ error.message }` ) );
		}
	}

	console.log(
		removed > 0
			? style.success( '\nTheme cleanup completed!' ) + ' ✨'
			: style.warning( '\nNothing to clean up.\n' ),
	);
};

/**
 * Recursively list project files, skipping VCS/build/dependency directories.
 *
 * @param {string} dir Directory to scan.
 * @return {string[]} Absolute file paths.
 */
const getAllFiles = ( dir ) => {
	const ignore = [ '.git', '.github', 'node_modules', 'vendor' ];
	const out = [];
	for ( const entry of fs.readdirSync( dir ) ) {
		if ( ignore.includes( entry ) ) {
			continue;
		}
		const full = path.join( dir, entry );
		if ( fs.statSync( full ).isDirectory() ) {
			out.push( ...getAllFiles( full ) );
		} else {
			out.push( full );
		}
	}
	return out;
};

main().catch( ( error ) => {
	if ( error && error.name === 'CancelledError' ) {
		console.log( style.warning( '\nSetup cancelled.\n' ) );
		process.exit( 130 );
	}
	console.log( style.error( `\nError: ${ error && error.message ? error.message : error }` ) );
	process.exit( 1 );
} );
