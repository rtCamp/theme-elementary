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
	 * Resolved component metadata cache.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $component_data_cache = [];

	/**
	 * Render a component by name.
	 *
	 * Resolves the component file based on registered paths and priority,
	 * then includes it with the provided arguments available in scope.
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options {
	 *     Optional. Resolution and asset enqueue options.
	 *
	 *     @type string $priority Resolution priority: 'theme' or 'plugin'. Default determined by filter.
	 *     @type bool   $script   Whether to enqueue the component's script. Default determined by filter.
	 *     @type bool   $style    Whether to enqueue the component's style. Default determined by filter.
	 * }
	 *
	 * @return void
	 */
	public static function render( string $name, array $args = [], array $options = [] ): void {

		$options   = self::get_render_options( $options );
		$component = self::get_component_data( $name, $options );

		if ( false === $component ) {
			return;
		}

		$options['component'] = $component;

		do_action( 'elementary_theme_before_get_component', $name, $args, $options );

		self::require_component_file( (string) $component['file'], $args, $options );

		self::enqueue_component_assets( $component, $options );

		do_action( 'elementary_theme_after_get_component', $name, $args, $options );
	}

	/**
	 * Get normalized render options.
	 *
	 * @param array<string, mixed> $options Render options.
	 *
	 * @return array<string, mixed> Render options with enqueue settings resolved.
	 */
	private static function get_render_options( array $options ): array {
		/**
		 * Filters the default enqueue settings for elementary theme components.
		 *
		 * This filter allows developers to modify whether scripts and styles
		 * should be enqueued by default for the theme component.
		 *
		 * @param array<string, bool> $defaults {
		 * Default enqueue settings.
		 *
		 * @type bool $script Whether to enqueue the component's script. Default true.
		 * @type bool $style  Whether to enqueue the component's style. Default true.
		 * }
		 */
		$enqueue = apply_filters(
			'elementary_theme_component_enqueue_defaults',
			[
				'script' => true,
				'style'  => true,
			]
		);

		if ( ! is_array( $enqueue ) ) {
			$enqueue = [];
		}

		$enqueue = wp_parse_args(
			$options,
			$enqueue
		);

		$options['script'] = ! empty( $enqueue['script'] );
		$options['style']  = ! empty( $enqueue['style'] );

		return $options;
	}

	/**
	 * Get the rendered HTML of a component as a string.
	 *
	 * Uses output buffering to capture the component output instead of
	 * sending it directly to the browser.
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options {
	 *     Optional. Resolution options.
	 *
	 *     @type string $priority Resolution priority: 'theme' or 'plugin'. Default determined by filter.
	 *     @type bool   $script   Whether to enqueue the component's script. Default determined by filter.
	 *     @type bool   $style    Whether to enqueue the component's style. Default determined by filter.
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
	 * Require a component file in render scope.
	 *
	 * @param string               $file    Component file path.
	 * @param array<string, mixed> $args    Component arguments.
	 * @param array<string, mixed> $options Component render options.
	 *
	 * @return void
	 */
	private static function require_component_file( string $file, array $args, array $options ): void {
		require $file;
	}

	/**
	 * Resolve the component file path.
	 *
	 * Checks registered paths in priority order and returns the first match.
	 * Path format: {source_path}/{Name}/{Name}.php
	 *
	 * @param string               $name    Component name.
	 * @param array<string, mixed> $options Resolution options.
	 *
	 * @return array<string, mixed>|false Component metadata on success, false if not found.
	 */
	private static function get_component_data( string $name, array $options = [] ): array|false {

		$component_name = self::normalize_component_name( $name );

		if ( false === $component_name ) {
			return false;
		}

		$priority = self::get_priority( $options );

		/**
		 * Filters the registered component paths.
		 *
		 * Each entry is keyed by source type ('theme', 'plugin') and maps
		 * to a path config with PHP, style, and script locations.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, array<string, mixed>> $paths   Associative array of source => path config.
		 * @param string                              $name    Component name being resolved.
		 * @param array<string, mixed>                $options Options passed to render().
		 */
		$paths = apply_filters(
			'elementary_theme_component_paths',
			[
				'theme' => [
					'php'    => ELEMENTARY_THEME_TEMP_DIR . '/src/Components',
					'style'  => [
						'dir' => ELEMENTARY_THEME_BUILD_DIR . '/css/components',
						'url' => ELEMENTARY_THEME_BUILD_URI . '/css/components',
					],
					'script' => [
						'dir' => ELEMENTARY_THEME_BUILD_DIR . '/js/components',
						'url' => ELEMENTARY_THEME_BUILD_URI . '/js/components',
					],
				],
			],
			$component_name,
			$options
		);

		if ( empty( $paths ) || ! is_array( $paths ) ) {
			return false;
		}

		$cache_key = self::get_cache_key( [ $component_name, $priority, $paths, $options['script'] ?? false, $options['style'] ?? false ] );

		if ( isset( self::$component_data_cache[ $cache_key ] ) ) {
			return self::$component_data_cache[ $cache_key ];
		}

		// Order sources based on priority.
		$order = self::get_source_order( $priority, $paths );

		foreach ( $order as $source ) {

			if (
				empty( $paths[ $source ] ) ||
				! is_array( $paths[ $source ] ) ||
				empty( $paths[ $source ]['php'] ) ||
				! is_string( $paths[ $source ]['php'] )
			) {
				continue;
			}

			$file = trailingslashit( $paths[ $source ]['php'] ) . $component_name . '/' . $component_name . '.php';

			if ( file_exists( $file ) && is_readable( $file ) ) {
				$component = [
					'name'   => $component_name,
					'source' => $source,
					'file'   => $file,
					'root'   => $paths[ $source ]['php'],
					'paths'  => $paths[ $source ],
					'assets' => self::get_component_assets( $component_name, $paths[ $source ], $options ),
				];

				self::$component_data_cache[ $cache_key ] = $component;

				return $component;
			}
		}

		return false;
	}

	/**
	 * Get component asset metadata.
	 *
	 * @param string               $component_name Component name.
	 * @param array<string, mixed> $paths          Component path config.
	 * @param array<string, mixed> $options        Component render options.
	 *
	 * @return array<string, array<string, string>> Asset metadata.
	 */
	private static function get_component_assets( string $component_name, array $paths, array $options ): array {
		if ( empty( $options['style'] ) && empty( $options['script'] ) ) {
			return [];
		}

		$assets = [];

		foreach (
			[
				'style'  => 'css',
				'script' => 'js',
			] as $asset_type => $extension
		) {
			if ( empty( $options[ $asset_type ] ) ) {
				continue;
			}

			if ( empty( $paths[ $asset_type ]['dir'] ) || empty( $paths[ $asset_type ]['url'] ) ) {
				continue;
			}

			$assets[ $asset_type ] = [
				'file' => trailingslashit( $paths[ $asset_type ]['dir'] ) . strtolower( $component_name ) . '.' . $extension,
				'url'  => trailingslashit( $paths[ $asset_type ]['url'] ) . strtolower( $component_name ) . '.' . $extension,
			];
		}

		return $assets;
	}

	/**
	 * Create a stable cache key for request-level lookup caches.
	 *
	 * @param array<mixed> $parts Cache key parts.
	 *
	 * @return string Cache key.
	 */
	private static function get_cache_key( array $parts ): string {
		$encoded_parts = wp_json_encode( $parts );

		return md5( is_string( $encoded_parts ) ? $encoded_parts : '' );
	}

	/**
	 * Enqueue assets for a rendered component.
	 *
	 * @param array<string, mixed> $component Component metadata.
	 * @param array<string, mixed> $options   Component render options.
	 *
	 * @return void
	 */
	private static function enqueue_component_assets( array $component, array $options ): void {
		if ( empty( $component['name'] ) || empty( $component['assets'] ) || ! is_array( $component['assets'] ) ) {
			return;
		}

		$slug = sanitize_key( (string) $component['name'] );

		if (
			! empty( $options['style'] ) &&
			! empty( $component['assets']['style'] ) &&
			is_array( $component['assets']['style'] )
		) {
			$handle = 'elementary-theme-component-' . $slug . '-style';

			if ( self::register_component_style( $handle, $component['assets']['style'] ) ) {
				wp_enqueue_style( $handle );
			}
		}

		if (
			! empty( $options['script'] ) &&
			! empty( $component['assets']['script'] ) &&
			is_array( $component['assets']['script'] )
		) {
			$handle = 'elementary-theme-component-' . $slug . '-script';

			if ( self::register_component_script( $handle, $component['assets']['script'] ) ) {
				wp_enqueue_script( $handle );
			}
		}
	}

	/**
	 * Register a component script.
	 *
	 * @param string               $handle    Name of the script. Should be unique.
	 * @param array<string, mixed> $asset     Component asset metadata.
	 * @param array<string>        $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null     $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool                 $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *
	 * @return bool Whether the script has been registered.
	 */
	private static function register_component_script( string $handle, array $asset, array $deps = [], string|bool|null $ver = false, bool $in_footer = true ): bool {
		if (
			empty( $asset['url'] ) ||
			empty( $asset['file'] ) ||
			! file_exists( $asset['file'] )
		) {
			return false;
		}

		$asset_meta = self::get_component_asset_meta( (string) $asset['file'], $deps, $ver );

		return wp_register_script( $handle, (string) $asset['url'], $asset_meta['dependencies'], $asset_meta['version'], $in_footer );
	}

	/**
	 * Register a component stylesheet.
	 *
	 * @param string               $handle Name of the stylesheet. Should be unique.
	 * @param array<string, mixed> $asset  Component asset metadata.
	 * @param array<string>        $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null     $ver    Optional. String specifying style version number, if not set, filetime will be used as version number.
	 * @param string               $media  Optional. The media for which this stylesheet has been defined.
	 *
	 * @return bool Whether the style has been registered.
	 */
	private static function register_component_style( string $handle, array $asset, array $deps = [], string|bool|null $ver = false, string $media = 'all' ): bool {
		if (
			empty( $asset['url'] ) ||
			empty( $asset['file'] ) ||
			! file_exists( $asset['file'] )
		) {
			return false;
		}

		$asset_meta = self::get_component_asset_meta( (string) $asset['file'], $deps, $ver );

		return wp_register_style( $handle, (string) $asset['url'], $asset_meta['dependencies'], $asset_meta['version'], $media );
	}

	/**
	 * Get component asset dependencies and version info from a matching .asset.php file.
	 *
	 * @param string           $file Asset file path.
	 * @param array<string>    $deps Asset dependencies to merge with.
	 * @param string|bool|null $ver  Asset version string.
	 *
	 * @return array{dependencies: array<string>, version: string|bool} Asset meta information including dependencies and version.
	 */
	private static function get_component_asset_meta( string $file, array $deps = [], string|bool|null $ver = false ): array {
		$normalized_file   = ltrim( str_replace( '\\', '/', $file ), '/' );
		$asset_meta_target = preg_replace( '/\.[^\/.]+$/', '', $normalized_file );
		$asset_meta_target = ! empty( $asset_meta_target ) ? $asset_meta_target : $normalized_file;
		$asset_meta_file   = '/' . $asset_meta_target . '.asset.php';
		$asset_meta        = is_readable( $asset_meta_file ) ? require $asset_meta_file : [];

		if ( ! is_array( $asset_meta ) ) {
			$asset_meta = [];
		}

		$dependencies = $asset_meta['dependencies'] ?? [];
		$version      = $asset_meta['version'] ?? self::get_component_file_version( $file, $ver );

		if ( ! is_array( $dependencies ) ) {
			$dependencies = [];
		}

		$dependencies = array_values( array_filter( $dependencies, 'is_string' ) );

		return [
			'dependencies' => array_merge( $deps, $dependencies ),
			'version'      => is_string( $version ) || is_bool( $version )
				? $version
				: ( is_int( $version ) ? (string) $version : self::get_component_file_version( $file, $ver ) ),
		];
	}

	/**
	 * Get component asset file version.
	 *
	 * @param string           $file File path.
	 * @param string|bool|null $ver  File version.
	 *
	 * @return string|bool File version based on file modification time or provided version.
	 */
	private static function get_component_file_version( string $file, string|bool|null $ver = false ): string|bool {
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		return file_exists( $file ) ? (string) filemtime( $file ) : false;
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
	 * @param array<string, mixed> $options Options array potentially containing 'priority'.
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
	 * @param string               $priority 'theme' or 'plugin'.
	 * @param array<string, mixed> $paths    Registered paths keyed by source.
	 *
	 * @return array<int, string> Ordered list of source keys to check.
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
