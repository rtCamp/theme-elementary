<?php
/**
 * Autoloader Trait for WordPress plugins.
 *
 * Wraps the Composer autoloader to provide graceful failure if it is missing.
 *
 * @package Elementary_Theme;
 */

declare( strict_types = 1 );

namespace Elementary_Theme\Kernel;

/**
 * Class - AutoLoaderTrait
 */
trait AutoLoaderTrait {
	/**
	 * The Error message to display when the autoloader errors.
	 *
	 * We stick it in a function, so it's available to `missing_autoloader_notice()` without prop drilling into the hook.
	 */
	abstract protected static function get_autoloader_error_message(): string;

	/**
	 * Attempts to load the autoloader file, if it exists.
	 *
	 * @param string $autoloader_file The path to the autoloader file.
	 *
	 * @return bool Whether the autoloader was successfully loaded.
	 */
	private static function require_autoloader( string $autoloader_file ): bool {
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
	 * Display the admin notice for the error message.
	 */
	public static function display_admin_notice(): void {
		$error_message = self::get_autoloader_error_message();
		_doing_it_wrong( self::class, esc_html( $error_message ), '0.0.1' );

		// Display the error notice in the admin.
		wp_admin_notice(
			esc_html( $error_message ),
			[
				'type'    => 'error',
				'dismiss' => false,
			]
		);
	}

	/**
	 * Displays a notice if the autoloader is missing.
	 */
	private static function missing_autoloader_notice(): void {
		$hooks = [
			'admin_notices',
			'network_admin_notices',
		];

		foreach ( $hooks as $hook ) {
			add_action(
				$hook,
				[ self::class, 'display_admin_notice' ]
			);
		}
	}
}
