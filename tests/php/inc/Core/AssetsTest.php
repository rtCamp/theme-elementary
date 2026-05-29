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
	 * Tear down test.
	 */
	public function tear_down(): void {
		wp_dequeue_style( 'elementary-theme-tailwind' );
		wp_deregister_style( 'elementary-theme-tailwind' );
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

		$this->assertGreaterThan( 0, has_action( 'after_setup_theme', [ $this->instance, 'add_editor_styles' ] ) );
		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', [ $this->instance, 'register_assets' ] ) );
		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', [ $this->instance, 'enqueue_assets' ] ) );
		$this->assertGreaterThan( 0, has_filter( 'render_block', [ $this->instance, 'enqueue_block_specific_assets' ] ) );
	}

	/**
	 * Test add_editor_styles registers the Tailwind stylesheet when Tailwind is enabled.
	 */
	public function test_add_editor_styles_when_tailwind_enabled(): void {
		global $editor_styles;
		$before = (array) $editor_styles;

		add_filter( 'elementary_theme_tailwind_enabled', '__return_true' );
		$instance = new Assets();
		$instance->add_editor_styles();
		remove_filter( 'elementary_theme_tailwind_enabled', '__return_true' );

		$added = array_diff( (array) $editor_styles, $before );
		$found = array_filter( $added, fn( string $s ): bool => str_contains( $s, 'tailwind.css' ) );
		$this->assertNotEmpty( $found );
	}

	/**
	 * Test add_editor_styles does not register the Tailwind stylesheet when Tailwind is disabled.
	 */
	public function test_add_editor_styles_when_tailwind_disabled(): void {
		global $editor_styles;
		$before = (array) $editor_styles;

		add_filter( 'elementary_theme_tailwind_enabled', '__return_false' );
		$instance = new Assets();
		$instance->add_editor_styles();
		remove_filter( 'elementary_theme_tailwind_enabled', '__return_false' );

		$added = array_diff( (array) $editor_styles, $before );
		$found = array_filter( $added, fn( string $s ): bool => str_contains( $s, 'tailwind.css' ) );
		$this->assertEmpty( $found );
	}

	/**
	 * Test enqueue_assets enqueues the Tailwind stylesheet on the frontend when enabled.
	 */
	public function test_enqueue_assets_enqueues_tailwind_when_enabled(): void {
		add_filter( 'elementary_theme_tailwind_enabled', '__return_true' );
		$instance = new Assets();

		wp_register_style( 'elementary-theme-tailwind', 'https://example.com/tailwind.css', [], false );
		$instance->enqueue_assets();

		remove_filter( 'elementary_theme_tailwind_enabled', '__return_true' );

		$this->assertTrue( wp_style_is( 'elementary-theme-tailwind', 'enqueued' ) );
	}

	/**
	 * Test enqueue_assets does not enqueue the Tailwind stylesheet on the frontend when disabled.
	 */
	public function test_enqueue_assets_does_not_enqueue_tailwind_when_disabled(): void {
		add_filter( 'elementary_theme_tailwind_enabled', '__return_false' );
		$instance = new Assets();

		wp_register_style( 'elementary-theme-tailwind', 'https://example.com/tailwind.css', [], false );
		$instance->enqueue_assets();

		remove_filter( 'elementary_theme_tailwind_enabled', '__return_false' );

		$this->assertFalse( wp_style_is( 'elementary-theme-tailwind', 'enqueued' ) );
	}
}
