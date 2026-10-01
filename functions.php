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
		define( 'ELEMENTARY_THEME_ENABLE_TAILWIND', false );
	}
}

constants();

// If the autoloader fails, we cannot proceed.
require_once ELEMENTARY_THEME_PATH . '/inc/Autoloader.php';
if ( ! class_exists( Autoloader::class ) || ! Autoloader::autoload() ) {
	return;
}

// Instantiate the theme on after_setup_theme. Its features translate their
// names and descriptions when they register, and WordPress 6.7+ reports
// translations requested before that action as too early; wp-env displays the
// notice, which broke wp-admin login. Nothing runs between loading this file
// and after_setup_theme, so the hooks the classes add for it still fire.
if ( class_exists( Main::class ) ) {
	if ( did_action( 'after_setup_theme' ) ) {
		Main::get_instance();
	} else {
		add_action(
			'after_setup_theme',
			static function (): void {
				Main::get_instance();
			},
			0
		);
	}
}
