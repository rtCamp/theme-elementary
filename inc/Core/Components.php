<?php
/**
 * Theme component loader.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\AssetLoader;
use rtCamp\WPFramework\ComponentLoader;
use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\Theme\Elementary\Main;

/**
 * Class Components
 *
 * The theme's component loader: namespaces its components/handles under the
 * theme context and registers their assets through the theme's shared Assets
 * instance (its AssetLoader).
 *
 * @since 1.0.0
 */
class Components extends ComponentLoader implements Shareable {

	/**
	 * Context slug used to namespace the theme's component asset handles.
	 *
	 * @return string
	 */
	protected function get_context(): string {
		return 'elementary';
	}

	/**
	 * Resolve the theme's shared asset loader (its Assets instance).
	 *
	 * @return AssetLoader Shared theme asset loader.
	 */
	protected function get_asset_loader(): AssetLoader {
		/**
		 * Shared theme asset loader.
		 *
		 * @var Assets $assets
		 */
		$assets = Main::get_instance()->get_shared( Assets::class );

		return $assets;
	}
}
