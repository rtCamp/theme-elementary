<?php
/**
 * A page creation pattern content.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Patterns\Content;

use Elementary_Theme\Patterns\Block_Pattern_Base;

/**
 * Class Page_Creation_Pattern
 *
 * @since 1.0.0
 */
final class Page_Creation_Pattern extends Block_Pattern_Base {
	/**
	 * Page Creation Pattern.
	 *
	 * @return array Block pattern properties.
	 */
	public function block_pattern() {
		return [
			'title'      => __( 'A Page Creation Pattern', 'elementary-theme' ),
			'blockTypes' => [ 'core/post-content' ],
			'content'    => $this->block_pattern_content(),
		];
	}

	/**
	 * Page Creation Pattern content.
	 *
	 * @return string Block pattern content.
	 */
	public function block_pattern_content() {
		ob_start();
		?>
		<!-- wp:paragraph -->
		<p>Page creation patterns are some starter patterns when creating a page. Learn more about <a href="https://make.wordpress.org/core/2022/05/03/page-creation-patterns-in-wordpress-6-0/">Page creation patterns</a>.</p>
		<!-- /wp:paragraph -->
		<?php
		return ob_get_clean();
	}
}
