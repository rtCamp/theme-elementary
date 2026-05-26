<?php
/**
 * Test ThemeSetup class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\ThemeSetup;

/**
 * Class ThemeSetupTest
 *
 * @since 1.0.0
 */
class ThemeSetupTest extends TestCase {

	/**
	 * ThemeSetup instance.
	 *
	 * @var ThemeSetup
	 */
	private ThemeSetup $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new ThemeSetup();
	}

	/**
	 * Test class exists.
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( ThemeSetup::class ) );
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

		$this->assertGreaterThan( 0, has_action( 'after_setup_theme', [ $this->instance, 'add_theme_support' ] ) );
		$this->assertGreaterThan( 0, has_action( 'after_setup_theme', [ $this->instance, 'register_image_sizes' ] ) );
		$this->assertGreaterThan( 0, has_action( 'after_setup_theme', [ $this->instance, 'load_textdomain' ] ) );
	}

	/**
	 * Test theme support is added.
	 */
	public function test_add_theme_support(): void {
		$this->instance->add_theme_support();

		$this->assertTrue( current_theme_supports( 'responsive-embeds' ) );
		$this->assertTrue( current_theme_supports( 'custom-spacing' ) );
		$this->assertTrue( current_theme_supports( 'align-wide' ) );
	}

	/**
	 * Test image sizes are registered.
	 */
	public function test_register_image_sizes(): void {
		$this->instance->register_image_sizes();

		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayHasKey( 'elementary-featured', $sizes );
		$this->assertSame( 1200, $sizes['elementary-featured']['width'] );
		$this->assertSame( 630, $sizes['elementary-featured']['height'] );
		$this->assertTrue( $sizes['elementary-featured']['crop'] );
	}
}
