<?php
/**
 * Autoloader for PHP classes inside Theme Elementary.
 *
 * Provides graceful failure if the Composer autoloader is missing.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

/**
 * Class Autoloader
 *
 * @since 1.0.0
 */
final class Autoloader {

	/**
	 * Attempts to autoload the Composer dependencies.
	 *
	 * If the autoloader is missing, it will display an admin notice.
	 *
	 * @return bool True if the autoloader was successfully loaded, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public static function autoload(): bool {
		$autoloader = ELEMENTARY_THEME_PATH . '/vendor/autoload.php';

		if ( ! is_readable( $autoloader ) ) {
			self::missing_autoloader_notice();
			return false;
		}

		require_once $autoloader; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		return true;
	}

	/**
	 * Displays a notice if the autoloader is missing.
	 *
	 * @since 1.0.0
	 */
	private static function missing_autoloader_notice(): void {
		$error_message = sprintf(
			/* translators: %s: The theme name. */
			__( '%s: The Composer autoloader was not found. If you installed the theme from the GitHub source code, make sure to run `composer install`.', 'elementary-theme' ),
			esc_html( 'Elementary Theme' )
		);

		_doing_it_wrong( esc_html( self::class ), esc_html( $error_message ), '1.0.0' );

		add_action(
			'admin_notices',
			static function () use ( $error_message ): void {
				wp_admin_notice(
					esc_html( $error_message ),
					[
						'type'    => 'error',
						'dismiss' => false,
					]
				);
			}
		);
	}
}
