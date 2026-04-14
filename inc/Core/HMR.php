<?php
/**
 * Theme bootstrap file for HMR (Hot Module Replacement) in development.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\Theme\Elementary\Framework\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 1.0.0
 */
class HMR {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Setup hooks.
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_hmr_assets' ] );
	}

	/**
	 * Enqueue HMR assets.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_hmr_assets(): void {
		if ( ! defined( 'THEME_HMR' ) || ! THEME_HMR ) {
			return;
		}

		wp_enqueue_script(
			'theme-main-hmr',
			'http://localhost:3000/assets/build/js/main-hmr.js',
			[],
			time(),
			true
		);
	}
}
