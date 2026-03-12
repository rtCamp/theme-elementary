<?php
/**
 * Blocks registration file.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme;

use Elementary_Theme\Traits\Singleton;

/**
 * Class Blocks
 *
 * @since 1.0.0
 */
class Blocks {

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
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register blocks.
	 *
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function register_blocks() {

		$blocks_dir = get_template_directory() . '/assets/build/blocks';

		// Bail early if the blocks directory doesn't exist (e.g. before running a build).
		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}

		// Get blocks manifest file path.
		$manifest = $blocks_dir . '/blocks-manifest.php';

		// Register the blocks metadata collection if the manifest exists and the function
		// is available (introduced in WordPress 6.7). This improves block loading performance.
		if ( file_exists( $manifest ) && function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( $blocks_dir, $manifest );
		}

		// List all subdirectories in 'assets/build/blocks' directory.
		$glob_result = glob( $blocks_dir . '/*' );
		$blocks = array_filter( $glob_result !== false ? $glob_result : array(), 'is_dir' );

		// Register each block.
		foreach ( $blocks as $block ) {
			// Use basename() to get just the folder name and skip blocks
			// prefixed with '_' (underscore), which are intentionally excluded.
			if ( 0 === strpos( basename( $block ), '_' ) ) {
				continue;
			}

			// Register the block.
			register_block_type( $block );
		}
	}
}
