<?php
/**
 * Autoloader for PHP classes inside a WordPress plugin.
 *
 * Wraps the Composer autoloader to provide graceful failure if it is missing.
 *
 * @package Elementary_Theme;
 */

declare( strict_types = 1 );

namespace Elementary_Theme;

// Load the class manually if it does not exist.
if ( ! trait_exists( 'Elementary_Theme\Kernel\AutoloaderTrait' ) ) {
	require_once ELEMENTARY_THEME_TEMPLATE_DIR . '/kernel/AutoloaderTrait.php';
}

/**
 * Class - Autoloader
 */
final class Autoloader {
	use Kernel\AutoloaderTrait;

	/**
	 * Attempts to autoload the Composer dependencies.
	 *
	 * If the autoloader is missing, it will display an admin notice and log an error.
	 */
	public static function autoload(): bool {

		// Return true if autoload is disabled.
		if ( defined( 'ELEMENTARY_THEME_DISABLE_AUTOLOAD' ) && false === ELEMENTARY_THEME_DISABLE_AUTOLOAD ) {
			return true;
		}

		$autoloader = ELEMENTARY_THEME_TEMPLATE_DIR . '/vendor/autoload.php';

		return self::require_autoloader( $autoloader );
	}

	/**
	 * Returns the error message for the autoloader.
	 */
	protected static function get_autoloader_error_message(): string {
		return sprintf(
			/* translators: %s: The plugin name. */
			__( '%s: The Composer autoloader was not found. If you installed the plugin from the GitHub source code, make sure to run `composer install`.', 'personal-theme' ),
			esc_html( 'personal-theme' )
		);
	}
}
