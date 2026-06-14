<?php
/**
 * Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Define theme constants.
 */
function constants(): void {
	if ( ! defined( 'ELEMENTARY_THEME_VERSION' ) ) {
		define( 'ELEMENTARY_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
	}

	if ( ! defined( 'ELEMENTARY_THEME_PATH' ) ) {
		define( 'ELEMENTARY_THEME_PATH', untrailingslashit( get_template_directory() ) );
	}

	if ( ! defined( 'ELEMENTARY_THEME_BUILD_URI' ) ) {
		define( 'ELEMENTARY_THEME_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
	}

	if ( ! defined( 'ELEMENTARY_THEME_BUILD_DIR' ) ) {
		define( 'ELEMENTARY_THEME_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
	}

	if ( ! defined( 'ELEMENTARY_THEME_ENABLE_TAILWIND' ) ) {
		define( 'ELEMENTARY_THEME_ENABLE_TAILWIND', file_exists( get_template_directory() . '/src/css/frontend/tailwind.css' ) );
	}
}

constants();

// If the autoloader fails, we cannot proceed.
require_once ELEMENTARY_THEME_PATH . '/inc/Autoloader.php';
if ( ! class_exists( Autoloader::class ) || ! Autoloader::autoload() ) {
	return;
}

// Instantiate the theme.
if ( class_exists( Main::class ) ) {
	Main::get_instance();
}
