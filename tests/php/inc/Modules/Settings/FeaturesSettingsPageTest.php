<?php
/**
 * Test Features settings page.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Modules\Settings\FeaturesSettingsPage;
use rtCamp\WPFramework\Utils\FeatureSelectorSettingsPage;

/**
 * Class FeaturesSettingsPageTest
 *
 * The page chrome itself (menu registration, fields, constant locking) is
 * covered by the framework's FeatureSelectorSettingsPage tests; these tests
 * cover the theme wiring only.
 *
 * @since 1.0.0
 */
class FeaturesSettingsPageTest extends TestCase {

	/**
	 * The class exists and extends the framework page.
	 */
	public function test_extends_framework_page(): void {
		$this->assertInstanceOf( FeatureSelectorSettingsPage::class, new FeaturesSettingsPage() );
	}

	/**
	 * It is registered in Main so the Loader boots it.
	 */
	public function test_registered_in_main(): void {
		$this->assertContains( FeaturesSettingsPage::class, Main::CLASSES );
	}

	/**
	 * The page is driven by the theme's FeatureRegistry (context 'elementary'),
	 * so it registers the single shared option `elementary_features`.
	 */
	public function test_page_is_driven_by_theme_features(): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';

		$page = new FeaturesSettingsPage();
		$page->register_settings();

		$registered = get_registered_settings();

		$this->assertArrayHasKey( 'elementary_features', $registered );
		$this->assertSame( 'array', $registered['elementary_features']['type'] );
	}
}
