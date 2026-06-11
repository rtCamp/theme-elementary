<?php
/**
 * Test Templates loader.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Templates;
use rtCamp\Theme\Elementary\Main;

/**
 * Class TemplatesTest
 *
 * @since 1.0.0
 */
class TemplatesTest extends TestCase {

	/**
	 * Templates instance.
	 *
	 * @var Templates
	 */
	private Templates $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new Templates();
	}

	/**
	 * The class exists and extends the framework TemplateLoader.
	 */
	public function test_extends_framework_template_loader(): void {
		$this->assertInstanceOf( 'rtCamp\WPFramework\TemplateLoader', $this->instance );
	}

	/**
	 * It is shareable, so the container hands out a single instance.
	 */
	public function test_is_shareable(): void {
		$this->assertInstanceOf( 'rtCamp\WPFramework\Contracts\Interfaces\Shareable', $this->instance );
	}

	/**
	 * It is registered in Main and resolvable from the container.
	 */
	public function test_registered_and_shared_in_main(): void {
		$this->assertContains( Templates::class, Main::CLASSES );
		$this->assertInstanceOf( Templates::class, Main::get_instance()->get_shared( Templates::class ) );
	}

	/**
	 * It resolves the theme's own template parts (e.g. the author-bio example).
	 */
	public function test_resolves_a_theme_template_part(): void {
		$located = $this->instance->locate( 'author-bio' );

		$this->assertIsString( $located );
		$this->assertStringEndsWith( 'template-parts/author-bio.php', (string) $located );
	}

	/**
	 * A missing part resolves to false rather than erroring.
	 */
	public function test_missing_part_returns_false(): void {
		$this->assertFalse( $this->instance->locate( 'no-such-part' ) );
	}
}
