<?php
/**
 * Define custom functions for the theme.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Components\ThemeComponentLoader;
use rtCamp\Theme\Elementary\Main;

if ( ! function_exists( 'elementary_theme_component' ) ) {

	/**
	 * Render a component by name.
	 *
	 * Global convenience wrapper for ThemeComponentLoader::render().
	 *
	 * @since 1.0.0
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options Optional. Resolution options. See ThemeComponentLoader::render().
	 *
	 * @return void
	 */
	function elementary_theme_component( string $name, array $args = [], array $options = [] ): void {
		/** @var ThemeComponentLoader $loader */
		$loader = Main::get_instance()->get_shared( ThemeComponentLoader::class );
		$loader->render( $name, $args, $options );
	}
}

if ( ! function_exists( 'elementary_theme_get_component' ) ) {

	/**
	 * Get the rendered HTML of a component as a string.
	 *
	 * Global convenience wrapper for ThemeComponentLoader::get().
	 *
	 * @since 1.0.0
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options Optional. Resolution options. See ThemeComponentLoader::get().
	 *
	 * @return string Rendered component HTML.
	 */
	function elementary_theme_get_component( string $name, array $args = [], array $options = [] ): string {
		/** @var ThemeComponentLoader $loader */
		$loader = Main::get_instance()->get_shared( ThemeComponentLoader::class );
		return $loader->get( $name, $args, $options );
	}
}
