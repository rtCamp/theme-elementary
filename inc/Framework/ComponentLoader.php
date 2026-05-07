<?php
/**
 * Component loader for resolving and rendering PHP component partials.
 *
 * Resolves components from theme or plugin paths with configurable priority.
 * Components are render-only PHP files that receive data as arguments and output HTML.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Framework;

/**
 * Class ComponentLoader
 *
 * @since 1.0.0
 */
class ComponentLoader {

	/**
	 * Render a component by name.
	 *
	 * Resolves the component file based on registered paths and priority,
	 * then includes it with the provided arguments available in scope.
	 *
	 * @param string $name    Component name (e.g. 'Button', 'Card').
	 * @param array  $args    Arguments to pass to the component.
	 * @param array  $options {
	 *     Optional. Resolution options.
	 *
	 *     @type string $priority Resolution priority: 'theme' or 'plugin'. Default determined by filter.
	 * }
	 *
	 * @return void
	 */
	public static function render( string $name, array $args = [], array $options = [] ): void {

		$file = self::get_component_file( $name, $options );

		if ( false === $file ) {
			return;
		}

		require $file;
	}

	/**
	 * Get the rendered HTML of a component as a string.
	 *
	 * Uses output buffering to capture the component output instead of
	 * sending it directly to the browser.
	 *
	 * @param string $name    Component name (e.g. 'Button', 'Card').
	 * @param array  $args    Arguments to pass to the component.
	 * @param array  $options {
	 *     Optional. Resolution options.
	 *
	 *     @type string $priority Resolution priority: 'theme' or 'plugin'. Default determined by filter.
	 * }
	 *
	 * @return string Rendered component HTML, or empty string if not found.
	 */
	public static function get( string $name, array $args = [], array $options = [] ): string {

		ob_start();
		self::render( $name, $args, $options );

		return (string) ob_get_clean();
	}

	/**
	 * Resolve the component file path.
	 *
	 * Checks registered paths in priority order and returns the first match.
	 * Path format: {source_path}/{Name}/{Name}.php
	 *
	 * @param string $name    Component name.
	 * @param array  $options Resolution options.
	 *
	 * @return string|false Full file path on success, false if not found.
	 */
	private static function get_component_file( string $name, array $options = [] ): string|false {

		$component_name = self::normalize_component_name( $name );

		if ( false === $component_name ) {
			return false;
		}

		$priority = self::get_priority( $options );

		/**
		 * Filters the registered component paths.
		 *
		 * Each entry is keyed by source type ('theme', 'plugin') and maps
		 * to a directory path where components are stored.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $paths    Associative array of source => directory path.
		 * @param string $name     Component name being resolved.
		 * @param array  $options  Options passed to render().
		 */
		$paths = apply_filters(
			'elementary_theme_component_paths',
			[
				'theme' => ELEMENTARY_THEME_TEMP_DIR . '/src/Components',
			],
			$component_name,
			$options
		);

		$paths = self::sanitize_component_paths( $paths );

		if ( empty( $paths ) ) {
			return false;
		}

		// Order sources based on priority.
		$order = self::get_source_order( $priority, $paths );

		foreach ( $order as $source ) {

			if ( empty( $paths[ $source ] ) ) {
				continue;
			}

			$file = trailingslashit( $paths[ $source ] ) . $component_name . '/' . $component_name . '.php';

			if ( file_exists( $file ) && is_readable( $file ) ) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * Sanitize component path mappings returned by filters.
	 *
	 * @param mixed $paths Potentially filtered paths value.
	 *
	 * @return array<string, string> Valid source => path mappings.
	 */
	private static function sanitize_component_paths( mixed $paths ): array {
		if ( ! is_array( $paths ) ) {
			return [];
		}

		$sanitized_paths = [];

		foreach ( $paths as $source => $path ) {
			if ( ! is_string( $source ) || ! is_string( $path ) ) {
				continue;
			}

			$source = trim( $source );
			$path   = trim( $path );

			if ( '' === $source || '' === $path ) {
				continue;
			}

			$sanitized_paths[ $source ] = $path;
		}

		return $sanitized_paths;
	}

	/**
	 * Normalize and validate a component name before using it in filesystem paths.
	 *
	 * Normalization trims surrounding whitespace. Validation then enforces
	 * length bounds, blocks traversal and path separators, and allows only
	 * alphanumeric characters, underscores and dashes.
	 *
	 * @param string $name Component name to normalize and validate.
	 *
	 * @return string|false Normalized component name, or false when invalid.
	 */
	private static function normalize_component_name( string $name ): string|false {
		$name = trim( $name );

		if (
			'' === $name ||
			strlen( $name ) > 128 ||
			str_contains( $name, '..' ) ||
			str_contains( $name, '/' ) ||
			str_contains( $name, '\\' ) ||
			1 !== preg_match( '/^[A-Za-z0-9_-]+$/', $name )
		) {
			return false;
		}

		return $name;
	}

	/**
	 * Get the resolution priority.
	 *
	 * @param array $options Options array potentially containing 'priority'.
	 *
	 * @return string 'theme' or 'plugin'.
	 */
	private static function get_priority( array $options ): string {

		if ( ! empty( $options['priority'] ) && in_array( $options['priority'], [ 'theme', 'plugin' ], true ) ) {
			return $options['priority'];
		}

		/**
		 * Filters the default component resolution priority.
		 *
		 * @since 1.0.0
		 *
		 * @param string $priority Default priority. Accepts 'theme' or 'plugin'.
		 */
		$default = apply_filters( 'elementary_theme_component_default_priority', 'theme' );

		if ( in_array( $default, [ 'theme', 'plugin' ], true ) ) {
			return $default;
		}

		return 'theme';
	}

	/**
	 * Get the source resolution order based on priority.
	 *
	 * @param string $priority 'theme' or 'plugin'.
	 * @param array  $paths    Registered paths keyed by source.
	 *
	 * @return array Ordered list of source keys to check.
	 */
	private static function get_source_order( string $priority, array $paths ): array {

		$sources = array_keys( $paths );

		if ( 'plugin' === $priority ) {
			// Move 'plugin' to front if it exists.
			$key = array_search( 'plugin', $sources, true );

			if ( false !== $key ) {
				unset( $sources[ $key ] );
				array_unshift( $sources, 'plugin' );
			}
		} else {
			// Move 'theme' to front if it exists.
			$key = array_search( 'theme', $sources, true );

			if ( false !== $key ) {
				unset( $sources[ $key ] );
				array_unshift( $sources, 'theme' );
			}
		}

		return array_values( $sources );
	}
}
