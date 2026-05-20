<?php
/**
 * Test if class Main exists.
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
		$this->assertTrue( Main::get_instance() instanceof Main );
	}

	/**
	 * Test if class Main has a method 'setup_hooks'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_setup_hooks(): void {
		$this->assertTrue( method_exists( 'rtCamp\Theme\Elementary\Main', 'setup_hooks' ) );
	}

	/**
	 * Test if class Main has a method 'elementary_theme_support'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_elementary_theme_support(): void {
		$this->assertTrue( method_exists( 'rtCamp\Theme\Elementary\Main', 'elementary_theme_support' ) );
	}
}
