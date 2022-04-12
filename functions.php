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

if ( ! function_exists( 'elementary_support' ) ) :
	/**
	 * Add required theme support.
	 *
	 * @since 1.0.0
	 */
	function elementary_support() {
		// Add support for core block styles.
		add_theme_support( 'wp-block-styles' );
	}

endif;

add_action( 'after_setup_theme', 'elementary_support' );

require_once ELEMENTARY_TEMP_DIR . '/vendor/autoload.php';

// Add block patterns.
$elementary_block_patterns_register = new \Elementary\Patterns\Block_Patterns();
add_action( 'init', array( $elementary_block_patterns_register, 'elementary_register_block_patterns_categories' ) );
add_action( 'init', array( $elementary_block_patterns_register, 'elementary_register_block_patterns' ) );
