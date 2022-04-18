<?php
/**
 * Theme bootstrap file.
 *
 * @package Elementary
 */

namespace Elementary;

use Elementary\Traits\Singleton;
use Elementary\Patterns\Block_Patterns;

/**
 * Class Elementary
 *
 * @since 1.0.0
 */
class Elementary {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Instantiate classes.
		Block_Patterns::get_instance();

		// Setup hooks.
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks() {
		add_action( 'after_setup_theme', array( $this, 'elementary_support' ) );
	}

	/**
	 * Add required theme support.
	 *
	 * @since 1.0.0
	 */
	public function elementary_support() {
		// Add support for core block styles.
		add_theme_support( 'wp-block-styles' );
	}
}
