#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * Theme setup / feature manager.
 *
 * First run (no `.wp-tooling.json`): rename the starter theme, persist the
 * choices, pick optional features, and optionally set up git + git hooks.
 * Later runs jump straight to the feature manager, so features can be toggled
 * any time with `npm run init`.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { execSync } = require( 'child_process' );
const { text, confirm, checkbox, spinner, style } = require( '@rtcamp/wp-tooling/ui' );

const ROOT = path.resolve( __dirname, '..' );

// Presence of the config flips init from scaffold mode to feature-manager mode.
const CONFIG_FILE = '.wp-tooling.json';

/**
 * Entry point.
 *
 * @return {Promise<void>}
 */
const main = async () => {
	const args = process.argv.slice( 2 );

	if ( args.includes( '--clean' ) || args.includes( '-c' ) ) {
		// Standalone clean never touches `.git` — only the scaffold flow may
		// remove the starter clone's repo (and its git prompt says so).
		await themeCleanupFlow( { keepGit: true } );
		return;
	}

	if ( args.length > 0 ) {
		console.log( style.error( '\nInvalid arguments.\n' ) );
		process.exitCode = 1;
		return;
	}

	if ( fs.existsSync( path.resolve( ROOT, CONFIG_FILE ) ) ) {
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
	renderHeader();

	if ( ! ( await confirm( { message: 'Would you like to set up the theme?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nTheme setup cancelled.\n' ) );
		return;
	}

	const themeName = await text( {
		message: 'Enter theme name (shown in WordPress admin)',
		validate: validThemeName,
	} );

	// Review loop: declining the table opens a multi-select editor, then
	// re-confirms. No cascade: editing one field never rewrites another.
	let fields = defaultFields( themeName.trim() );
	while ( true ) {
		renderThemeDetails( fields );
		if ( await confirm( { message: 'Confirm the theme details?', defaultValue: true } ) ) {
			break;
		}
		fields = await editFields( fields );
	}

	const counts = applyThemeName( fields );
	applyVersion( fields.version );
	writeProjectConfig( fields );
	// The theme is initialized now — drop the `prepare` auto-trigger so a later
	// `npm install` cannot re-open init, even if the cleanup below is declined.
	updateJsonFile( 'package.json', stripInitFromPrepare );

	console.log( style.bold( '\n  Optional features' ) );
	// Init usually runs inside `npm install` (the prepare hook), so the feature
	// manager records new deps in package.json instead of nesting an install.
	await runFeatureManager( { install: false } );

	let keepGit = false;
	if ( await confirm( {
		message: 'Initialize git? (Note: deletes any `.git` folder already in this directory)',
		defaultValue: false,
	} ) ) {
		keepGit = await initializeGit();
	} else {
		console.log( style.warning( '\nSkipping git initialization (and git hooks).' ) );
	}

	await themeCleanupFlow( { keepGit } );

	renderSummary( fields, { ...counts, keepGit } );
};

/**
 * Print the setup banner.
 *
 * @return {void}
 */
const renderHeader = () => {
	console.log( style.bold( '\n  Theme setup' ) );
	console.log( style.muted( '  Name your theme and pick optional features. Ctrl+C cancels.' ) );
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

/**
 * Collapse doubled backslashes so a namespace pasted from composer.json
 * (`rtCamp\\Theme\\X`) behaves the same as one typed plainly, and strip
 * leading/trailing separators that would produce invalid PHP.
 *
 * @param {string} value
 * @return {string} Normalised namespace.
 */
const normalizeNamespace = ( value ) =>
	String( value )
		.trim()
		.replace( /\\+/g, '\\' )
		.replace( /^\\|\\$/g, '' );

/*
 * Per-field validators for text() prompts — each returns an error message or
 * undefined. They guard the generated code: the theme name is substituted into
 * single-quoted PHP strings (so no quotes/backslashes), a digit-leading prefix
 * or text domain would produce invalid PHP/CSS identifiers, and a malformed
 * namespace or package name corrupts composer.json. Validation runs on the raw
 * input; derived values go through the same normalizers the replacements use.
 */
const validThemeName = ( value ) => {
	const v = String( value ).trim();
	if ( ! v ) {
		return 'This field is required.';
	}
	if ( ! /^[A-Za-z]/.test( v ) ) {
		return 'Start with a letter so the derived PHP/CSS identifiers stay valid.';
	}
	return /['"\\]/.test( v )
		? 'Quotes and backslashes are not allowed — the name is written into PHP strings.'
		: undefined;
};

const validVersion = ( value ) =>
	/^\d+\.\d+\.\d+(-[0-9A-Za-z.-]+)?(\+[0-9A-Za-z.-]+)?$/.test( String( value ).trim() )
		? undefined
		: 'Use a semantic version like 1.0.0.';

const validTextDomain = ( value ) =>
	/^[a-z][a-z0-9-]*$/.test( toKebab( value ) )
		? undefined
		: 'Must produce a kebab-case slug starting with a letter (e.g. my-theme).';

const validNamespace = ( value ) =>
	/^[A-Za-z_][A-Za-z0-9_]*(\\[A-Za-z_][A-Za-z0-9_]*)*$/.test( normalizeNamespace( value ) )
		? undefined
		: 'Use letters, numbers and underscores separated by \\ (e.g. rtCamp\\Theme\\MyTheme).';

const validFunctionPrefix = ( value ) =>
	/^[a-z][a-z0-9_]*$/.test( toSnake( value ) )
		? undefined
		: 'Must produce a snake_case prefix starting with a letter (e.g. my_theme).';

const validPackageName = ( value ) =>
	/^[a-z0-9]([a-z0-9._-]*[a-z0-9])?\/[a-z0-9]([a-z0-9._-]*[a-z0-9])?$/.test( String( value ).trim() )
		? undefined
		: 'Use the composer vendor/name format (lowercase, e.g. rtcamp/my-theme).';

/**
 * Editable source fields, defaulted from the theme name. Everything else
 * (constants, CSS prefix, case variants) is derived from these.
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
 * Canonical identity derived from the (possibly edited) fields. The details
 * table, the search-replace, and the saved config all go through this.
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
		namespace: normalizeNamespace( f.namespace ),
		packageName: f.packageName.trim(),
		functionPrefix: `${ prefix }_`,
		cssClassPrefix: `${ textDomain }-`,
		constantPrefix: prefix.toUpperCase(),
	};
};

// Editable source fields: [ key, label, prompt, normalize, validate ]. Text
// domain and prefix are normalised to kebab/snake so identifiers stay valid.
const FIELD_META = [
	[ 'themeName', 'Theme Name', 'Theme name', ( v ) => v.trim(), validThemeName ],
	[ 'version', 'Version', 'Theme version', ( v ) => v.trim(), validVersion ],
	[ 'textDomain', 'Text Domain', 'Text domain', toKebab, validTextDomain ],
	[ 'namespace', 'Namespace', 'PHP namespace', ( v ) => v.trim(), validNamespace ],
	[ 'functionPrefix', 'Function Prefix', 'Function / constant prefix', toSnake, validFunctionPrefix ],
	[ 'packageName', 'Package', 'Composer package name', ( v ) => v.trim(), validPackageName ],
];

/**
 * Field editor: multi-select the fields to change, then edit each chosen one in
 * turn (defaulting to its current value). The caller re-renders the details
 * table and re-confirms, so selecting nothing is a safe "never mind". Editing
 * one field never rewrites another.
 *
 * @param {Object} fields Current source fields.
 * @return {Promise<Object>} Updated source fields.
 */
const editFields = async ( fields ) => {
	const f = { ...fields };
	// Map each menu label back to its field meta up front (before any edit
	// changes a value and with it the label).
	const byLabel = new Map(
		FIELD_META.map( ( meta ) => [ `${ meta[ 1 ].padEnd( 16 ) }${ f[ meta[ 0 ] ] }`, meta ] ),
	);
	const picked = await checkbox( {
		message: 'Select the fields to edit',
		choices: [ ...byLabel.keys() ],
	} );
	const chosen = new Set( picked.map( ( label ) => byLabel.get( label )[ 0 ] ) );

	// Edit in canonical order for a predictable prompt sequence.
	for ( const [ key, , prompt, normalize, validate ] of FIELD_META ) {
		if ( ! chosen.has( key ) ) {
			continue;
		}
		f[ key ] = normalize(
			await text( { message: prompt, defaultValue: f[ key ], validate } ),
		);
	}
	return f;
};

/**
 * Ordered [search, replacement] pairs applied literally (split/join, so the
 * back-slashed namespace needs no escaping). Compound tokens come before bare
 * ones; specific bare-"elementary" identifiers are listed explicitly so repo
 * URLs and elementary.local examples stay untouched.
 *
 * @param {Object} f Source fields.
 * @return {Array<[string, string]>} Replacement pairs.
 */
const deriveReplacements = ( f ) => {
	const textDomain = toKebab( f.textDomain );
	const prefix = toSnake( f.functionPrefix );
	const namespace = normalizeNamespace( f.namespace );
	return [
		// composer.json stores the namespace JSON-escaped — cover both forms.
		[ 'rtCamp\\Theme\\Elementary', namespace ],
		[ 'rtCamp\\\\Theme\\\\Elementary', namespace.replace( /\\/g, '\\\\' ) ],
		[ 'rtcamp/elementary', f.packageName.trim() ],
		[ 'elementary theme', f.themeName.toLowerCase() ],
		[ 'Elementary Theme', f.themeName ],
		[ 'ELEMENTARY THEME', f.themeName.toUpperCase() ],
		[ 'elementary-theme', textDomain ],
		[ 'Elementary-Theme', toTrain( textDomain ) ],
		[ 'ELEMENTARY-THEME', toCobol( textDomain ) ],
		[ 'elementary_theme', prefix ],
		[ 'Elementary_Theme', toPascalSnake( prefix ) ],
		[ 'ELEMENTARY_THEME', prefix.toUpperCase() ],
		[ 'Theme Elementary', f.themeName ],
		[ 'elementary-media-text-interactive', `${ textDomain }-media-text-interactive` ],
		[ 'elementary/media-text', `${ textDomain }/media-text` ],
		[ 'elementary-featured', `${ textDomain }-featured` ],
		[ 'elementary-browser-sync', `${ textDomain }-browser-sync` ],
		[ 'elementary-settings', `${ textDomain }-settings` ],
		[ 'elementary_main_section', `${ prefix }_main_section` ],
		[ 'elementary_', `${ prefix }_` ],
		[ 'themes/elementary', `themes/${ textDomain }` ],
		[ 'Elementary', f.themeName ], // bare label — keep last
	];
};

/**
 * Render the theme details box for confirmation. Two aligned columns, split
 * into the identity you set and the values derived from it (dimmed). Values
 * come from {@link resolveIdentity}, so the box shows exactly what is applied.
 *
 * @param {Object} fields Source fields.
 * @return {void}
 */
const renderThemeDetails = ( fields ) => {
	const id = resolveIdentity( fields );
	const identity = [
		[ 'Theme Name', id.themeName ],
		[ 'Version', id.version ],
		[ 'Text Domain', id.textDomain ],
		[ 'Package', id.packageName ],
		[ 'Namespace', id.namespace ],
		[ 'Function Prefix', id.functionPrefix ],
	];
	// Purely computed from the identity above — shown dimmed, never set directly.
	const derived = [
		[ 'CSS Class Prefix', id.cssClassPrefix ],
		[ 'Constants', `${ id.constantPrefix }_{VERSION,BUILD_DIR,BUILD_URI}` ],
	];

	const PAD = 2; // padding inside the left border
	const GAP = 2; // between the label and value columns
	const all = [ ...identity, ...derived ];
	const labelW = Math.max( ...all.map( ( [ label ] ) => label.length ) );
	const valueW = Math.max( ...all.map( ( [ , value ] ) => value.length ) );
	const inner = PAD + labelW + GAP + valueW + 1; // +1 trailing space

	// Build a row, computing the right-pad from the raw (un-styled) text so
	// ANSI codes never throw off the border alignment.
	const row = ( label, value, dim ) => {
		const raw = ' '.repeat( PAD ) + label.padEnd( labelW ) + ' '.repeat( GAP ) + value;
		const body = dim
			? style.muted( raw )
			: ' '.repeat( PAD ) + style.success( label.padEnd( labelW ) ) + ' '.repeat( GAP ) + style.info( value );
		return style.muted( '│' ) + body + ' '.repeat( inner - raw.length ) + style.muted( '│' );
	};
	const rule = ( left, right ) => style.muted( '  ' + left + '─'.repeat( inner ) + right );

	console.log( style.bold( '\n  Theme details' ) );
	console.log( rule( '┌', '┐' ) );
	for ( const [ label, value ] of identity ) {
		console.log( '  ' + row( label, value, false ) );
	}
	console.log( rule( '├', '┤' ) );
	for ( const [ label, value ] of derived ) {
		console.log( '  ' + row( label, value, true ) );
	}
	console.log( rule( '└', '┘' ) );
};

// Contents of these are never search-replaced — decoding binary data as UTF-8
// and writing it back corrupts it. Their *names* still go through the rename
// pass (e.g. elementary-theme-logo.png).
const BINARY_EXTENSIONS = new Set( [
	'.png', '.jpg', '.jpeg', '.gif', '.webp', '.ico',
	'.woff', '.woff2', '.ttf', '.eot', '.otf',
	'.mo', '.zip', '.gz', '.jar', '.pdf',
] );

/**
 * Apply the identity across all project files — contents first, then
 * filenames carrying a token (e.g. elementary-theme.pot) — and regenerate the
 * Composer autoloader for the new namespace.
 *
 * @param {Object} fields Source fields.
 * @return {{filesChanged: number, filesRenamed: number}} Counts for the summary.
 */
const applyThemeName = ( fields ) => {
	const replacements = deriveReplacements( fields );
	const replaceAll = ( value ) =>
		replacements.reduce( ( out, [ search, replacement ] ) => out.split( search ).join( replacement ), value );
	// Skip this script itself: it holds the search tokens verbatim (in
	// deriveReplacements), and it is kept after scaffolding, so rewriting it
	// would corrupt its own replacement table.
	const files = getAllFiles( ROOT ).filter( ( file ) => file !== __filename );

	const s = spinner( 'Applying your theme name across the project…' );
	s.start();

	let filesChanged = 0;
	let filesRenamed = 0;

	try {
		for ( const file of files ) {
			if ( BINARY_EXTENSIONS.has( path.extname( file ).toLowerCase() ) ) {
				continue;
			}
			const original = fs.readFileSync( file, 'utf8' );
			const updated = replaceAll( original );
			if ( updated !== original ) {
				fs.writeFileSync( file, updated, 'utf8' );
				filesChanged++;
			}
		}

		for ( const file of files ) {
			const base = path.basename( file );
			const nextBase = replaceAll( base );
			if ( nextBase !== base ) {
				fs.renameSync( file, path.join( path.dirname( file ), nextBase ) );
				filesRenamed++;
			}
		}

		s.succeed( `Theme name applied — ${ filesChanged } file(s) updated, ${ filesRenamed } renamed.` );
	} catch ( error ) {
		s.fail( `Failed to apply theme name: ${ error.message }` );
		throw error;
	}

	const dump = spinner( 'Regenerating Composer autoloader…' );
	dump.start();
	try {
		execSync( 'composer dump-autoload', { cwd: ROOT, stdio: 'pipe' } );
		dump.succeed( 'Composer autoloader regenerated.' );
	} catch {
		dump.fail( 'Could not run `composer dump-autoload` — run it manually after installing dependencies.' );
	}

	return { filesChanged, filesRenamed };
};

/**
 * Apply the chosen version to style.css and package.json with surgical regex
 * edits — the version is not a search-replace token.
 *
 * @param {string} version
 * @return {void}
 */
const applyVersion = ( version ) => {
	replaceInFile( 'style.css', /(^\s*\*?\s*Version:\s*).*$/m, `$1${ version }` );
	replaceInFile( 'package.json', /("version"\s*:\s*")[^"]*"/, `$1${ version }"` );
};

/**
 * Persist the identity that scaffolds reuse via `discover_from: "config:<key>"`.
 * The `features` block is owned by the wp-tooling feature manager.
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
		cssDir: 'src/css/frontend',
		features: {},
	};

	try {
		fs.writeFileSync(
			path.resolve( ROOT, CONFIG_FILE ),
			JSON.stringify( config, null, '\t' ) + '\n',
			'utf8',
		);
		console.log( style.success( `Saved project config to ${ CONFIG_FILE }` ), '✨' );
	} catch ( error ) {
		console.log( style.error( `Error while writing ${ CONFIG_FILE }: ${ error.message }` ) );
	}
};

/**
 * Names of the features enabled in `.wp-tooling.json` (for the summary).
 *
 * @return {string[]} Enabled feature keys, or an empty array.
 */
const enabledFeatures = () => {
	try {
		const cfg = JSON.parse( fs.readFileSync( path.resolve( ROOT, CONFIG_FILE ), 'utf8' ) );
		return Object.entries( cfg.features || {} ).filter( ( [ , on ] ) => on ).map( ( [ name ] ) => name );
	} catch {
		return [];
	}
};

/**
 * Print the closing recap: what was applied, plus the next steps.
 *
 * @param {Object}  fields              Source fields.
 * @param {Object}  meta                Summary metadata.
 * @param {number}  [meta.filesChanged] Files whose contents changed.
 * @param {number}  [meta.filesRenamed] Files renamed.
 * @param {boolean} [meta.keepGit]      Whether a git repo was initialized.
 * @return {void}
 */
const renderSummary = ( fields, { filesChanged = 0, filesRenamed = 0, keepGit = false } = {} ) => {
	const id = resolveIdentity( fields );
	const features = enabledFeatures();
	const rows = [
		[ 'Name', id.themeName ],
		[ 'Namespace', id.namespace ],
		[ 'Files', `${ filesChanged } updated · ${ filesRenamed } renamed` ],
		[ 'Features', features.length ? features.join( ', ' ) : 'none' ],
		[ 'Git', keepGit ? 'initialized' : 'skipped' ],
	];
	const width = Math.max( ...rows.map( ( [ label ] ) => label.length ) );

	console.log( style.bold( '\n  ✓ Setup complete' ) );
	for ( const [ label, value ] of rows ) {
		console.log( '    ' + style.muted( label.padEnd( width ) ) + '  ' + value );
	}
	console.log(
		style.success( '\n  Next: ' ) + 'run ' + style.warning( '`npm install`' ) +
		' to install feature deps, then ' + style.warning( '`npm start`' ) + '.',
	);
	console.log( style.muted( '  Re-run `npm run init` any time to toggle features on or off.\n' ) );
};

/**
 * Run the wp-tooling feature manager in-process (same TTY session as init).
 * When install is skipped (init already runs inside `npm install`), opt in to
 * recording new deps in package.json so the next `npm install` picks them up.
 *
 * @param {Object}  options         Options.
 * @param {boolean} options.install Run npm install for newly enabled features.
 * @return {Promise<void>}
 */
const runFeatureManager = async ( { install = true } = {} ) => {
	try {
		const { runFeatures } = require( '@rtcamp/wp-tooling/features' );
		await runFeatures( { cwd: ROOT, install, record: ! install } );
	} catch ( error ) {
		if ( error && error.name === 'CancelledError' ) {
			throw error; // handled by main()'s top-level catch
		}
		console.log( style.warning(
			`\nCould not run the feature manager: ${ error && error.message ? error.message : error }`,
		) );
		console.log( style.warning(
			'Run `npx wp-tooling features` later to manage features.\n',
		) );
	}
};

/**
 * Initialize a fresh git repo, install hooks, and create the first commit.
 *
 * @return {Promise<boolean>} True when a repo was created (so cleanup keeps it).
 */
const initializeGit = async () => {
	try {
		fs.rmSync( path.resolve( ROOT, '.git' ), { recursive: true, force: true } );
	} catch {
		// git init below will surface any real problem.
	}

	let initialized = false;
	const s = spinner( 'Initializing git…' );
	s.start();
	try {
		execSync( 'git init', { cwd: ROOT, stdio: 'pipe' } );
		initialized = true;
		s.succeed( 'Git initialized.' );

		await installGitHooks();

		execSync( 'git add -A', { cwd: ROOT, stdio: 'pipe' } );
		// --no-verify: the freshly installed hooks must not block this commit.
		execSync(
			"git commit -m 'Initialize project using https://github.com/rtCamp/theme-elementary' --no-verify",
			{ cwd: ROOT, stdio: 'pipe' },
		);
	} catch {
		s.fail( 'Error while initializing git. Please check the logs above.' );
	}
	return initialized;
};

/**
 * Offer and install the wp-tooling git hooks (pre-commit lint + commit-msg),
 * pointing `prepare` at `wp-tooling install-hooks` so they reinstall after a
 * fresh clone (`.git/hooks` is never committed).
 *
 * @return {Promise<void>}
 */
const installGitHooks = async () => {
	if ( ! ( await confirm( { message: 'Would you like to install git hooks (pre-commit lint + commit-msg)?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nSkipping git hooks.\n' ) );
		return;
	}

	const s = spinner( 'Installing git hooks…' );
	s.start();
	try {
		const { installHooks } = require( '@rtcamp/wp-tooling/hooks' );
		await installHooks( ROOT, { force: true } );
		// `|| true` keeps non-git installs (CI / tarball) green.
		updateJsonFile( 'package.json', ( pkg ) => {
			pkg.scripts = pkg.scripts || {};
			pkg.scripts.prepare = 'wp-tooling install-hooks || true';
		} );
		s.succeed( 'Git hooks installed (pre-commit + commit-msg).' );
	} catch ( error ) {
		s.fail( `Could not install git hooks: ${ error.message }` );
	}
};

/**
 * Offer and run the theme cleanup: drop the scaffold-only lifecycle scripts
 * and delete first-run-only files.
 *
 * @param {Object}  options         Options.
 * @param {boolean} options.keepGit Keep `.git` (a repo was created this run).
 * @return {Promise<void>}
 */
const themeCleanupFlow = async ( { keepGit = false } = {} ) => {
	if ( ! ( await confirm( { message: 'Would you like to run the theme cleanup?', defaultValue: true } ) ) ) {
		console.log( style.warning( '\nExiting without running theme cleanup.\n' ) );
		return;
	}

	updateJsonFile( 'composer.json', ( json ) => {
		if ( json.scripts ) {
			delete json.scripts[ 'post-install-cmd' ];
		}
	} );
	updateJsonFile( 'package.json', cleanupPackageJson );
	removeScaffoldFiles( keepGit );
};

/**
 * Drop `npm run init` from the `prepare` script so `npm install` stops
 * re-running init (the `init` script itself stays so the feature manager
 * remains re-runnable). Mutator for {@link updateJsonFile} — runs right after
 * the project config is written, and again as a fallback during cleanup.
 *
 * @param {Object} pkg Parsed package.json.
 * @return {boolean} False to skip writing when nothing changed.
 */
const stripInitFromPrepare = ( pkg ) => {
	const prepare = pkg.scripts && pkg.scripts.prepare;
	if ( ! prepare || ! prepare.includes( 'npm run init' ) ) {
		return false;
	}
	const remaining = prepare
		.split( '&&' )
		.map( ( script ) => script.trim() )
		.filter( ( script ) => script !== 'npm run init' );
	if ( remaining.length === 0 ) {
		delete pkg.scripts.prepare;
	} else {
		pkg.scripts.prepare = remaining.join( ' && ' );
	}
	return true;
};

/**
 * Cleanup mutations for package.json: strip the init auto-trigger (fallback —
 * normally already done post-init) and remove repository/bugs/homepage entries
 * still pointing at the starter repo — they belong to theme-elementary, not
 * the scaffolded theme.
 *
 * @param {Object} pkg Parsed package.json.
 * @return {boolean} False to skip writing when nothing changed.
 */
const cleanupPackageJson = ( pkg ) => {
	let changed = stripInitFromPrepare( pkg );

	for ( const key of [ 'repository', 'bugs', 'homepage' ] ) {
		if ( JSON.stringify( pkg[ key ] || '' ).includes( 'theme-elementary' ) ) {
			delete pkg[ key ];
			changed = true;
		}
	}

	return changed;
};

/**
 * Delete first-run-only files. Keeps `bin/init.js` and `.wp-tooling.json` so
 * the feature manager remains re-runnable.
 *
 * @param {boolean} keepGit Keep `.git` (a repo was created this run).
 * @return {void}
 */
const removeScaffoldFiles = ( keepGit ) => {
	const toRemove = [ '.github', 'languages', ...( keepGit ? [] : [ '.git' ] ) ];

	let removed = 0;
	for ( const entry of toRemove ) {
		const target = path.resolve( ROOT, entry );
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
 * Edit a JSON file in place, tab-indented like the rest of the repo.
 *
 * @param {string}   file   Path relative to the theme root.
 * @param {Function} mutate Receives the parsed object; return false to skip writing.
 * @return {void}
 */
const updateJsonFile = ( file, mutate ) => {
	const filePath = path.resolve( ROOT, file );
	try {
		if ( ! fs.existsSync( filePath ) ) {
			return;
		}
		const json = JSON.parse( fs.readFileSync( filePath, 'utf8' ) );
		if ( mutate( json ) === false ) {
			return;
		}
		fs.writeFileSync( filePath, JSON.stringify( json, null, '\t' ) + '\n', 'utf8' );
	} catch ( error ) {
		console.log( style.error( `Error while updating ${ file }: ${ error.message }` ) );
	}
};

/**
 * Apply a single regex replacement to a file, leaving the rest untouched.
 *
 * @param {string} file        Path relative to the theme root.
 * @param {RegExp} pattern     Pattern to replace.
 * @param {string} replacement Replacement string.
 * @return {void}
 */
const replaceInFile = ( file, pattern, replacement ) => {
	const filePath = path.resolve( ROOT, file );
	try {
		if ( ! fs.existsSync( filePath ) ) {
			return;
		}
		const updated = fs.readFileSync( filePath, 'utf8' ).replace( pattern, replacement );
		fs.writeFileSync( filePath, updated, 'utf8' );
	} catch ( error ) {
		console.log( style.error( `Error while updating ${ file }: ${ error.message }` ) );
	}
};

/**
 * Recursively list project files, skipping VCS/dependency directories.
 * Symlinks are skipped so a broken link cannot abort the rename pass mid-way.
 *
 * @param {string} dir Directory to scan.
 * @return {string[]} Absolute file paths.
 */
const getAllFiles = ( dir ) => {
	const ignore = [ '.git', '.github', 'node_modules', 'vendor' ];
	const out = [];
	for ( const entry of fs.readdirSync( dir, { withFileTypes: true } ) ) {
		if ( ignore.includes( entry.name ) || entry.isSymbolicLink() ) {
			continue;
		}
		const full = path.join( dir, entry.name );
		if ( entry.isDirectory() ) {
			out.push( ...getAllFiles( full ) );
		} else if ( entry.isFile() ) {
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
