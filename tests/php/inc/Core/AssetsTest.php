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
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new Assets();
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
		$this->instance->register_assets();

		$this->assertTrue( wp_script_is( 'elementary-theme-core-navigation', 'registered' ) );
		$this->assertTrue( wp_style_is( 'elementary-theme-styles', 'registered' ) );
	}
}
