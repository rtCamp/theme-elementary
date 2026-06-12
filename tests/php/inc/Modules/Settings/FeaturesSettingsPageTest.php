<?php
/**
 * Test Features settings page.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Features;
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
	 * The page is driven by the theme's Features selector, so its slug,
	 * option group, and option keys all carry the `elementary` context.
	 */
	public function test_page_is_driven_by_theme_features(): void {
		$page     = new FeaturesSettingsPage();
		$selector = ( new ReflectionProperty( FeatureSelectorSettingsPage::class, 'selector' ) )->getValue( $page );

		$this->assertInstanceOf( Features::class, $selector );
	}

	/**
	 * One boolean setting is registered per theme flag.
	 */
	public function test_registers_one_setting_per_flag(): void {
		$page = new FeaturesSettingsPage();
		$page->register_settings();

		$registered = get_registered_settings();
		$features   = new Features();

		foreach ( $features->get_registered() as $slug ) {
			$option_key = $features->option_key( $slug );

			$this->assertArrayHasKey( $option_key, $registered );
			$this->assertSame( 'boolean', $registered[ $option_key ]['type'] );
		}
	}
}
