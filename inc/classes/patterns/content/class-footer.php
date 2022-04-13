<?php
/**
 * Footer pattern content.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Patterns\Content;

use Elementary_Theme\Patterns\Block_Pattern_Base;

/**
 * Class Footer
 *
 * @since 1.0.0
 */
final class Footer extends Block_Pattern_Base {

	/**
	 * Footer Block Pattern.
	 *
	 * @return array Block pattern properties.
	 */
	public function block_pattern() {
		return array(
			'title'      => __( 'Footer', 'elementary-theme' ),
			'categories' => array( 'footer' ),
			'blockTypes' => array( 'core/template-part/footer' ),
			'content'    => $this->block_pattern_content(),
		);
	}

	/**
	 * Footer Block Pattern content.
	 *
	 * @return string Block pattern content.
	 */
	public function block_pattern_content() {
		ob_start();
		?>
		<!-- wp:group {"layout":{"inherit":"true"}} -->
		<div class="wp-block-group">
			<!-- wp:group {"align":"wide","layout":{"type":"flex","justifyContent":"space-between"},"style":{"spacing":{"padding":{"bottom":"var(--wp--custom--spacing--medium)","top":"var(--wp--custom--spacing--medium)"}}}} -->
			<div class="wp-block-group alignwide" style="padding-top: var(--wp--custom--spacing--medium); padding-bottom: var(--wp--custom--spacing--medium);">
				<!-- wp:navigation {"layout":{"type":"flex","setCascadingProperties":true,"justifyContent":"left"},"overlayMenu":"never","className":"site-footer","style":{"typography":{"fontStyle":"normal"},"spacing":{"blockGap":"2.5rem"}},"fontSize":"small"} /-->
				<!-- wp:paragraph {"align":"left","fontSize":"small","style":{"spacing":{"margin":{"top":0}}}} -->
				<p class="has-small-font-size" style="margin-top: 0;">
					<?php
					printf(
						/* Translators: WordPress link. */
						esc_html__( 'Proudly powered by %s', 'elementary-theme' ),
						'<a href="' . esc_url( __( 'https://wordpress.org/', 'elementary-theme' ) ) . '">WordPress</a>'
					);
					?>
				</p><!-- /wp:paragraph -->
			</div><!-- /wp:group -->
		</div><!-- /wp:group -->
		<?php
		return ob_get_clean();
	}
}
