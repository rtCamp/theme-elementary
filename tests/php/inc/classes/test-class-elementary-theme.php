<?php
/**
 * Test if class Elementary_Theme exists.
 *
 * @package Elementary-Theme
 */

use Elementary_Theme\Tests\TestCase;
use Elementary_Theme\Elementary_Theme;

/**
 * Class Test_Elementary_Theme
 */
class Test_Elementary_Theme extends TestCase {

	/**
	 * Test if class Elementary_Theme exists.
	 *
	 * @since 1.0.0
	 */
	public function test_class_exists() {
		$this->assertTrue( class_exists( 'Elementary_Theme\Elementary_Theme' ) );
	}

	/**
	 * Test if class Elementary_Theme is a singleton.
	 *
	 * @since 1.0.0
	 */
	public function test_class_is_singleton() {
		$this->assertTrue( Elementary_Theme::get_instance() instanceof Elementary_Theme );
	}

	/**
	 * Test if class Elementary_Theme has a method 'setup_hooks'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_setup_hooks() {
		$this->assertTrue( method_exists( 'Elementary_Theme\Elementary_Theme', 'setup_hooks' ) );
	}

	/**
	 * Test if class Elementary_Theme has a method 'elementary_theme_support'.
	 *
	 * @since 1.0.0
	 */
	public function test_class_has_method_elementary_theme_support() {
		$this->assertTrue( method_exists( 'Elementary_Theme\Elementary_Theme', 'elementary_theme_support' ) );
	}
}
