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
use rtCamp\Theme\Elementary\Core\Menu;
use rtCamp\Theme\Elementary\Core\ThemeSetup;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Modules\Settings\ThemeOptions;

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
				Menu::class,
				ThemeSetup::class,
				MediaTextInteractive::class,
				ThemeOptions::class,
			]
		);
	}
}
