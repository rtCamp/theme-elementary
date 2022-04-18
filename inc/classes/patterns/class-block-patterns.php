<?php
/**
 * Blocks Patterns handler.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Patterns;

use Elementary_Theme\Traits\Singleton;

/**
 * Class Block_Patterns
 *
 * @since 1.0.0
 */
class Block_Patterns {

	use Singleton;

	/**
	 * Blocks Patterns Namespace.
	 *
	 * @var string
	 */
	const PATTERN_NAMESPACE = 'elementary-theme';

	/**
	 * Block Content classes namespace.
	 *
	 * @var string
	 */
	const BLOCK_CONTENT_NAMESPACE = 'Elementary_Theme\\Patterns\\Content\\';

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks() {
		add_action( 'init', [ $this, 'elementary_theme_register_block_patterns_categories' ] );
		add_action( 'init', [ $this, 'elementary_theme_register_block_patterns' ] );
	}

	/**
	 * Register categories for blocks patterns.
	 *
	 * @since 1.0.0
	 */
	public function elementary_theme_register_block_patterns_categories() {
		$block_pattern_categories = array(
			'featured' => array(
				'label' => __( 'Featured', 'elementary-theme' ),
			),
			'footer'   => array(
				'label' => __( 'Footer', 'elementary-theme' ),
			),
			'query'    => array(
				'label' => __( 'Query', 'elementary-theme' ),
			),
		);

		/**
		 * Filters the block pattern categories.
		 *
		 * @since 1.0.0
		 *
		 * @param array $block_pattern_categories Array of block pattern categories.
		 */
		$block_pattern_categories = apply_filters( 'elementary_theme_block_patterns_categories', $block_pattern_categories );

		foreach ( $block_pattern_categories as $name => $properties ) {
			register_block_pattern_category( $name, $properties );
		}
	}

	/**
	 * Register block patterns.
	 *
	 * @since 1.0.0
	 */
	public function elementary_theme_register_block_patterns() {
		$block_patterns_classes = array(
			'footer'     => 'Footer',
			'hidden-404' => 'Hidden_404',
		);

		/**
		 * Filters the theme block patterns.
		 *
		 * @since 1.0.0
		 *
		 * @param array $block_patterns The theme block patterns.
		 */
		$block_patterns = apply_filters( 'elementary_theme_block_patterns', $block_patterns_classes );

		foreach ( $block_patterns as $name => $class ) {
			$class = self::BLOCK_CONTENT_NAMESPACE . $class;

			register_block_pattern(
				self::PATTERN_NAMESPACE . '/' . $name,
				( new $class() )->block_pattern()
			);
		}
	}
}
