<?php
/**
 * Theme bootstrap file.
 *
 * @package Elementary-Theme
 */

namespace rtCamp\Theme\Elementary;

use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Framework\Traits\Singleton;
use rtCamp\Theme\Elementary\Core\Assets;

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

		// Setup hooks.
		$this->setup_hooks();
		$this->block_extensions();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks() {
		add_action( 'after_setup_theme', [ $this, 'elementary_theme_support' ] );
	}

	/**
	 * Add required theme support.
	 *
	 * @since 1.0.0
	 */
	public function elementary_theme_support() {
		// Add support for core block styles.
		add_theme_support( 'wp-block-styles' );
	}

	/**
	 * Block extensions
	 *
	 * @since 1.0.0
	 */
	public function block_extensions() {
		MediaTextInteractive::get_instance();
	}
}
