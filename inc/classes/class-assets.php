<?php
/**
 * Theme bootstrap file.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme;

use Elementary_Theme\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 1.0.0
 */
class Assets {

	use Singleton;

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
		add_filter( 'render_block', [ $this, 'enqueue_block_specific_assets' ], 10, 2 );
	}

	/**
	 * Register assets.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function register_assets() {

		$this->register_script( 'core-navigation', 'js/core-navigation.js' );
		$this->register_style( 'core-navigation', 'css/core-navigation.css' );
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
	public function get_asset_meta( $file, $deps = [], $ver = false ) {
		$asset_meta_file = sprintf( '%s/js/%s.asset.php', untrailingslashit( ELEMENTARY_THEME_BUILD_DIR ), basename( $file, '.' . pathinfo( $file )['extension'] ) );
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

		$file_path = sprintf( '%s/%s', ELEMENTARY_THEME_BUILD_DIR, $file );

		if ( ! \file_exists( $file_path ) ) {
			return false;
		}

		$src        = sprintf( ELEMENTARY_THEME_BUILD_URI . '/%s', $file );
		$asset_meta = $this->get_asset_meta( $file, $deps );

		return wp_register_script( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'], $in_footer );
	}

	/**
	 * Register a CSS stylesheet.
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string|bool      $file    style file, path of the script relative to the assets/build/ directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 *
	 * @return bool Whether the style has been registered. True on success, false on failure.
	 */
	public function register_style( $handle, $file, $deps = [], $ver = false, $media = 'all' ) {

		$file_path = sprintf( '%s/%s', ELEMENTARY_THEME_BUILD_DIR, $file );

		if ( ! \file_exists( $file_path ) ) {
			return false;
		}

		$src        = sprintf( ELEMENTARY_THEME_BUILD_URI . '/%s', $file );
		$asset_meta = $this->get_asset_meta( $file, $deps );

		return wp_register_style( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'], $media );
	}

	/**
	 * Get file version.
	 *
	 * @param string             $file File path.
	 * @param int|string|boolean $ver  File version.
	 *
	 * @return bool|false|int
	 */
	public function get_file_version( $file, $ver = false ) {
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		$file_path = sprintf( '%s/%s', ELEMENTARY_THEME_BUILD_DIR, $file );

		return file_exists( $file_path ) ? filemtime( $file_path ) : false;
	}

}
