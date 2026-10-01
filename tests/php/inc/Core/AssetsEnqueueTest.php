<?php
/**
 * Test the Assets enqueue paths: block-specific assets, Tailwind and the
 * BrowserSync client.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Core\Assets;
use rtCamp\Theme\Elementary\Tests\TestCase;

/**
 * Class AssetsEnqueueTest
 *
 * Every test runs against fresh script/style registries so enqueue state never
 * leaks between tests. BrowserSync tests read a throwaway env file through an
 * Assets subclass, never the developer's real .env.local.
 *
 * @since 1.0.0
 */
class AssetsEnqueueTest extends TestCase {

	/**
	 * Built files written for a test, removed in tear_down().
	 *
	 * @var string[]
	 */
	private array $fixture_files = [];

	/**
	 * Throwaway env file read instead of .env.local.
	 *
	 * @var string
	 */
	private string $env_file = '';

	/**
	 * Registries in place before the test.
	 *
	 * @var array{0: mixed, 1: mixed}
	 */
	private array $saved_registries = [];

	/**
	 * Swap in empty script/style registries and create the env file.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->saved_registries = [ $GLOBALS['wp_scripts'] ?? null, $GLOBALS['wp_styles'] ?? null ];
		$GLOBALS['wp_scripts']  = new WP_Scripts();
		$GLOBALS['wp_styles']   = new WP_Styles();

		$this->env_file = (string) tempnam( sys_get_temp_dir(), 'elementary-assets-env-' );
	}

	/**
	 * Restore registries and remove fixtures.
	 */
	public function tear_down(): void {
		[ $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] ] = $this->saved_registries;

		foreach ( $this->fixture_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		$this->fixture_files = [];

		if ( '' !== $this->env_file && file_exists( $this->env_file ) ) {
			unlink( $this->env_file );
		}

		remove_all_filters( 'elementary_theme_tailwind_enabled' );

		parent::tear_down();
	}

	/**
	 * Navigation markup pulls in the navigation script and style.
	 */
	public function test_navigation_block_enqueues_its_assets(): void {
		$assets = $this->registered_assets();

		$markup = '<nav class="wp-block-navigation"></nav>';
		$result = $assets->enqueue_block_specific_assets( $markup, [ 'blockName' => 'core/navigation' ] );

		$this->assertSame( $markup, $result );
		$this->assertTrue( wp_script_is( 'elementary-theme-core-navigation', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'elementary-theme-core-navigation', 'enqueued' ) );
	}

	/**
	 * Other blocks, and blocks without a name, enqueue nothing.
	 */
	public function test_other_blocks_enqueue_nothing(): void {
		$assets = $this->registered_assets();

		$this->assertSame( '<p>x</p>', $assets->enqueue_block_specific_assets( '<p>x</p>', [ 'blockName' => 'core/paragraph' ] ) );
		$this->assertSame( 'raw', $assets->enqueue_block_specific_assets( 'raw', [ 'blockName' => null ] ) );
		$this->assertSame( 'raw', $assets->enqueue_block_specific_assets( 'raw', [] ) );

		$this->assertFalse( wp_script_is( 'elementary-theme-core-navigation', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'elementary-theme-core-navigation', 'enqueued' ) );
	}

	/**
	 * The main stylesheet is always enqueued; Tailwind only when enabled.
	 */
	public function test_enqueue_assets_adds_tailwind_only_when_enabled(): void {
		$assets = $this->registered_assets();

		$assets->enqueue_assets();
		$this->assertTrue( wp_style_is( 'elementary-theme-styles', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'elementary-theme-tailwind', 'registered' ) );

		add_filter( 'elementary_theme_tailwind_enabled', '__return_true' );
		$assets->register_assets();
		$assets->enqueue_assets();

		$this->assertTrue( wp_style_is( 'elementary-theme-tailwind', 'enqueued' ) );
	}

	/**
	 * With HMR on (no flags set) the client loads from the site host on port 3001.
	 */
	public function test_browser_sync_client_defaults_to_port_3001(): void {
		$this->require_local_environment();

		$this->browser_sync_assets( '' )->enqueue_browser_sync();

		$this->assertSame( $this->expected_client_url( 3001 ), $this->browser_sync_src() );
	}

	/**
	 * BS_PORT moves the client to the configured port.
	 */
	public function test_browser_sync_client_uses_bs_port(): void {
		$this->require_local_environment();

		$this->browser_sync_assets( "ENABLE_HMR=true\nBS_PORT=4123\n" )->enqueue_browser_sync();

		$this->assertSame( $this->expected_client_url( 4123 ), $this->browser_sync_src() );
	}

	/**
	 * Invalid BS_PORT values fall back to 3001 instead of producing a broken URL.
	 *
	 * @dataProvider invalid_ports
	 *
	 * @param string $value BS_PORT value.
	 */
	public function test_browser_sync_client_ignores_invalid_ports( string $value ): void {
		$this->require_local_environment();

		$this->browser_sync_assets( "BS_PORT={$value}\n" )->enqueue_browser_sync();

		$this->assertSame( $this->expected_client_url( 3001 ), $this->browser_sync_src() );
	}

	/**
	 * Invalid BS_PORT values.
	 *
	 * @return array<string, array{0: string}> Cases.
	 */
	public function invalid_ports(): array {
		return [
			'zero'          => [ '0' ],
			'out of range'  => [ '70000' ],
			'not a number'  => [ 'abc' ],
			'trailing junk' => [ '3002abc' ],
		];
	}

	/**
	 * ENABLE_HMR off or DISABLE_BS on keeps the client out.
	 *
	 * @dataProvider client_off_flags
	 *
	 * @param string $env Env file contents.
	 */
	public function test_browser_sync_client_respects_off_flags( string $env ): void {
		$this->browser_sync_assets( $env )->enqueue_browser_sync();

		$this->assertFalse( wp_script_is( 'elementary-theme-browser-sync', 'enqueued' ) );
	}

	/**
	 * Env contents that keep the BrowserSync client out.
	 *
	 * @return array<string, array{0: string}> Cases.
	 */
	public function client_off_flags(): array {
		return [
			'hmr off'     => [ "ENABLE_HMR=false\n" ],
			'bs disabled' => [ "ENABLE_HMR=true\nDISABLE_BS=yes\n" ],
		];
	}

	/**
	 * Without an override the env file is .env.local in the theme root.
	 */
	public function test_env_file_defaults_to_theme_env_local(): void {
		$method = new ReflectionMethod( Assets::class, 'env_file_path' );
		$method->setAccessible( true );

		$this->assertSame(
			wp_normalize_path( trailingslashit( ELEMENTARY_THEME_PATH ) . '.env.local' ),
			wp_normalize_path( (string) $method->invoke( new Assets() ) )
		);
	}

	/**
	 * Register the theme's frontend assets against minimal built files.
	 *
	 * AssetLoader only registers a handle when its built file exists, and the
	 * PHP test job runs without a front-end build.
	 *
	 * @return Assets Assets with its handles registered.
	 */
	private function registered_assets(): Assets {
		foreach ( [ 'js/frontend/core-navigation.js', 'css/frontend/core-navigation.css', 'css/frontend/styles.css', 'css/frontend/tailwind.css' ] as $relative ) {
			$file = untrailingslashit( ELEMENTARY_THEME_PATH ) . '/assets/build/' . $relative;

			if ( ! file_exists( $file ) ) {
				wp_mkdir_p( dirname( $file ) );
				file_put_contents( $file, '/* test fixture */' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Test fixture.
				$this->fixture_files[] = $file;
			}
		}

		$assets = new Assets();
		$assets->register_assets();

		return $assets;
	}

	/**
	 * Assets reading the given env contents instead of .env.local.
	 *
	 * @param string $env Env file contents.
	 *
	 * @return Assets Assets subclass bound to the throwaway env file.
	 */
	private function browser_sync_assets( string $env ): Assets {
		file_put_contents( $this->env_file, $env ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Test fixture.

		return new class( $this->env_file ) extends Assets {

			/**
			 * Env file to read.
			 *
			 * @var string
			 */
			private string $test_env_file;

			/**
			 * Constructor.
			 *
			 * @param string $env_file Throwaway env file.
			 */
			public function __construct( string $env_file ) {
				$this->test_env_file = $env_file;

				parent::__construct();
			}

			/**
			 * {@inheritDoc}
			 */
			protected function env_file_path(): string {
				return $this->test_env_file;
			}
		};
	}

	/**
	 * The client only loads in the local environment type, which wp-env sets.
	 */
	private function require_local_environment(): void {
		if ( 'local' !== wp_get_environment_type() ) {
			$this->markTestSkipped( 'The BrowserSync client only loads in the local environment type.' );
		}
	}

	/**
	 * Client URL expected for the test site on the given port.
	 *
	 * @param int $port BrowserSync port.
	 *
	 * @return string URL.
	 */
	private function expected_client_url( int $port ): string {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$host = $host ? $host : 'localhost';

		return sprintf( '%s://%s:%d/browser-sync/browser-sync-client.js', is_ssl() ? 'https' : 'http', $host, $port );
	}

	/**
	 * Source URL of the enqueued BrowserSync client, or null.
	 *
	 * @return string|null Source URL.
	 */
	private function browser_sync_src(): ?string {
		$this->assertTrue( wp_script_is( 'elementary-theme-browser-sync', 'enqueued' ) );

		return wp_scripts()->registered['elementary-theme-browser-sync']->src ?? null;
	}
}
