<?php
/**
 * Theme bootstrap file.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\Theme\Elementary\Framework\Traits\AssetLoaderTrait;
use rtCamp\Theme\Elementary\Framework\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 1.0.0
 */
class Assets {

	use AssetLoaderTrait;
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
	public function setup_hooks(): void {
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
		$this->register_script( 'core-navigation', 'js/core-navigation.js' );
		$this->register_style( 'core-navigation', 'css/core-navigation.css' );
		$this->register_style( 'elementary-theme-styles', 'css/styles.css' );
	}

	/**
	 * Enqueue block specific assets.
	 *
	 * @param string $markup Markup of the block.
	 * @param array  $block  Array with block information.
	 *
	 * @return string Updated markup.
	 *
	 * @since 1.0.0
	 *
	 * @action render_block
	 */
	public function enqueue_block_specific_assets( string $markup, array $block ): string {
		if ( is_array( $block ) && ! empty( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
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
	}
}
