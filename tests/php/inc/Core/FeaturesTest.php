<?php
/**
 * Test Features service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Features;
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
	 * The class exists and extends the framework FeatureSelector.
	 */
	public function test_extends_framework_feature_selector(): void {
		$features = new Features();

		$this->assertInstanceOf( FeatureSelector::class, $features );
		$this->assertInstanceOf( Shareable::class, $features );
	}

	/**
	 * It is shareable, registered in Main, and resolvable from the container.
	 */
	public function test_registered_and_shared_in_main(): void {
		$this->assertContains( Features::class, Main::CLASSES );
		$this->assertInstanceOf( Features::class, Main::get_instance()->get_shared( Features::class ) );
	}

	/**
	 * The instance is namespaced with the theme's context slug.
	 */
	public function test_uses_elementary_context(): void {
		$this->assertSame( 'elementary', ( new Features() )->get_context() );
	}

	/**
	 * Every theme flag is registered at construction.
	 */
	public function test_registers_theme_flags(): void {
		$registered = ( new Features() )->get_registered();

		$this->assertContains( Features::AUTHOR_BIO, $registered );
		$this->assertContains( Features::MEDIA_TEXT_INTERACTIVE, $registered );
	}

	/**
	 * Option keys and override constants derive from the context.
	 */
	public function test_key_derivation(): void {
		$features = new Features();

		$this->assertSame( 'elementary_feature_author_bio', $features->option_key( Features::AUTHOR_BIO ) );
		$this->assertSame( 'ELEMENTARY_FEATURE_AUTHOR_BIO', $features->constant_name( Features::AUTHOR_BIO ) );
		$this->assertSame( 'elementary_feature_media_text_interactive', $features->option_key( Features::MEDIA_TEXT_INTERACTIVE ) );
	}

	/**
	 * Flags default to enabled and follow the persisted option.
	 */
	public function test_is_enabled_follows_option(): void {
		$features = new Features();

		$this->assertTrue( $features->is_enabled( Features::AUTHOR_BIO ) );

		update_option( $features->option_key( Features::AUTHOR_BIO ), true );
		$this->assertTrue( $features->is_enabled( Features::AUTHOR_BIO ) );

		$features->disable( Features::AUTHOR_BIO );
		$this->assertFalse( $features->is_enabled( Features::AUTHOR_BIO ) );

		$features->enable( Features::AUTHOR_BIO );
		$this->assertTrue( $features->is_enabled( Features::AUTHOR_BIO ) );
	}

	/**
	 * Two instances read the same state — the invariant that makes the
	 * `new Features()` instances in load-time consumers equivalent to the
	 * shared one.
	 */
	public function test_instances_are_interchangeable(): void {
		$writer = new Features();
		$reader = new Features();

		$writer->enable( Features::MEDIA_TEXT_INTERACTIVE );

		$this->assertTrue( $reader->is_enabled( Features::MEDIA_TEXT_INTERACTIVE ) );
		$this->assertSame( $writer->get_registered(), $reader->get_registered() );
	}

	/**
	 * Display metadata is resolved lazily with non-empty labels for every flag.
	 */
	public function test_get_features_provides_labels(): void {
		foreach ( ( new Features() )->get_features() as $slug => $meta ) {
			$this->assertSame( $slug, $meta['slug'] );
			$this->assertNotSame( '', $meta['name'] );
			$this->assertNotSame( $slug, $meta['name'], "Flag {$slug} should have a human-readable name." );
			$this->assertNotSame( '', $meta['description'] );
		}
	}

	/**
	 * Util::is_feature_enabled() proxies the shared instance.
	 */
	public function test_util_helper_reads_flags(): void {
		$this->assertTrue( Util::is_feature_enabled( Features::AUTHOR_BIO ) );

		( new Features() )->disable( Features::AUTHOR_BIO );

		$this->assertFalse( Util::is_feature_enabled( Features::AUTHOR_BIO ) );
	}
}
