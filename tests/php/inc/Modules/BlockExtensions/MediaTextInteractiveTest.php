<?php
/**
 * Test MediaTextInteractive block extension.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\WPFramework\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class MediaTextInteractiveTest
 *
 * @since 1.0.0
 */
class MediaTextInteractiveTest extends TestCase {

	/**
	 * MediaTextInteractive implements ConditionallyRegistrable.
	 */
	public function test_implements_conditionally_registrable(): void {
		$this->assertTrue( is_a( MediaTextInteractive::class, ConditionallyRegistrable::class, true ) );
	}

	/**
	 * The block render filters are attached when the feature is loaded.
	 */
	public function test_registers_block_render_filters(): void {
		$this->assertNotFalse( has_filter( 'render_block_core/button' ) );
		$this->assertNotFalse( has_filter( 'render_block_core/columns' ) );
		$this->assertNotFalse( has_filter( 'render_block_core/video' ) );
	}
}
