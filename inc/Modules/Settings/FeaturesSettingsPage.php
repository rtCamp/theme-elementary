<?php
/**
 * Features settings page.
 *
 * @package rtCamp\Theme\Elementary\Modules\Settings
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Settings;

use rtCamp\Theme\Elementary\Core\Features;
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
	 * Constructor.
	 *
	 * The Loader instantiates without arguments, so the selector cannot be
	 * injected; see the Features docblock for why a fresh instance is
	 * equivalent to the shared one.
	 */
	public function __construct() {
		parent::__construct( new Features() );
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
