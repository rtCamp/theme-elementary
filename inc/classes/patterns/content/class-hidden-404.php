<?php
/**
 * 404 Pattern content.
 *
 * @package Elementary
 */

namespace Elementary\Patterns\Content;

use Elementary\Patterns\Block_Pattern_Base;

/**
 * Class Hidden_404
 *
 * @since 1.0.0
 */
final class Hidden_404 extends Block_Pattern_Base {
	/**
	 * 404 Block Pattern.
	 *
	 * @return array Block pattern properties.
	 */
	public function block_pattern() {
		return array(
			'title'    => __( '404 content', 'elementary' ),
			'inserter' => false,
			'content'  => $this->block_pattern_content(),
		);
	}

	/**
	 * 404 Block Pattern content.
	 *
	 * @return string Block pattern content.
	 */
	public function block_pattern_content() {
		ob_start();
		?>
		<!-- wp:heading {"style":{"typography":{"fontSize":"clamp(4rem, 40vw, 20rem)","fontWeight":"100","lineHeight":"1"}},"className":"has-text-align-center"} -->
		<h2 class="has-text-align-center" style="font-size:clamp(4rem, 40vw, 20rem);font-weight:100;line-height:1"><?php echo esc_html( _x( '404', 'Error code for a webpage that is not found.', 'elementary' ) ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center"><?php esc_html_e( 'This page could not be found. Maybe try a search?', 'elementary' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:spacer {"height":"1em"} -->
		<div style="height:1em" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:search {"label":"<?php esc_html_e( 'Search', 'elementary' ); ?>","showLabel":false,"width":100,"widthUnit":"%","buttonText":"<?php esc_html_e( 'Search', 'elementary' ); ?>","buttonUseIcon":true,"align":"center"} /-->

		<!-- wp:spacer {"height":"2em"} -->
		<div style="height:2em" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->
		<?php
		return ob_get_clean();
	}
}
