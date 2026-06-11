/**
 * External dependencies
 */
const fs = require( 'fs' );
const os = require( 'os' );
const path = require( 'path' );

jest.mock( '@wordpress/scripts/config/webpack.config', () => [
	{
		optimization: {
			minimizer: [],
			splitChunks: {},
		},
		plugins: [],
		module: { rules: [] },
	},
	{
		output: {},
	},
] );

const { getComponentEntries } = require( '../../webpack.config' );

describe( 'webpack component entries', () => {
	let tmpDir;

	afterEach( () => {
		if ( tmpDir ) {
			fs.rmSync( tmpDir, { recursive: true, force: true } );
			tmpDir = undefined;
		}
	} );

	it( 'only matches files with the exact component basename', () => {
		tmpDir = fs.mkdtempSync( path.join( os.tmpdir(), 'elementary-webpack-components-' ) );
		const buttonDir = path.join( tmpDir, 'button' );

		fs.mkdirSync( buttonDir );
		fs.writeFileSync( path.join( buttonDir, 'button.js' ), '' );
		fs.writeFileSync( path.join( buttonDir, 'button-extra.js' ), '' );
		fs.writeFileSync( path.join( buttonDir, 'button.test.js' ), '' );
		fs.writeFileSync( path.join( buttonDir, 'button_utils.js' ), '' );

		expect( getComponentEntries( tmpDir, /\.js$/ ) ).toEqual( {
			'components/button': path.join( buttonDir, 'button.js' ),
		} );
	} );

	it( 'picks up .ts and .tsx component entries with the build pattern', () => {
		tmpDir = fs.mkdtempSync(
			path.join( os.tmpdir(), 'elementary-webpack-components-ts-' )
		);

		const cardDir = path.join( tmpDir, 'card' );
		const modalDir = path.join( tmpDir, 'modal' );
		const legacyDir = path.join( tmpDir, 'legacy' );

		[ cardDir, modalDir, legacyDir ].forEach( ( dir ) => fs.mkdirSync( dir ) );

		// One entry per supported extension.
		fs.writeFileSync( path.join( cardDir, 'card.ts' ), '' );
		fs.writeFileSync( path.join( modalDir, 'modal.tsx' ), '' );
		fs.writeFileSync( path.join( legacyDir, 'legacy.js' ), '' );

		// Same-folder files that must NOT become entries (basename mismatch),
		// even though their extensions match the widened pattern.
		fs.writeFileSync( path.join( cardDir, 'card.types.ts' ), '' );
		fs.writeFileSync( path.join( cardDir, 'card.test.tsx' ), '' );

		// Mirrors the pattern used by componentScripts in webpack.config.js.
		expect( getComponentEntries( tmpDir, /\.(jsx?|tsx?)$/ ) ).toEqual( {
			'components/card': path.join( cardDir, 'card.ts' ),
			'components/modal': path.join( modalDir, 'modal.tsx' ),
			'components/legacy': path.join( legacyDir, 'legacy.js' ),
		} );
	} );
} );
