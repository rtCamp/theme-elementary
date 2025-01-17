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
		// List all subdirectories in 'inc/blocks' directory.
		$blocks = array_filter( glob( ELEMENTARY_THEME_TEMP_DIR . '/assets/build/blocks/*' ), 'is_dir' );

		// Register each block.
		foreach ( $blocks as $block ) {

			// Register the block.
			register_block_type( $block );
		}
	}
}
