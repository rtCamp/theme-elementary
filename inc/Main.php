<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

use rtCamp\WPFramework\Contracts\Traits\{Singleton, Loader};
use rtCamp\Theme\Elementary\Core\Assets;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Modules\Settings\Theme_Options;

/**
 * Class Main
 *
 * @since 1.0.0
 */
class Main {

	use Singleton;
	use Loader;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->load(
			[
				Assets::class,
				MediaTextInteractive::class,
				Theme_Options::class,
			] 
		);

		$this->setup_hooks();
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
		add_theme_support( 'wp-block-styles' );
	}
}
