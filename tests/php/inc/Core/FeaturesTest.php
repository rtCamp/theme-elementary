<?php
/**
 * Test Features service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Helpers\Util;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\WPPrimitives\Contracts\Interfaces\Shareable;
use rtCamp\WPPrimitives\Utils\FeatureSelector;

/**
 * Class FeaturesTest
 *
 * The registry behaviour is exercised with test-only flags so these tests keep
 * passing after init removes the example features. Checks on the example flags
 * themselves live in the example tests (AuthorBioTest, MediaTextInteractiveTest),
 * which init removes together with the examples.
 *
 * @since 1.0.0
 */
class FeaturesTest extends TestCase {

	/**
	 * Test-only flag registered on the shared registry.
	 */
	private const FLAG = 'sample-flag';

	/**
	 * Second test-only flag, for the shared option row check.
	 */
	private const OTHER_FLAG = 'sample-flag-two';

	/**
	 * Register the test-only flags on the shared registry once.
	 *
	 * Registration lives on the shared instance for the whole run, so only
	 * register what is missing: a repeated register() call is a
	 * _doing_it_wrong() collision.
	 */
	public function set_up(): void {
		parent::set_up();

		$registry   = $this->shared_registry();
		$registered = $registry->get_registered();

		foreach ( [ self::FLAG, self::OTHER_FLAG ] as $flag ) {
			if ( in_array( $flag, $registered, true ) ) {
				continue;
			}

			$registry->register(
				[
					$flag => [
						'name'        => 'Sample flag ' . $flag,
						'description' => 'Registered by FeaturesTest.',
					],
				]
			);
		}
	}

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
		$this->assertInstanceOf( FeatureRegistry::class, $this->shared_registry() );
	}

	/**
	 * The instance is namespaced with the theme's context slug.
	 */
	public function test_uses_elementary_context(): void {
		$this->assertSame( 'elementary', ( new FeatureRegistry() )->get_context() );
	}

	/**
	 * Shared option key and override constants derive from the context.
	 */
	public function test_key_derivation(): void {
		$registry = new FeatureRegistry();

		$this->assertSame( 'elementary_features', $registry->shared_option_key() );
		$this->assertSame( self::FLAG, $registry->flag_key( self::FLAG ) );
		$this->assertSame( 'ELEMENTARY_FEATURE_SAMPLE_FLAG', $registry->constant_name( self::FLAG ) );
	}

	/**
	 * The registry is authoritative: a flag nobody registered is always off.
	 */
	public function test_unregistered_flags_are_off(): void {
		$this->assertFalse( ( new FeatureRegistry() )->is_enabled( self::FLAG ) );
		$this->assertFalse( $this->shared_registry()->is_enabled( 'never-registered-flag' ) );
	}

	/**
	 * Flags default to enabled and follow the persisted option.
	 */
	public function test_is_enabled_follows_option(): void {
		$registry = $this->shared_registry();

		$this->assertTrue( $registry->is_enabled( self::FLAG ) );

		$registry->disable( self::FLAG );
		$this->assertFalse( $registry->is_enabled( self::FLAG ) );

		$registry->enable( self::FLAG );
		$this->assertTrue( $registry->is_enabled( self::FLAG ) );
	}

	/**
	 * All flags share a single option row; there are no per-flag option rows.
	 */
	public function test_flags_share_one_option_row(): void {
		$registry = $this->shared_registry();

		$registry->enable( self::FLAG );
		$registry->disable( self::OTHER_FLAG );

		$stored = get_option( 'elementary_features' );
		$this->assertTrue( $stored[ self::FLAG ] );
		$this->assertFalse( $stored[ self::OTHER_FLAG ] );
		$this->assertFalse( get_option( 'elementary_feature_sample_flag', false ) );
	}

	/**
	 * Display metadata is resolved on construction with non-empty labels.
	 */
	public function test_get_features_provides_labels(): void {
		$features = $this->shared_registry()->get_features();

		$this->assertArrayHasKey( self::FLAG, $features );

		foreach ( $features as $slug => $meta ) {
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
		$this->assertTrue( Util::is_feature_enabled( self::FLAG ) );

		$this->shared_registry()->disable( self::FLAG );

		$this->assertFalse( Util::is_feature_enabled( self::FLAG ) );
	}

	/**
	 * The theme's shared registry instance.
	 *
	 * @return FeatureRegistry Shared registry.
	 */
	private function shared_registry(): FeatureRegistry {
		/**
		 * Shared feature registry.
		 *
		 * @var FeatureRegistry $registry
		 */
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );

		return $registry;
	}
}
