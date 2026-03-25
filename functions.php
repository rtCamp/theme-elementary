<?php
/**
 * Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Elementary-Theme
 */

/**
 * Initializes the constant for the theme.
 *
 * @return void
 */
function elementary_theme_init_constants() {
	if ( ! defined( 'ELEMENTARY_THEME_VERSION' ) ) {
		define( 'ELEMENTARY_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
	}

	if ( ! defined( 'ELEMENTARY_THEME_TEMPLATE_DIR' ) ) {
		define( 'ELEMENTARY_THEME_TEMPLATE_DIR', untrailingslashit( get_template_directory() ) );
	}

	if ( ! defined( 'ELEMENTARY_THEME_BUILD_URI' ) ) {
		define( 'ELEMENTARY_THEME_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
	}

	if ( ! defined( 'ELEMENTARY_THEME_BUILD_DIR' ) ) {
		define( 'ELEMENTARY_THEME_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
	}
}

elementary_theme_init_constants();
require_once ELEMENTARY_THEME_TEMPLATE_DIR . '/inc/Autoloader.php';

// If autoloader class does not exist or autoload invocation has any error we must quit early.
if ( ! class_exists( 'Elementary_Theme\Autoloader' ) || ! Elementary_Theme\Autoloader::autoload() ) {
	return;
}

/**
 * Theme bootstrap instance.
 *
 * @since 1.0.0
 *
 * @return object Theme bootstrap instance.
 */
function elementary_theme_init() {
	return Elementary_Theme\Main::get_instance();
}

// Instantiate theme.
elementary_theme_init();
