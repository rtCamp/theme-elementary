<?php
/**
 * Media Text Interactive.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Block_Extensions;

use WP_HTML_Tag_Processor;
use Elementary_Theme\Traits\Singleton;

/**
 * Class Media_Text_Interactive
 */
class Media_Text_Interactive {

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
	public function setup_hooks() {

		add_filter( 'render_block_core/button', array( $this, 'render_block_core_button' ), 10, 2 );
		add_filter( 'render_block_core/columns', array( $this, 'render_block_core_columns' ), 10, 2 );
		add_filter( 'render_block_core/video', array( $this, 'render_block_core_video' ), 10, 2 );

	}

	/**
	 * Render block core/button.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block.
	 * @return string
	 */
	public function render_block_core_button( $block_content, $block ) {
		if ( ! isset( $block['attrs']['className'] ) || ! str_contains( $block['attrs']['className'], 'elementary-media-text-interactive' ) ) {
			return $block_content;
		}

		$p = new WP_HTML_Tag_Processor( $block_content );

		$p->next_tag();
		$p->set_attribute( 'data-wp-on--click', 'actions.play' );

		return $p->get_updated_html();
	}

	/**
	 * Render block core/columns.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block.
	 * @return string
	 */
	public function render_block_core_columns( $block_content, $block ) {
		if ( ! isset( $block['attrs']['className'] ) || ! str_contains( $block['attrs']['className'], 'elementary-media-text-interactive' ) ) {
			return $block_content;
		}

		/**
		 * Enqueue the module script, The prefix `@` is used to indicate that the script is a module.
		 * This handle with the prefix `@` will be used in other scripts to import this module.
		 */
		wp_enqueue_script_module(
			'@elementary/media-text-interactive',
			sprintf( '%s/js/modules/media-text.js', ELEMENTARY_THEME_BUILD_URI ),
			[
				'@wordpress/interactivity',
			]
		);

		$p = new WP_HTML_Tag_Processor( $block_content );

		$p->next_tag();
		$p->set_attribute( 'data-wp-interactive', '{ "namespace": "elementary/media-text-interactive" }' );
		$p->set_attribute( 'data-wp-context', '{ "isPlaying": false }' );

		return $p->get_updated_html();
	}

	/**
	 * Render block core/video.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block.
	 * @return string
	 */
	public function render_block_core_video( $block_content, $block ) {
		if ( ! isset( $block['attrs']['className'] ) || ! str_contains( $block['attrs']['className'], 'elementary-media-text-interactive' ) ) {
			return $block_content;
		}
		$p = new WP_HTML_Tag_Processor( $block_content );

		$p->next_tag();
		$p->set_attribute( 'data-wp-watch', 'callbacks.playVideo' );

		return $p->get_updated_html();
	}
}
