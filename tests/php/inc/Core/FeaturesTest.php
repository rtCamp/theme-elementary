<?php
/**
 * Test Features service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Helpers\Util;
use rtCamp\Theme\Elementary\Main;
use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\Utils\FeatureSelector;

/**
 * Class FeaturesTest
 *
 * @since 1.0.0
 */
class FeaturesTest extends TestCase {

	/**
	 * The class exists, extends the framework FeatureSelector, and is shareable.
	 */
	public function test_extends_framework_feature_selector(): void {
		$registry = new FeatureRegistry();

		$this->assertInstanceOf( FeatureSelector::class, $registry );
		$this->assertInstanceOf( Shareable::class, $registry );
	}

	/**
	 * It is registered in Main before the feature classes that depend on it.
	 */
	public function test_registered_and_shared_in_main(): void {
		$this->assertContains( FeatureRegistry::class, Main::CLASSES );
		$this->assertInstanceOf( FeatureRegistry::class, Main::get_instance()->get_shared( FeatureRegistry::class ) );
	}

	/**
	 * The instance is namespaced with the theme's context slug.
	 */
	public function test_uses_elementary_context(): void {
		$this->assertSame( 'elementary', ( new FeatureRegistry() )->get_context() );
	}

	/**
	 * Every theme feature self-registers at construction, so the shared instance
	 * already has the expected flags by the time tests run (Main is booted in
	 * the test bootstrap).
	 */
	public function test_registers_theme_flags(): void {
		$registered = Main::get_instance()->get_shared( FeatureRegistry::class )->get_registered();

		$this->assertContains( 'author-bio', $registered );
		$this->assertContains( 'media-text-interactive', $registered );
	}

	/**
	 * Shared option key and override constants derive from the context.
	 */
	public function test_key_derivation(): void {
		$registry = new FeatureRegistry();

		$this->assertSame( 'elementary_features', $registry->shared_option_key() );
		$this->assertSame( 'author-bio', $registry->flag_key( 'author-bio' ) );
		$this->assertSame( 'ELEMENTARY_FEATURE_AUTHOR_BIO', $registry->constant_name( 'author-bio' ) );
		$this->assertSame( 'ELEMENTARY_FEATURE_MEDIA_TEXT_INTERACTIVE', $registry->constant_name( 'media-text-interactive' ) );
	}

	/**
	 * Flags default to enabled and follow the persisted option.
	 */
	public function test_is_enabled_follows_option(): void {
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );

		$this->assertTrue( $registry->is_enabled( 'author-bio' ) );

		$registry->disable( 'author-bio' );
		$this->assertFalse( $registry->is_enabled( 'author-bio' ) );

		$registry->enable( 'author-bio' );
		$this->assertTrue( $registry->is_enabled( 'author-bio' ) );
	}

	/**
	 * All flags share a single option row; there are no per-flag option rows.
	 */
	public function test_flags_share_one_option_row(): void {
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );

		$registry->enable( 'author-bio' );
		$registry->disable( 'media-text-interactive' );

		$stored = get_option( 'elementary_features' );
		$this->assertTrue( $stored['author-bio'] );
		$this->assertFalse( $stored['media-text-interactive'] );
		$this->assertFalse( get_option( 'elementary_feature_author_bio', false ) );
	}

	/**
	 * Display metadata is resolved on construction with non-empty labels.
	 */
	public function test_get_features_provides_labels(): void {
		foreach ( Main::get_instance()->get_shared( FeatureRegistry::class )->get_features() as $slug => $meta ) {
			$this->assertSame( $slug, $meta['slug'] );
			$this->assertNotSame( '', $meta['name'] );
			$this->assertNotSame( $slug, $meta['name'], "Flag {$slug} should have a human-readable name." );
			$this->assertNotSame( '', $meta['description'] );
		}
	}

	/**
	 * Util::is_feature_enabled() proxies the shared registry instance.
	 */
	public function test_util_helper_reads_flags(): void {
		$this->assertTrue( Util::is_feature_enabled( 'author-bio' ) );

		Main::get_instance()->get_shared( FeatureRegistry::class )->disable( 'author-bio' );

		$this->assertFalse( Util::is_feature_enabled( 'author-bio' ) );
	}
}
