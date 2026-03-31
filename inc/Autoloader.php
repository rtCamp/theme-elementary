<?php
/**
 * Autoloader for PHP classes inside a WordPress plugin.
 *
 * Wraps the Composer autoloader to provide graceful failure if it is missing.
 *
 * @package Elementary-Theme
 */

namespace rtCamp\Theme\Elementary;

if ( ! class_exists( 'rtCamp\Plugin_Skeleton_D\Framework\AutoloaderTrait' ) ) {
	require_once ELEMENTARY_THEME_TEMP_DIR . '/inc/Framework/Traits/AutoloaderTrait.php';
}

/**
 * Class - Autoloader
 */
final class Autoloader {

	use Framework\Traits\AutoloaderTrait;
	/**
	 * Attempts to autoload the Composer dependencies.
	 *
	 * If the autoloader is missing, it will display an admin notice and log an error.
	 */
	public static function autoload() {
		$autoloader = ELEMENTARY_THEME_TEMP_DIR . '/vendor/autoload.php';

		return self::require_autoloader( $autoloader );
	}

	/**
	 * The error message to display when the autoloader is missing.
	 */
	private static function get_autoloader_error_message() {
		return sprintf(
			/* translators: %s: The plugin name. */
			__( '%s: The Composer autoloader was not found. If you installed the plugin from the GitHub source code, make sure to run `composer install`.', 'elementary-theme' ),
			esc_html( 'Elementary Theme' )
		);
	}
}
