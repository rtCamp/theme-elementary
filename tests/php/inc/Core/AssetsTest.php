<?php
/**
 * Test Assets class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Assets;

/**
 * Class AssetsTest
 *
 * @since 1.0.0
 */
class AssetsTest extends TestCase {

	/**
	 * Assets instance.
	 *
	 * @var Assets
	 */
	private Assets $instance;

	/**
	 * Build-asset fixture files created during a test, removed in tear_down().
	 *
	 * @var string[]
	 */
	private array $fixture_files = [];

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new Assets();
	}

	/**
	 * Remove any build-asset fixtures created during the test.
	 */
	public function tear_down(): void {
		foreach ( $this->fixture_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		$this->fixture_files = [];

		parent::tear_down();
	}

	/**
	 * Test class exists.
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( Assets::class ) );
	}

	/**
	 * Test class implements Registrable.
	 */
	public function test_implements_registrable(): void {
		$this->assertInstanceOf( 'rtCamp\WPFramework\Contracts\Interfaces\Registrable', $this->instance );
	}

	/**
	 * Test register_hooks adds actions.
	 */
	public function test_register_hooks(): void {
		$this->instance->register_hooks();

		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', [ $this->instance, 'register_assets' ] ) );
		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] ) );
		$this->assertGreaterThan( 0, has_filter( 'render_block', [ $this->instance, 'enqueue_block_specific_assets' ] ) );
	}

	/**
	 * Handles carry the theme prefix via the framework handle() helper.
	 */
	public function test_handles_are_prefixed(): void {
		$this->assertSame( 'elementary-theme-', Assets::HANDLE_PREFIX );
		$this->assertSame( 'elementary-theme-core-navigation', $this->instance->handle( 'core-navigation' ) );
		$this->assertSame( 'elementary-theme-styles', $this->instance->handle( 'styles' ) );
		$this->assertSame( 'elementary-theme-tailwind', $this->instance->handle( 'tailwind' ) );
		$this->assertSame( 'elementary-theme-browser-sync', $this->instance->handle( 'browser-sync' ) );
	}

	/**
	 * register_assets() registers styles/scripts under the prefixed handles.
	 */
	public function test_register_assets_uses_prefixed_handles(): void {
		// AssetLoader::register_script()/register_style() only register a handle
		// when the built file exists on disk. The PHP unit-test job runs without a
		// front-end build, so write the minimal built files register_assets()
		// reads (removed again in tear_down()).
		$this->write_build_asset( 'js/frontend/core-navigation.js' );
		$this->write_build_asset( 'css/frontend/core-navigation.css' );
		$this->write_build_asset( 'css/frontend/styles.css' );
		$this->write_build_asset( 'css/frontend/tailwind.css' );

		$this->instance->register_assets();

		$this->assertTrue( wp_script_is( 'elementary-theme-core-navigation', 'registered' ) );
		$this->assertTrue( wp_style_is( 'elementary-theme-styles', 'registered' ) );
	}

	/**
	 * Write a fixture file under the theme's built-assets directory.
	 *
	 * @param string $relative Path relative to assets/build (e.g. js/frontend/core-navigation.js).
	 */
	private function write_build_asset( string $relative ): void {
		$file = untrailingslashit( ELEMENTARY_THEME_PATH ) . '/assets/build/' . $relative;
		$dir  = dirname( $file );

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		file_put_contents( $file, '/* test fixture */' );
		$this->fixture_files[] = $file;
	}
}
