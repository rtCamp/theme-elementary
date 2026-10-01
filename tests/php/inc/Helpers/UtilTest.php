<?php
/**
 * Test the Util helpers.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Core\Components;
use rtCamp\Theme\Elementary\Core\Encryption;
use rtCamp\Theme\Elementary\Core\Logger;
use rtCamp\Theme\Elementary\Core\Templates;
use rtCamp\Theme\Elementary\Helpers\Util;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Tests\TestCase;

/**
 * Class UtilTest
 *
 * Component and template fixtures are written into the theme for the duration
 * of a test (as AssetsTest does for build files) so the suite does not depend
 * on the removable example components and template parts.
 *
 * @since 1.0.0
 */
class UtilTest extends TestCase {

	/**
	 * Fixture component name.
	 */
	private const COMPONENT = 'util-test-fixture';

	/**
	 * Fixture template part slug.
	 */
	private const TEMPLATE = 'util-test-fixture';

	/**
	 * Paths created by a test, removed in tear_down().
	 *
	 * @var string[]
	 */
	private array $created = [];

	/**
	 * Remove fixtures and reset the loaders' lookup caches.
	 */
	public function tear_down(): void {
		foreach ( array_reverse( $this->created ) as $path ) {
			if ( is_file( $path ) ) {
				unlink( $path );
			} elseif ( is_dir( $path ) ) {
				rmdir( $path );
			}
		}
		$this->created = [];

		Util::components()->clear_cache();
		Util::templates()->clear_cache();

		parent::tear_down();
	}

	/**
	 * Util is static-only.
	 */
	public function test_cannot_be_instantiated(): void {
		$constructor = ( new ReflectionClass( Util::class ) )->getConstructor();

		$this->assertNotNull( $constructor );
		$this->assertTrue( $constructor->isPrivate() );
	}

	/**
	 * The accessors hand out the container's shared instances.
	 */
	public function test_service_accessors_return_shared_instances(): void {
		$main = Main::get_instance();

		$this->assertSame( $main->get_shared( Logger::class ), Util::logger() );
		$this->assertSame( $main->get_shared( Encryption::class ), Util::encryption() );
		$this->assertSame( $main->get_shared( Templates::class ), Util::templates() );
		$this->assertSame( $main->get_shared( Components::class ), Util::components() );
	}

	/**
	 * Util::get_component() returns a component's markup with its args in
	 * scope; Util::component() echoes the same markup.
	 */
	public function test_component_helpers_render_with_args(): void {
		$this->write_component( '<span class="fixture"><?php echo esc_html( $args[\'label\'] ?? \'\' ); ?></span>' );

		$html = Util::get_component( self::COMPONENT, [ 'label' => '<b>Hi</b>' ] );
		$this->assertSame( '<span class="fixture">&lt;b&gt;Hi&lt;/b&gt;</span>', trim( $html ) );

		ob_start();
		Util::component( self::COMPONENT, [ 'label' => 'Echoed' ] );
		$this->assertSame( '<span class="fixture">Echoed</span>', trim( (string) ob_get_clean() ) );
	}

	/**
	 * An unknown component renders nothing and reports the misuse.
	 */
	public function test_unknown_component_renders_nothing(): void {
		$this->setExpectedIncorrectUsage( Components::class . '::get' );

		$this->assertSame( '', Util::get_component( 'no-such-component' ) );
	}

	/**
	 * Util::get_template() returns a template part with its args;
	 * Util::render_template() echoes it.
	 */
	public function test_template_helpers_render_with_args(): void {
		$this->write_template( '<p><?php echo esc_html( $args[\'title\'] ?? \'\' ); ?></p>' );

		$this->assertSame( '<p>Hello &amp; welcome</p>', trim( Util::get_template( self::TEMPLATE, null, [ 'title' => 'Hello & welcome' ] ) ) );

		ob_start();
		Util::render_template( self::TEMPLATE, null, [ 'title' => 'Echoed' ] );
		$this->assertSame( '<p>Echoed</p>', trim( (string) ob_get_clean() ) );
	}

	/**
	 * A missing template part renders an empty string.
	 */
	public function test_missing_template_renders_empty_string(): void {
		$this->assertSame( '', Util::get_template( 'no-such-part' ) );
	}

	/**
	 * Write the fixture component template into the theme's components directory.
	 *
	 * @param string $php Component template source.
	 */
	private function write_component( string $php ): void {
		$dir = untrailingslashit( ELEMENTARY_THEME_PATH ) . '/src/components/' . self::COMPONENT;
		$this->write( $dir, self::COMPONENT . '.php', $php );
	}

	/**
	 * Write the fixture template part into the theme's template-parts directory.
	 *
	 * @param string $php Template source.
	 */
	private function write_template( string $php ): void {
		$this->write( untrailingslashit( ELEMENTARY_THEME_PATH ) . '/template-parts', self::TEMPLATE . '.php', $php );
	}

	/**
	 * Create a fixture file, tracking created paths for cleanup.
	 *
	 * @param string $dir      Directory.
	 * @param string $filename File name.
	 * @param string $contents File contents.
	 */
	private function write( string $dir, string $filename, string $contents ): void {
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
			$this->created[] = $dir;
		}

		$file = $dir . '/' . $filename;
		file_put_contents( $file, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Test fixture.
		$this->created[] = $file;
	}
}
