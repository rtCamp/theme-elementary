<?php
/**
 * Autoloader for PHP classes inside a Theme Elementary.
 *
 * Wraps the Composer autoloader to provide graceful failure if it is missing.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

if ( ! trait_exists( 'rtCamp\Theme\Elementary\Framework\Traits\AutoloaderTrait' ) ) {
	require_once ELEMENTARY_THEME_TEMP_DIR . '/inc/Framework/Traits/AutoloaderTrait.php';
}

/**
 * Class Autoloader
 */
final class Autoloader {

	use Framework\Traits\AutoloaderTrait;
	/**
	 * Attempts to autoload the Composer dependencies.
	 *
	 * If the autoloader is missing, it will display an admin notice and log an error.
	 *
	 * @return bool True if the autoloader was successfully loaded, false otherwise.
	 */
	public static function autoload(): bool {
		$autoloader = ELEMENTARY_THEME_TEMP_DIR . '/vendor/autoload.php';

		return self::require_autoloader( $autoloader );
	}

	/**
	 * The error message to display when the autoloader is missing.
	 *
	 * @return string The error message to display.
	 */
	protected static function get_autoloader_error_message(): string {
		return sprintf(
			/* translators: %s: The theme name. */
			__( '%s: The Composer autoloader was not found. If you installed the theme from the GitHub source code, make sure to run `composer install`.', 'elementary-theme' ),
			esc_html( 'Elementary Theme' )
		);
	}
}
