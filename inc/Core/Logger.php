<?php
/**
 * Theme logger service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\Utils\Logger as FrameworkLogger;

/**
 * Class Logger
 *
 * The theme's shared Logger. Extends the framework Logger, prefixed with the
 * theme slug, and shared through the container so every part of the theme
 * writes through one instance (like a singleton, but via the framework
 * Shareable contract). Stays silent unless WP_DEBUG. Access it from anywhere
 * via Helpers\Util::logger().
 *
 * @since 1.0.0
 */
final class Logger extends FrameworkLogger implements Shareable {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'elementary_theme' );
	}
}
