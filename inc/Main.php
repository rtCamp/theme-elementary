<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

use rtCamp\WPFramework\Contracts\Traits\{Singleton, Loader};
use rtCamp\Theme\Elementary\Core\{Assets, Components, Encryption, Logger, Menu, Templates, ThemeSetup};
// wp:example:block-extension
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
// wp:example:block-extension:end
// wp:example:settings
use rtCamp\Theme\Elementary\Modules\Settings\ThemeOptions;
// wp:example:settings:end
// wp:example:shortcode
use rtCamp\Theme\Elementary\Modules\Shortcodes\AuthorBio;
// wp:example:shortcode:end

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
		Templates::class,
		Encryption::class,
		Logger::class,
		// wp:example:block-extension
		MediaTextInteractive::class,
		// wp:example:block-extension:end
		// wp:example:settings
		ThemeOptions::class,
		// wp:example:settings:end
		// wp:example:shortcode
		AuthorBio::class,
		// wp:example:shortcode:end
	];

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->load( self::CLASSES );
	}
}
