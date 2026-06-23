<?php
/**
 * Theme feature registry.
 *
 * @package rtCamp\Theme\Elementary\Core
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\Utils\FeatureSelector;

/**
 * Class FeatureRegistry
 *
 * The theme's shared feature-flag registry. Flags are registered into it by
 * each ThemeFeature subclass on construction, so the admin settings page
 * discovers them automatically without a central list.
 *
 * Shared option:      elementary_features  (one row; value is {flag_key => bool})
 * Override constants: ELEMENTARY_FEATURE_{FLAG}
 *
 * @since 1.0.0
 */
final class FeatureRegistry extends FeatureSelector implements Shareable {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'elementary' );
	}
}
