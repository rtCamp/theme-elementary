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

		// Get blocks manifest file path.
		$manifest = ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks/blocks-manifest.php';
	
		// Check if manifest file exists.
		if ( file_exists( ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks/blocks-manifest.php' ) ) {
			
			// Register the blocks metadata collection. This will allow WordPress to know about the blocks and improve the performance.
			wp_register_block_metadata_collection(
				ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks',
				$manifest
			);
		}

		// List all subdirectories in 'inc/blocks' directory.
		$blocks = array_filter( glob( ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks/*' ), 'is_dir' );

		// Register each block.
		foreach ( $blocks as $block ) {
			// Get the block name and skip the ones starting with '_' (underscore) prefix.
			$block_name = str_replace( ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks/', '', $block );
			if ( 0 === strpos( $block_name, '_' ) ) {
				continue;
			}
			// Register the block.
			register_block_type( $block );
		}
	}
}
