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
} );
