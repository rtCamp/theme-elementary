<?php
/**
 * Author bio shortcode.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Shortcodes;

use rtCamp\Theme\Elementary\Core\Features;
use rtCamp\Theme\Elementary\Helpers\Util;
use rtCamp\WPFramework\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class AuthorBio
 *
 * Example consumer of the theme's TemplateLoader. Registers the
 * `[elementary_author_bio]` shortcode, which renders the `author-bio`
 * template part through Util::get_template() — a child theme can override the
 * markup by shipping its own template-parts/author-bio.php.
 *
 * Gated behind the `author-bio` feature flag (Settings → Features), enabled
 * by default; toggling the flag takes effect on the next request, since
 * registration is decided once at load.
 *
 * @since 1.0.0
 */
final class AuthorBio implements ConditionallyRegistrable {

	/**
	 * {@inheritDoc}
	 *
	 * Runs during Main's load — Util::is_feature_enabled() / get_shared()
	 * would re-enter the Singleton here, so construct a Features instance
	 * directly (see the Features docblock for why that is equivalent).
	 */
	public function can_register(): bool {
		return ( new Features() )->is_enabled( Features::AUTHOR_BIO );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_shortcode( 'elementary_author_bio', [ $this, 'render' ] );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 *
	 * @return string Rendered author-bio markup, or '' when unavailable.
	 */
	public function render( array|string $atts = [] ): string {
		$atts = shortcode_atts(
			[ 'user_id' => (string) get_the_author_meta( 'ID' ) ],
			(array) $atts,
			'elementary_author_bio'
		);

		$user_id = (int) $atts['user_id'];

		if ( $user_id <= 0 ) {
			return '';
		}

		return Util::get_template(
			'author-bio',
			null,
			[
				'name'   => get_the_author_meta( 'display_name', $user_id ),
				'bio'    => get_the_author_meta( 'description', $user_id ),
				'avatar' => get_avatar( $user_id, 64 ),
			]
		);
	}
}
