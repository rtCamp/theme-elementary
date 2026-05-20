<?php
/**
 * Block Registrar Module.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Blocks;

use rtCamp\Theme\Elementary\Framework\Traits\Singleton;

/**
 * Class Registrar
 *
 * Discovers and registers blocks from the src/blocks directory.
 *
 * @since 1.0.0
 */
class Registrar {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	protected function setup_hooks(): void {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		/**
		 * Filters the directories where the theme looks for blocks to register.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string> $paths Array of absolute paths to block directories.
		 */
		$block_paths = apply_filters(
			'elementary_theme_block_paths',
			[
				get_stylesheet_directory() . '/src/blocks',
				get_template_directory() . '/src/blocks',
			]
		);

		if ( ! is_array( $block_paths ) || empty( $block_paths ) ) {
			return;
		}

		$registered_blocks = [];

		foreach ( $block_paths as $blocks_dir ) {
			if ( ! is_dir( $blocks_dir ) ) {
				continue;
			}

			$directories = glob( $blocks_dir . '/*', GLOB_ONLYDIR );

			if ( empty( $directories ) ) {
				continue;
			}

			foreach ( $directories as $block_dir ) {
				$metadata_file = $block_dir . '/block.json';

				if ( ! file_exists( $metadata_file ) ) {
					continue;
				}

				$metadata   = wp_json_file_decode( $metadata_file, [ 'associative' => true ] );
				$block_name = is_array( $metadata ) && ! empty( $metadata['name'] ) ? (string) $metadata['name'] : basename( $block_dir );

				if ( isset( $registered_blocks[ $block_name ] ) || \WP_Block_Type_Registry::get_instance()->is_registered( $block_name ) ) {
					continue;
				}

				register_block_type( $block_dir );
				$registered_blocks[ $block_name ] = true;
			}
		}
	}
}
