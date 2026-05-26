<?php
/**
 * Test Main class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Main;

/**
 * Class MainTest
 *
 * @since 1.0.0
 */
class MainTest extends TestCase {

	/**
	 * Test if class Main exists.
	 *
	 * @since 1.0.0
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( 'rtCamp\Theme\Elementary\Main' ) );
	}

	/**
	 * Test if class Main is a singleton.
	 *
	 * @since 1.0.0
	 */
	public function test_class_is_singleton(): void {
		$this->assertInstanceOf( Main::class, Main::get_instance() );
	}
}
