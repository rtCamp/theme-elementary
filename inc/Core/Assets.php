<?php
/**
 * Theme assets registration.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Traits\AssetLoaderTrait;
use rtCamp\WPFramework\Contracts\Interfaces\Registrable;

/**
 * Class Assets
 *
 * @since 1.0.0
 */
class Assets implements Registrable {

	use AssetLoaderTrait;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->base_dir   = trailingslashit( ELEMENTARY_THEME_PATH );
		$this->base_url   = trailingslashit( get_template_directory_uri() );
		$this->assets_dir = 'assets/build';
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'render_block', [ $this, 'enqueue_block_specific_assets' ], 10, 2 );
	}

	/**
	 * Register assets.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function register_assets(): void {
		$this->register_script( 'core-navigation', 'js/frontend/core-navigation' );
		$this->register_style( 'core-navigation', 'css/frontend/core-navigation' );
		$this->register_style( 'elementary-theme-styles', 'css/frontend/styles' );
	}

	/**
	 * Enqueue block specific assets.
	 *
	 * @param string               $markup Markup of the block.
	 * @param array<string, mixed> $block  Array with block information.
	 *
	 * @return string Updated markup.
	 *
	 * @since 1.0.0
	 *
	 * @action render_block
	 */
	public function enqueue_block_specific_assets( string $markup, array $block ): string {
		if ( ! empty( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
			wp_enqueue_script( 'core-navigation' );
			wp_enqueue_style( 'core-navigation' );
		}

		return $markup;
	}

	/**
	 * Enqueue JS and CSS in frontend.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_assets(): void {
		wp_enqueue_style( 'elementary-theme-styles' );

		if ( 'local' === wp_get_environment_type() && ! ELEMENTARY_THEME_DISABLE_BROWSER_SYNC ) {
			if ( defined( 'ELEMENTARY_THEME_BROWSER_SYNC_URL' ) ) {
				$bs_url = ELEMENTARY_THEME_BROWSER_SYNC_URL;
			} else {
				$scheme = is_ssl() ? 'https' : 'http';
				$host   = wp_parse_url( home_url(), PHP_URL_HOST );
				$host   = $host ? $host : 'localhost';
				$bs_url = "{$scheme}://{$host}:3000/browser-sync/browser-sync-client.js";
			}
			wp_enqueue_script( 'elementary-browser-sync', $bs_url, [], ELEMENTARY_THEME_VERSION, true );
		}
	}
}
