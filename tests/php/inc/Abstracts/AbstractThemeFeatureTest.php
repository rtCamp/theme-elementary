<?php
/**
 * Test the theme feature base class.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Abstracts\AbstractThemeFeature;
use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Helpers\Util;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\WPPrimitives\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class AbstractThemeFeatureTest
 *
 * Each test defines its feature with a slug of its own: constructing a feature
 * registers its flag on the shared registry for the rest of the run, and a
 * second registration of the same slug is a _doing_it_wrong() collision.
 *
 * @since 1.0.0
 */
class AbstractThemeFeatureTest extends TestCase {

	/**
	 * Constructing a feature registers its flag, name and description on the
	 * theme's shared FeatureRegistry.
	 */
	public function test_construction_registers_on_the_theme_registry(): void {
		$this->feature( 'theme-feature-registers' );

		$features = $this->registry()->get_features();

		$this->assertArrayHasKey( 'theme-feature-registers', $features );
		$this->assertSame( 'Sample theme feature', $features['theme-feature-registers']['name'] );
		$this->assertSame( 'Registered by AbstractThemeFeatureTest.', $features['theme-feature-registers']['description'] );
	}

	/**
	 * Features are conditionally registrable, following their flag.
	 */
	public function test_can_register_follows_the_flag(): void {
		$feature = $this->feature( 'theme-feature-toggle' );

		$this->assertInstanceOf( ConditionallyRegistrable::class, $feature );
		$this->assertTrue( $feature->can_register() );
		$this->assertTrue( Util::is_feature_enabled( 'theme-feature-toggle' ) );

		$this->registry()->disable( 'theme-feature-toggle' );

		$this->assertFalse( $feature->can_register() );
		$this->assertFalse( Util::is_feature_enabled( 'theme-feature-toggle' ) );
	}

	/**
	 * Build a feature with the given slug.
	 *
	 * @param string $slug Feature slug, unique per test.
	 *
	 * @return AbstractThemeFeature Feature.
	 */
	private function feature( string $slug ): AbstractThemeFeature {
		return new class( $slug ) extends AbstractThemeFeature {

			/**
			 * Feature slug.
			 *
			 * @var string
			 */
			private string $test_slug;

			/**
			 * Constructor.
			 *
			 * @param string $slug Feature slug.
			 */
			public function __construct( string $slug ) {
				$this->test_slug = $slug;

				parent::__construct();
			}

			/**
			 * {@inheritDoc}
			 */
			protected function get_slug(): string {
				return $this->test_slug;
			}

			/**
			 * {@inheritDoc}
			 */
			protected function get_name(): string {
				return 'Sample theme feature';
			}

			/**
			 * {@inheritDoc}
			 */
			protected function get_description(): string {
				return 'Registered by AbstractThemeFeatureTest.';
			}

			/**
			 * {@inheritDoc}
			 */
			public function register_hooks(): void {}
		};
	}

	/**
	 * The theme's shared registry.
	 *
	 * @return FeatureRegistry Registry.
	 */
	private function registry(): FeatureRegistry {
		/**
		 * Shared feature registry.
		 *
		 * @var FeatureRegistry $registry
		 */
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );

		return $registry;
	}
}
