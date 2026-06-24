<?php
/**
 * Author bio shortcode.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Shortcodes;

use rtCamp\Theme\Elementary\Abstracts\AbstractThemeFeature;
use rtCamp\Theme\Elementary\Helpers\Util;

/**
 * Class AuthorBio
 *
 * Registers the [elementary_author_bio] shortcode, which renders the
 * `author-bio` template part through Util::get_template().
 *
 * Gated behind the `author-bio` feature flag (Settings → Features), enabled
 * by default; toggling the flag takes effect on the next request, since
 * registration is decided once at load.
 *
 * @since 1.0.0
 */
final class AuthorBio extends AbstractThemeFeature {

	/**
	 * {@inheritDoc}
	 */
	protected function get_slug(): string {
		return 'author-bio';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_description(): string {
		return __( 'Enables the [elementary_author_bio] shortcode, which renders an author biography block on posts and pages.', 'elementary-theme' );
	}

	/**
	 * {@inheritDoc}
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
