<?php
/**
 * Test Tailwind CSS enqueue gating in the Assets class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Assets;

/**
 * Class AssetsTailwindTest
 *
 * Tailwind is off by default; the elementary_theme_tailwind_enabled filter
 * (wrapping the ELEMENTARY_THEME_ENABLE_TAILWIND constant, resolved at enqueue
 * time) turns it on or off. These cover the enqueue gating behaviour.
 *
 * @since 1.0.0
 */
class AssetsTailwindTest extends TestCase {

	private const TAILWIND_HANDLE = 'elementary-theme-tailwind';
	private const TAILWIND_FILTER = 'elementary_theme_tailwind_enabled';

	/**
	 * Assets instance.
	 *
	 * @var Assets
	 */
	private Assets $instance;

	/**
	 * Saved styles registry, restored after each test.
	 *
	 * @var WP_Styles|null
	 */
	private ?WP_Styles $old_wp_styles;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();

		// Isolate the style queue so enqueues don't leak across tests.
		$this->old_wp_styles  = $GLOBALS['wp_styles'] ?? null;
		$GLOBALS['wp_styles'] = new WP_Styles();

		$this->instance = new Assets();

		// Stand in for the build output: the asset meta isn't present in the test
		// environment, so register the handle directly. This lets the assertions
		// exercise the enqueue gating rather than the framework's asset resolution.
		wp_register_style( self::TAILWIND_HANDLE, 'https://example.org/tailwind.css', [], '1.0.0' );
	}

	/**
	 * Teardown test.
	 */
	public function tear_down(): void {
		$GLOBALS['wp_styles'] = $this->old_wp_styles;

		parent::tear_down();
	}

	/**
	 * Off by default: with the constant at its false default and no filter, the
	 * Tailwind style is not enqueued.
	 */
	public function test_not_enqueued_by_default(): void {
		$this->instance->enqueue_assets();

		$this->assertFalse( wp_style_is( self::TAILWIND_HANDLE, 'enqueued' ) );
	}

	/**
	 * The filter enables Tailwind and the style is enqueued. The filter is added
	 * after the instance is constructed, proving the flag is resolved at enqueue
	 * time rather than once in the constructor.
	 */
	public function test_enqueued_when_filter_enables(): void {
		add_filter( self::TAILWIND_FILTER, '__return_true' );

		$this->instance->enqueue_assets();

		$this->assertTrue( wp_style_is( self::TAILWIND_HANDLE, 'enqueued' ) );
	}

	/**
	 * The filter force-disables Tailwind: the style is not enqueued even though
	 * the handle is registered.
	 */
	public function test_not_enqueued_when_filter_disables(): void {
		add_filter( self::TAILWIND_FILTER, '__return_false' );

		$this->instance->enqueue_assets();

		$this->assertFalse( wp_style_is( self::TAILWIND_HANDLE, 'enqueued' ) );
	}
}
