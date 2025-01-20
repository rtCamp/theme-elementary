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
	public function setup_hooks() {
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

		$blocks_dir = untrailingslashit( get_template_directory() ) . '/assets/build/blocks';

		// Get blocks manifest file path.
		$manifest = $blocks_dir . '/blocks-manifest.php';
	
		// Check if manifest file exists.
		if ( file_exists( $manifest ) ) {
			
			// Register the blocks metadata collection. This will allow WordPress to know about the blocks and improve the performance.
			wp_register_block_metadata_collection(
				$blocks_dir,
				$manifest
			);
		}

		// List all subdirectories in 'inc/blocks' directory.
		$blocks = array_filter( glob( $blocks_dir . '/*' ), 'is_dir' );

		// Register each block.
		foreach ( $blocks as $block ) {
			// Get the block name and skip the ones starting with '_' (underscore) prefix.
			$block_name = str_replace( $blocks_dir, '', $block );
			if ( 0 === strpos( $block_name, '_' ) ) {
				continue;
			}
			// Register the block.
			register_block_type( $block );
		}
	}
}
