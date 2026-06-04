<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

use rtCamp\WPFramework\Contracts\Traits\{Singleton, Loader};
use rtCamp\Theme\Elementary\Core\{Assets, Components, Menu, ThemeSetup};
use rtCamp\Theme\Elementary\Modules\{BlockExtensions\MediaTextInteractive, Settings\ThemeOptions};

/**
 * Class Main
 *
 * @since 1.0.0
 */
class Main {

	use Singleton;
	use Loader;

	/**
	 * List of classes to load.
	 */
	const CLASSES = [
		Assets::class,
		Menu::class,
		ThemeSetup::class,
		Components::class,
		MediaTextInteractive::class,
		ThemeOptions::class,
	];

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->load( self::CLASSES );
	}
}
