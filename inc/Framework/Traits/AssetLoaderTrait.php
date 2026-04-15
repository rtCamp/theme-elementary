<?php
/**
 * Trait for WordPress asset loading.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Framework\Traits;

/**
 * Trait AssetLoaderTrait
 *
 * @since 1.0.0
 */
trait AssetLoaderTrait {

	/**
	 * Register a new script.
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $file      Script file, path of the script relative to the assets/build/ directory.
	 * @param array<string>    $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 *
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	private function register_script( string $handle, string $file, array $deps = [], string|bool|null $ver = false, bool $in_footer = true ): bool {
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
	 * @param string           $file   Style file, path of the script relative to the assets/build/ directory.
	 * @param array<string>    $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying script version number, if not set, filetime will be used as version number.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 *
	 * @return bool Whether the style has been registered. True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	private function register_style( string $handle, string $file, array $deps = [], string|bool|null $ver = false, string $media = 'all' ): bool {
		$file_path = sprintf( '%s/%s', ELEMENTARY_THEME_BUILD_DIR, $file );

		if ( ! \file_exists( $file_path ) ) {
			return false;
		}

		$src        = sprintf( ELEMENTARY_THEME_BUILD_URI . '/%s', $file );
		$asset_meta = $this->get_asset_meta( $file, $deps );

		return wp_register_style( $handle, $src, $asset_meta['dependencies'], $asset_meta['version'], $media );
	}

	/**
	 * Get asset dependencies and version info from {handle}.asset.php if exists.
	 *
	 * @param string           $file File name.
	 * @param array<string>    $deps Script dependencies to merge with.
	 * @param string|bool|null $ver  Asset version string.
	 *
	 * @return array<string, mixed> Asset meta information including dependencies and version.
	 *
	 * @since 1.0.0
	 */
	private function get_asset_meta( string $file, array $deps = [], string|bool|null $ver = false ): array {
		$asset_meta_file = sprintf( '%s/js/%s.asset.php', untrailingslashit( ELEMENTARY_THEME_BUILD_DIR ), basename( $file, '.' . pathinfo( $file, PATHINFO_EXTENSION ) ) );
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
	 * @param string           $file File path.
	 * @param string|bool|null $ver  File version.
	 *
	 * @return int|string|bool File version based on file modification time or provided version.
	 *
	 * @since 1.0.0
	 */
	private function get_file_version( string $file, string|bool|null $ver = false ): int|string|bool {
		if ( ! empty( $ver ) ) {
			return $ver;
		}

		$file_path = sprintf( '%s/%s', ELEMENTARY_THEME_BUILD_DIR, $file );

		return file_exists( $file_path ) ? filemtime( $file_path ) : false;
	}
}
