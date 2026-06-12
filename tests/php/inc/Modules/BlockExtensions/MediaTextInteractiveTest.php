<?php
/**
 * Test MediaTextInteractive block extension.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Features;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\WPFramework\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class MediaTextInteractiveTest
 *
 * @since 1.0.0
 */
class MediaTextInteractiveTest extends TestCase {

	/**
	 * MediaTextInteractive instance.
	 *
	 * @var MediaTextInteractive
	 */
	private MediaTextInteractive $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new MediaTextInteractive();
	}

	/**
	 * The module is gated behind the `media-text-interactive` feature flag:
	 * off by default, on once the flag is enabled.
	 */
	public function test_registration_is_gated_by_feature_flag(): void {
		$this->assertInstanceOf( ConditionallyRegistrable::class, $this->instance );
		$this->assertFalse( $this->instance->can_register() );

		( new Features() )->enable( Features::MEDIA_TEXT_INTERACTIVE );

		$this->assertTrue( $this->instance->can_register() );
	}

	/**
	 * The block render filters are attached by register_hooks().
	 */
	public function test_registers_block_render_filters(): void {
		$this->instance->register_hooks();

		$this->assertNotFalse( has_filter( 'render_block_core/button', [ $this->instance, 'render_block_core_button' ] ) );
		$this->assertNotFalse( has_filter( 'render_block_core/columns', [ $this->instance, 'render_block_core_columns' ] ) );
		$this->assertNotFalse( has_filter( 'render_block_core/video', [ $this->instance, 'render_block_core_video' ] ) );
	}
}
