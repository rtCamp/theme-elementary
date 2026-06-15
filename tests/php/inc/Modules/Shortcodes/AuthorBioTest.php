<?php
/**
 * Test AuthorBio shortcode.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Core\Features;
use rtCamp\Theme\Elementary\Modules\Shortcodes\AuthorBio;
use rtCamp\WPFramework\Contracts\Interfaces\ConditionallyRegistrable;

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
	 * AuthorBio instance.
	 *
	 * @var AuthorBio
	 */
	private AuthorBio $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new AuthorBio();
		$this->instance->register_hooks();
	}

	/**
	 * The shortcode is registered by register_hooks().
	 */
	public function test_registers_shortcode(): void {
		$this->assertTrue( shortcode_exists( 'elementary_author_bio' ) );
	}

	/**
	 * The module is gated behind the `author-bio` feature flag: on by
	 * default, off once the flag is disabled.
	 */
	public function test_registration_is_gated_by_feature_flag(): void {
		$this->assertInstanceOf( ConditionallyRegistrable::class, $this->instance );
		$this->assertTrue( $this->instance->can_register() );

		( new Features() )->disable( Features::AUTHOR_BIO );

		$this->assertFalse( $this->instance->can_register() );
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
}
