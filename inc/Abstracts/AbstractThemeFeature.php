<?php
/**
 * Theme feature base class.
 *
 * @package rtCamp\Theme\Elementary\Abstracts
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Abstracts;

use rtCamp\WPFramework\Contracts\Abstracts\AbstractFeature;
use rtCamp\WPFramework\Utils\FeatureSelector;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Core\FeatureRegistry;

/**
 * Class AbstractThemeFeature
 *
 * Abstract base for all theme features. Wires AbstractFeature to the theme's
 * shared FeatureRegistry via Main::get_shared().
 *
 * @since 1.0.0
 */
abstract class AbstractThemeFeature extends AbstractFeature {

	/**
	 * {@inheritDoc}
	 */
	protected function get_feature_registry(): FeatureSelector {
		return Main::get_instance()->get_shared( FeatureRegistry::class );
	}
}
