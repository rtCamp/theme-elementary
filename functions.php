<?php
/**
 * Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Elementary
 */

if ( ! defined( 'ELEMENTARY_VERSION' ) ) :
	define( 'ELEMENTARY_VERSION', wp_get_theme()->get( 'Version' ) );
endif;

if ( ! defined( 'ELEMENTARY_TEMP_DIR' ) ) :
	define( 'ELEMENTARY_TEMP_DIR', untrailingslashit( get_template_directory() ) );
endif;

if ( ! defined( 'ELEMENTARY_BUILD_URI' ) ) :
	define( 'ELEMENTARY_BUILD_URI', untrailingslashit( get_template_directory_uri() ) . '/assets/build' );
endif;

if ( ! defined( 'ELEMENTARY_BUILD_DIR' ) ) :
	define( 'ELEMENTARY_BUILD_DIR', untrailingslashit( get_template_directory() ) . '/assets/build' );
endif;

require_once ELEMENTARY_TEMP_DIR . '/vendor/autoload.php';

/**
 * Theme bootstrap instance.
 *
 * @since 1.0.0
 *
 * @return object Elementary
 */
function elementary_instance() {
	return Elementary\Elementary::get_instance();
}

// Instantiate theme.
elementary_instance();
