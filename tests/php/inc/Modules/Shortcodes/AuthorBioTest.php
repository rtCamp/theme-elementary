<?php
/**
 * Test AuthorBio shortcode.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Core\Templates;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Modules\Shortcodes\AuthorBio;
use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\WPPrimitives\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class AuthorBioTest
 *
 * Exercises the example end-to-end: shortcode -> Util::get_template -> the
 * Templates loader -> template-parts/author-bio.php.
 *
 * @since 1.0.0
 */
class AuthorBioTest extends TestCase {

	/**
	 * AuthorBio implements ConditionallyRegistrable.
	 */
	public function test_implements_conditionally_registrable(): void {
		$this->assertTrue( is_a( AuthorBio::class, ConditionallyRegistrable::class, true ) );
	}

	/**
	 * The shortcode is registered by register_hooks().
	 */
	public function test_registers_shortcode(): void {
		$this->assertTrue( shortcode_exists( 'elementary_author_bio' ) );
	}

	/**
	 * It renders the author-bio part for a real user.
	 */
	public function test_renders_author_bio_for_a_user(): void {
		$user_id = self::factory()->user->create(
			[
				'display_name' => 'Ada Lovelace',
				'description'  => 'First programmer.',
			]
		);

		$html = do_shortcode( '[elementary_author_bio user_id="' . $user_id . '"]' );

		$this->assertStringContainsString( 'elementary-author-bio', $html );
		$this->assertStringContainsString( 'Ada Lovelace', $html );
		$this->assertStringContainsString( 'First programmer.', $html );
	}

	/**
	 * It returns an empty string when no valid user is resolved.
	 */
	public function test_returns_empty_for_invalid_user(): void {
		$this->assertSame( '', do_shortcode( '[elementary_author_bio user_id="0"]' ) );
	}

	/**
	 * The register_hooks() method adds the shortcode. Built without the
	 * constructor, which would register the author-bio flag a second time.
	 */
	public function test_register_hooks_adds_the_shortcode(): void {
		remove_shortcode( 'elementary_author_bio' );

		$feature = ( new ReflectionClass( AuthorBio::class ) )->newInstanceWithoutConstructor();
		$feature->register_hooks();

		$this->assertTrue( shortcode_exists( 'elementary_author_bio' ) );
	}

	/**
	 * The feature self-registers its flag with a description for Settings → Features.
	 */
	public function test_feature_flag_is_registered_and_described(): void {
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );
		$features = $registry->get_features();

		$this->assertArrayHasKey( 'author-bio', $features );
		$this->assertStringContainsString( 'shortcode', $features['author-bio']['description'] );
		$this->assertSame( 'ELEMENTARY_FEATURE_AUTHOR_BIO', $registry->constant_name( 'author-bio' ) );
	}

	/**
	 * The shortcode's template part resolves through the theme template loader.
	 */
	public function test_template_part_resolves_through_the_theme_loader(): void {
		$located = Main::get_instance()->get_shared( Templates::class )->locate( 'author-bio' );

		$this->assertIsString( $located );
		$this->assertStringEndsWith( 'template-parts/author-bio.php', (string) $located );
	}
}
