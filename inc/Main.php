<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

use rtCamp\WPFramework\Contracts\Traits\Singleton;
use rtCamp\WPFramework\Contracts\Traits\Loader;
use rtCamp\Theme\Elementary\Core\Assets;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Post_Types\Portfolio;
use rtCamp\Theme\Elementary\Settings\Theme_Options;
use rtCamp\Theme\Elementary\Shortcodes\Current_Year;
use rtCamp\Theme\Elementary\Taxonomies\Project_Type;

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
		$this->load( [
			Assets::class,
			MediaTextInteractive::class,
			Portfolio::class,
			Project_Type::class,
			Current_Year::class,
			Theme_Options::class,
		] );

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
