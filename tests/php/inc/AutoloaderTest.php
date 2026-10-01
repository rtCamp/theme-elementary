<?php
/**
 * Test the Composer autoloader guard.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Autoloader;
use rtCamp\Theme\Elementary\Tests\TestCase;

/**
 * Class AutoloaderTest
 *
 * The missing-autoloader branch needs ELEMENTARY_THEME_PATH to point at a
 * directory without vendor/, which a constant cannot do inside one process,
 * so only the installed path is covered here.
 *
 * @since 1.0.0
 */
class AutoloaderTest extends TestCase {

	/**
	 * With dependencies installed the guard loads them and reports success
	 * without raising the missing-autoloader notice.
	 */
	public function test_autoload_succeeds_when_vendor_is_installed(): void {
		$this->assertFileIsReadable( ELEMENTARY_THEME_PATH . '/vendor/autoload.php' );

		$this->assertTrue( Autoloader::autoload() );
		$this->assertFalse( $this->has_missing_autoloader_notice() );
	}

	/**
	 * Whether any admin_notices callback was added by the autoloader guard.
	 *
	 * @return bool True when a closure from Autoloader is hooked.
	 */
	private function has_missing_autoloader_notice(): bool {
		global $wp_filter;

		foreach ( $wp_filter['admin_notices']->callbacks ?? [] as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( $callback['function'] instanceof Closure ) {
					$scope = ( new ReflectionFunction( $callback['function'] ) )->getClosureScopeClass();

					if ( null !== $scope && Autoloader::class === $scope->getName() ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}
