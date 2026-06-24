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
 * the env file resolved by Assets::env_file_path(); the suite overrides that
 * seam to a throwaway temp file (one per test), so it never touches — or
 * depends on — the developer's real .env.local.
 *
 * @since 1.0.0
 */
class AssetsHmrTest extends TestCase {

	/**
	 * Absolute path to this test's throwaway env file.
	 *
	 * @var string
	 */
	private string $env_file = '';

	/**
	 * Point each test at its own temp env file, starting from a clean slate.
	 */
	public function set_up(): void {
		parent::set_up();

		// Unique per test (parallel-safe); start with no file so defaults apply.
		$this->env_file = tempnam( sys_get_temp_dir(), 'elementary-hmr-env-' );
		$this->clear_env();
	}

	/**
	 * Remove the temp env file.
	 */
	public function tear_down(): void {
		$this->clear_env();

		parent::tear_down();
	}

	/**
	 * Delete the temp env file if present.
	 */
	private function clear_env(): void {
		if ( file_exists( $this->env_file ) ) {
			unlink( $this->env_file );
		}
	}

	/**
	 * Write a single KEY=value line to the temp env file.
	 *
	 * @param string $key   Variable name.
	 * @param string $value Value.
	 */
	private function write_env( string $key, string $value ): void {
		file_put_contents( $this->env_file, "{$key}={$value}\n" );
	}

	/**
	 * Invoke a private Assets method on a fresh instance whose env-file lookup
	 * is redirected to this test's temp file.
	 *
	 * @param string $method Method name.
	 *
	 * @return mixed Return value.
	 */
	private function invoke( string $method ) {
		$assets = new class( $this->env_file ) extends Assets {
			/**
			 * Path returned by env_file_path().
			 *
			 * @var string
			 */
			private string $test_env_file;

			/**
			 * Capture the throwaway env file, then build a normal Assets.
			 *
			 * @param string $env_file Throwaway env file to read instead of .env.local.
			 */
			public function __construct( string $env_file ) {
				$this->test_env_file = $env_file;
				parent::__construct();
			}

			/**
			 * Redirect the env lookup to the test's temp file.
			 *
			 * @return string
			 */
			protected function env_file_path(): string {
				return $this->test_env_file;
			}
		};

		// Private parent methods are invocable on the subclass instance;
		// setAccessible() is a no-op (and deprecated) on PHP 8.1+, so it is omitted.
		$ref = new \ReflectionMethod( Assets::class, $method );

		return $ref->invoke( $assets );
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
