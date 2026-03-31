<?php
/**
 * Test if class Main exists.
 *
 * @package Elementary-Theme
 */

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Main;

/**
 * Class Test_Elementary_Theme
 */
class Test_Elementary_Theme extends TestCase {

	/**
	 * Test if class Main exists.
	 *
	 * @since 1.0.0
	 */
	public function test_class_exists() {
		$this->assertTrue( class_exists( 'rtCamp\Theme\Elementary\Main' ) );
	}

	/**
	 * Test if class Main is a singleton.
	 *
	 * @since 1.0.0
	 */
	public function test_class_is_singleton() {
		$this->assertTrue( Main::get_instance() instanceof Main );
	}

	/**
	 * Test if class Main has a method 'setup_hooks'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_setup_hooks() {
		$this->assertTrue( method_exists( 'rtCamp\Theme\Elementary\Main', 'setup_hooks' ) );
	}

	/**
	 * Test if class Main has a method 'elementary_theme_support'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_elementary_theme_support() {
		$this->assertTrue( method_exists( 'rtCamp\Theme\Elementary\Main', 'elementary_theme_support' ) );
	}
}
