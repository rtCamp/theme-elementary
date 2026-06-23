<?php
/**
 * Features settings page.
 *
 * @package rtCamp\Theme\Elementary\Modules\Settings
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Settings;

use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Main;
use rtCamp\WPFramework\Utils\FeatureSelectorSettingsPage;

/**
 * Class FeaturesSettingsPage
 *
 * Admin UI for the theme's feature flags, at Settings → Features (slug
 * `elementary-features`). The framework page renders one checkbox per flag
 * registered on the injected selector and shows constant-overridden flags as
 * locked — only the titles are overridden here, for the theme text domain.
 *
 * @since 1.0.0
 */
final class FeaturesSettingsPage extends FeatureSelectorSettingsPage {

	/**
	 * {@inheritDoc}
	 */
	protected function get_selector(): FeatureRegistry {
		return Main::get_instance()->get_shared( FeatureRegistry::class );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_title(): string {
		return __( 'Elementary Features', 'elementary-theme' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_menu_title(): string {
		return __( 'Features', 'elementary-theme' );
	}
}
