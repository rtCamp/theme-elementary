<?php
/**
 * Theme feature-flags service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\Utils\FeatureSelector;

/**
 * Class Features
 *
 * The theme's feature-flag registry. Extends the framework FeatureSelector
 * with the `elementary` context, so flag state derives as:
 *
 *   - option key:        elementary_feature_{flag}   (toggled in admin under
 *                        Settings → Features, see Modules\Settings\FeaturesSettingsPage)
 *   - override constant: ELEMENTARY_FEATURE_{FLAG}   (wp-config.php; wins over
 *                        the option and locks the admin checkbox)
 *
 * Flags default to disabled. Read them at hook time through
 * Helpers\Util::is_feature_enabled(); load-time consumers (constructors,
 * ConditionallyRegistrable::can_register()) must construct their own
 * `new Features()` instead — Main::get_instance()->get_shared() would re-enter
 * the Singleton mid-construction, since the instance is only assigned after
 * Main's constructor (and thus the Loader) finishes. A fresh instance is
 * equivalent to the shared one: the registry is rebuilt identically in the
 * constructor and all toggle state lives in options/constants.
 *
 * Flag labels are intentionally not translated here: this constructor runs
 * when functions.php boots Main, before the theme text domain loads. The
 * settings page reads {@see Features::get_features()} lazily at `admin_init`,
 * so translated labels are supplied there instead.
 *
 * @since 1.0.0
 */
final class Features extends FeatureSelector implements Shareable {

	/**
	 * Flag gating the AuthorBio shortcode module.
	 */
	public const AUTHOR_BIO = 'author-bio';

	/**
	 * Flag gating the MediaTextInteractive block extension.
	 */
	public const MEDIA_TEXT_INTERACTIVE = 'media-text-interactive';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'elementary' );

		$this->register(
			[
				self::AUTHOR_BIO,
				self::MEDIA_TEXT_INTERACTIVE,
			]
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Merges translated display labels onto the registered flags. Safe to
	 * translate here: this is only read lazily (the settings page calls it at
	 * `admin_init`), after the theme text domain has loaded.
	 */
	public function get_features(): array {
		$labels = [
			self::AUTHOR_BIO             => [
				'name'        => __( 'Author bio shortcode', 'elementary-theme' ),
				'description' => __( 'Registers the [elementary_author_bio] shortcode rendering the author-bio template part.', 'elementary-theme' ),
			],
			self::MEDIA_TEXT_INTERACTIVE => [
				'name'        => __( 'Interactive media & text', 'elementary-theme' ),
				'description' => __( 'Enhances core button, columns, and video blocks with the media-text interactivity behavior.', 'elementary-theme' ),
			],
		];

		$features = parent::get_features();

		foreach ( $labels as $slug => $meta ) {
			if ( isset( $features[ $slug ] ) ) {
				$features[ $slug ] = array_merge( $features[ $slug ], $meta );
			}
		}

		return $features;
	}
}
