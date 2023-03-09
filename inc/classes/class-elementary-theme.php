<?php
/**
 * Theme bootstrap file.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme;

use Elementary_Theme\Traits\Singleton;
use Elementary_Theme\Assets;

/**
 * Class Elementary_Theme
 *
 * @since 1.0.0
 */
class Elementary_Theme {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Instantiate classes.
		Assets::get_instance();

		// Setup hooks.
		$this->setup_hooks();
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
}
