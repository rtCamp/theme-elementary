<?php
/**
 * Title: Media Text Interactive
 * Slug: elementary-theme/media-text-interactive
 * Description: Media Text Interactive pattern content.
 * Categories:
 * Keywords: Media, Text, Interactive
 * Viewport Width: 1280
 * Block Types:
 * Post Types:
 * Inserter: true
 */
?>

<!-- wp:columns {"align":"wide","className":"elementary-media-text-interactive"} -->
<div class="wp-block-columns alignwide elementary-media-text-interactive">
	<!-- wp:column {"verticalAlignment":"center"} -->
	<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading -->
		<h2 class="wp-block-heading"><?php esc_html_e( 'Title', 'elementary-theme' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Blanditiis obcaecati vel impedit sed commodi error eveniet veniam. Delectus illum quis porro expedita quibusdam officiis iste est dicta assumenda, esse natus.', 'elementary-theme' ); ?>
		</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons"><!-- wp:button {"className":"elementary-media-text-interactive"} -->
			<div class="wp-block-button elementary-media-text-interactive"><a
					class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Play', 'elementary-theme' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"center"} -->
	<div class="wp-block-column is-vertically-aligned-center">
		<!-- wp:video {"className":"elementary-media-text-interactive"} -->
		<figure class="wp-block-video elementary-media-text-interactive"></figure>
		<!-- /wp:video -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
