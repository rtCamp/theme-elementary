<?php
/**
 * Autoloader Trait for WordPress Themes.
 *
 * Wraps the Composer autoloader to provide graceful failure if it is missing.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Framework\Traits;

/**
 * Trait AutoloaderTrait
 *
 * @since 1.0.0
 */
trait AutoloaderTrait {

	/**
	 * The Error message to display when the autoloader errors.
	 *
	 * We stick it in a function, so it's available to `missing_autoloader_notice()` without prop drilling into the hook.
	 */
	abstract protected static function get_autoloader_error_message();

	/**
	 * Attempts to load the autoloader file, if it exists.
	 *
	 * @param string $autoloader_file The path to the autoloader file.
	 *
	 * @return bool Whether the autoloader was successfully loaded.
	 *
	 * @since 1.0.0
	 */
	private static function require_autoloader( $autoloader_file ): bool {
		// Use a local static variable to track if the autoloader has already been loaded.
		static $loaded = [];
		if ( isset( $loaded[ $autoloader_file ] ) ) {
			return $loaded[ $autoloader_file ];
		}

		if ( ! is_readable( $autoloader_file ) ) {
			self::missing_autoloader_notice();
			return false;
		}

		$loaded[ $autoloader_file ] = (bool) require_once $autoloader_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Autoloader is a Composer file.

		return $loaded[ $autoloader_file ];
	}

	/**
	 * Displays a notice if the autoloader is missing.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private static function missing_autoloader_notice(): void {
		$hooks = [
			'admin_notices',
			'network_admin_notices',
		];

		foreach ( $hooks as $hook ) {
			add_action(
				$hook,
				static function () {
					$error_message = self::get_autoloader_error_message();
					_doing_it_wrong( self::class, esc_html( $error_message ), '0.0.1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					// Display the error notice in the admin.
					if ( function_exists( 'wp_admin_notice' ) ) {
						wp_admin_notice(
							esc_html( $error_message ),
							[
								'type'    => 'error',
								'dismiss' => false,
							]
						);
					} else {
						echo '<div class="notice notice-error"><p>' . esc_html( $error_message ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			);
		}
	}
}
