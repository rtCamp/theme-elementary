<?php
/**
 * Define custom functions for the theme.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Framework\ComponentLoader;

if ( ! function_exists( 'elementary_theme_component' ) ) {

	/**
	 * Render a component by name.
	 *
	 * Global convenience wrapper for ComponentLoader::render().
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Component name (e.g. 'Button', 'Card').
	 * @param array  $args    Arguments to pass to the component.
	 * @param array  $options Optional. Resolution options. See ComponentLoader::render().
	 *
	 * @return void
	 */
	function elementary_theme_component( string $name, array $args = [], array $options = [] ): void {
		ComponentLoader::render( $name, $args, $options );
	}
}

if ( ! function_exists( 'elementary_theme_get_component' ) ) {

	/**
	 * Get the rendered HTML of a component as a string.
	 *
	 * Global convenience wrapper for ComponentLoader::get().
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Component name (e.g. 'Button', 'Card').
	 * @param array  $args    Arguments to pass to the component.
	 * @param array  $options Optional. Resolution options. See ComponentLoader::get().
	 *
	 * @return string Rendered component HTML.
	 */
	function elementary_theme_get_component( string $name, array $args = [], array $options = [] ): string {
		return ComponentLoader::get( $name, $args, $options );
	}
}
