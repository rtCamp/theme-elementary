<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Framework\Traits\Singleton;
use rtCamp\Theme\Elementary\Core\Assets;
use rtCamp\Theme\Elementary\Core\HMR;

/**
 * Class Main
 *
 * @since 1.0.0
 */
class Main {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Instantiate classes.
		Assets::get_instance();

		if ( defined( 'THEME_HMR' ) && THEME_HMR ) {
			HMR::get_instance();
		}

		// Setup hooks.
		$this->setup_hooks();
		$this->block_extensions();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks(): void {
		add_action( 'after_setup_theme', [ $this, 'elementary_theme_support' ] );
	}

	/**
	 * Add required theme support.
	 *
	 * @since 1.0.0
	 */
	public function elementary_theme_support(): void {
		// Add support for core block styles.
		add_theme_support( 'wp-block-styles' );
	}

	/**
	 * Block extensions
	 *
	 * @since 1.0.0
	 */
	public function block_extensions(): void {
		MediaTextInteractive::get_instance();
	}
}
