<?php
/**
 * Title: Footer
 * Slug: elementary-theme/footer
 * Description: Footer pattern.
 * Categories: footer
 * Keywords: footer
 * Viewport Width: 1280
 * Block Types: core/template-part/footer
 * Post Types: wp_template
 * Inserter: false
 */
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

