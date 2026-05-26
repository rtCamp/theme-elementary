<?php
/**
 * Test Menu class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Menu;

/**
 * Class MenuTest
 *
 * @since 1.0.0
 */
class MenuTest extends TestCase {

	/**
	 * Menu instance.
	 *
	 * @var Menu
	 */
	private Menu $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new Menu();
	}

	/**
	 * Test class exists.
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( Menu::class ) );
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

		$this->assertGreaterThan( 0, has_action( 'after_setup_theme', [ $this->instance, 'register_menus' ] ) );
	}

	/**
	 * Test menus are registered.
	 */
	public function test_register_menus(): void {
		$this->instance->register_menus();

		$locations = get_registered_nav_menus();
		$this->assertArrayHasKey( 'primary', $locations );
		$this->assertArrayHasKey( 'footer', $locations );
	}
}
