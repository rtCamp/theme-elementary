<?php
/**
 * Provides the base class for asset loading.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Kernel\Abstracts;

/**
 * Class Abstract_Asset_Loader
 *
 * @since 1.0.0
 */
abstract class Abstract_Asset_Loader {

	/**
	 * The base path of the build directory for assets.
	 *
	 * @var string
	 */
	private string $base_path;

	/**
	 * The base uri of the build directory for assets.
	 *
	 * @var string
	 */
	private string $base_uri;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// Setup hooks.
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'render_block', [ $this, 'enqueue_block_specific_assets' ], 10, 2 );
	}

	/**
	 * Initializes the required variables.
	 *
	 * @param string $base_path The Base Path for the asset build directory.
	 * @param string $base_uri  The Base URI for the assets build directory.
	 */
	protected function init( string $base_path, string $base_uri ) {
		$this->base_path = $base_path;
		$this->base_uri  = $base_uri;
	}

	/**
	 * Register a new script.
	 *
	 * @param string           $handle    Name of the stylesheet. Should be unique.
	 * @param string|bool      $file      stylesheet file, path of the stylesheet relative to the base directory.
	 * @param array            $deps      Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying stylesheet version number, if not set, filetime will be used as version number.
	 * @param bool             $media     Optional.  The media type for which this stylesheet is defined (e.g., 'all', 'screen', 'print'). Defaults to 'all'
	 *                                    Default 'false'.
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 */
	public function register_style( string $handle, string|bool $file, array $deps = [], string|bool|null $ver = false, $media = false ) {

		if ( empty( $this->base_path ) || empty( $this->base_uri ) ) {
			return false;
		}

		$file_path = sprintf( '%s/%s', $this->base_path, $file );

		if ( ! \file_exists( $file_path ) ) {
			return false;
		}

		$src        = sprintf( $this->base_uri . '/%s', $file );
		$asset_meta = $this->get_asset_meta( $file, $deps );

		return wp_register_style( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'] ?? $ver, $media );
	}

	/**
	 * Register a new script.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string|bool      $file       script file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 */
	public function register_script( $handle, $file, $deps = [], $ver = false, $in_footer = true ) {

		if ( empty( $this->base_path ) || empty( $this->base_uri ) ) {
			return false;
		}

		$file_path = sprintf( '%s/%s', $this->base_path, $file );

		if ( ! \file_exists( $file_path ) ) {
			return false;
		}

		$src        = sprintf( $this->base_uri . '/%s', $file );
		$asset_meta = $this->get_asset_meta( $file, $deps );

		return wp_register_script( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'] ?? $ver, $in_footer );
	}

	/**
	 * Enqueue a script.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string|bool      $file      Script file, path of the script relative to the base directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'true'.
	 *
	 * @return bool|null Whether the script has been enqueued.False on failure.
	 */
	protected function enqueue_script( string $handle, string|bool $file = false, array $deps = [], string|bool|null $ver = false, bool $in_footer = true ): bool|null {

		// If the style is not registered, we attempt to register it before enqueuing it.
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			if ( ! $file || ! $this->register_script( $handle, $file, $deps, $ver, $in_footer ) ) {
				return false;
			}
		}

		return wp_enqueue_script( $handle );
	}

	/**
	 * Enqueue a stylesheet.
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string|bool      $file   Stylesheet file, path of the stylesheet relative to the base directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if not set, filetime will be used as version number.
	 * @param string|bool      $media  Optional. The media type for which this stylesheet is defined. Default 'all'.
	 *
	 * @return bool|null Whether the style has been enqueued. False on failure.
	 */
	protected function enqueue_style( string $handle, string|bool $file = false, array $deps = [], string|bool|null $ver = false, string|bool $media = 'all' ): bool|null {

		// If the style is not registered, we attempt to register it before enqueuing it.
		if ( ! wp_style_is( $handle, 'registered' ) ) {
			if ( ! $this->register_style( $handle, $file, $deps, $ver, $media ) ) {
				return false;
			}
		}

		return wp_enqueue_style( $handle );
	}

	/**
	 * Enqueue block specific assets.
	 *
	 * @param string $markup Markup of the block.
	 * @param array  $block Array with block information.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_specific_assets( $markup, $block ) {
		if ( is_array( $block ) && ! empty( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
			wp_enqueue_script( 'core-navigation' );
			wp_enqueue_style( 'core-navigation' );
		}

		return $markup;
	}

	/**
	 * Get asset dependencies and version info from {handle}.asset.php if exists.
	 *
	 * @param string $file File name.
	 * @param array  $deps Script dependencies to merge with.
	 * @param string $ver  Asset version string.
	 *
	 * @return array
	 */
	protected function get_asset_meta( $file, $deps = [], $ver = false ) {
		$asset_meta_file = sprintf( '%s/js/%s.asset.php', untrailingslashit( $this->base_path ), basename( $file, '.' . pathinfo( $file, PATHINFO_EXTENSION ) ) );
		$asset_meta      = is_readable( $asset_meta_file )
			? require $asset_meta_file
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $file, $ver ),
			];

		$asset_meta['dependencies'] = array_merge( $deps, $asset_meta['dependencies'] );

		return $asset_meta;
	}

	/**
	 * Get file version.
	 *
	 * @param string             $file File path.
	 * @param int|string|boolean $ver  File version.
	 *
	 * @return bool|false|int
	 */
	protected function get_file_version( $file, $ver = false ) {
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		$file_path = sprintf( '%s/%s', $this->base_path, $file );

		return file_exists( $file_path ) ? filemtime( $file_path ) : false;
	}
}
