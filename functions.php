<?php
/**
 * Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package rtCamp\Theme\Elementary
 */

if ( ! defined( 'ELEMENTARY_THEME_VERSION' ) ) :
	define( 'ELEMENTARY_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
endif;

if ( ! defined( 'ELEMENTARY_THEME_TEMP_DIR' ) ) :
	define( 'ELEMENTARY_THEME_TEMP_DIR', untrailingslashit( get_template_directory() ) );
endif;

if ( ! defined( 'ELEMENTARY_THEME_BUILD_URI' ) ) :
	define( 'ELEMENTARY_THEME_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
endif;

if ( ! defined( 'ELEMENTARY_THEME_BUILD_DIR' ) ) :
	define( 'ELEMENTARY_THEME_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
endif;

require_once ELEMENTARY_THEME_TEMP_DIR . '/inc/Autoloader.php';

if ( ! class_exists( 'rtCamp\Theme\Elementary\Autoloader' ) || ! rtCamp\Theme\Elementary\Autoloader::autoload() ) {
	return;
}

/**
 * Theme bootstrap instance.
 *
 * @since 1.0.0
 *
 * @return object Theme bootstrap instance.
 */
function elementary_theme_instance() {

	return rtCamp\Theme\Elementary\Main::get_instance();
}

// Instantiate theme.
elementary_theme_instance();
