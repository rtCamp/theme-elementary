<?php
/**
 * Test the HMR / BrowserSync gating in the Assets class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Assets;

/**
 * Class AssetsHmrTest
 *
 * Covers is_hmr_enabled() (the ENABLE_HMR master switch) and
 * is_browser_sync_disabled() (the independent DISABLE_BS override). Both read
 * .env.local in the theme root, so each test writes a fresh file and a new
 * Assets instance re-reads it.
 *
 * @since 1.0.0
 */
class AssetsHmrTest extends TestCase {

	/**
	 * Absolute path to the theme's .env.local.
	 *
	 * @var string
	 */
	private string $env_file = '';

	/**
	 * Contents of a pre-existing .env.local, restored after the test.
	 *
	 * @var string|null
	 */
	private ?string $env_backup = null;

	/**
	 * Back up any real .env.local and start each test from a clean slate.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->env_file   = trailingslashit( ELEMENTARY_THEME_PATH ) . '.env.local';
		$this->env_backup = is_readable( $this->env_file ) ? file_get_contents( $this->env_file ) : null;
		$this->clear_env();
	}

	/**
	 * Restore the developer's .env.local exactly as it was.
	 */
	public function tear_down(): void {
		$this->clear_env();

		if ( null !== $this->env_backup ) {
			file_put_contents( $this->env_file, $this->env_backup );
		}

		parent::tear_down();
	}

	/**
	 * Remove the test .env.local if present.
	 */
	private function clear_env(): void {
		if ( file_exists( $this->env_file ) ) {
			unlink( $this->env_file );
		}
	}

	/**
	 * Write a single KEY=value line to .env.local.
	 *
	 * @param string $key   Variable name.
	 * @param string $value Value.
	 */
	private function write_env( string $key, string $value ): void {
		file_put_contents( $this->env_file, "{$key}={$value}\n" );
	}

	/**
	 * Invoke a private Assets method on a fresh instance (which re-reads .env.local).
	 *
	 * @param string $method Method name.
	 *
	 * @return mixed Return value.
	 */
	private function invoke( string $method ) {
		$ref = new \ReflectionMethod( Assets::class, $method );
		$ref->setAccessible( true );

		return $ref->invoke( new Assets() );
	}

	/**
	 * HMR is on when ENABLE_HMR is absent.
	 */
	public function test_hmr_enabled_by_default(): void {
		$this->assertTrue( $this->invoke( 'is_hmr_enabled' ) );
	}

	/**
	 * Off values switch HMR off.
	 */
	public function test_hmr_disabled_for_off_values(): void {
		foreach ( [ 'false', '0', 'no', 'off', 'OFF' ] as $value ) {
			$this->write_env( 'ENABLE_HMR', $value );
			$this->assertFalse( $this->invoke( 'is_hmr_enabled' ), "ENABLE_HMR={$value}" );
		}
	}

	/**
	 * Anything else keeps HMR on.
	 */
	public function test_hmr_enabled_for_on_values(): void {
		foreach ( [ 'true', '1', 'yes', 'on' ] as $value ) {
			$this->write_env( 'ENABLE_HMR', $value );
			$this->assertTrue( $this->invoke( 'is_hmr_enabled' ), "ENABLE_HMR={$value}" );
		}
	}

	/**
	 * DISABLE_BS is independent of HMR: off by default, on for truthy values.
	 */
	public function test_disable_bs_is_independent(): void {
		$this->assertFalse( $this->invoke( 'is_browser_sync_disabled' ) );

		$this->write_env( 'DISABLE_BS', 'true' );
		$this->assertTrue( $this->invoke( 'is_browser_sync_disabled' ) );
	}
}
