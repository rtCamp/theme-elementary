<?php
/**
 * Title: 404 content
 * Slug: elementary-theme/hidden-404
 * Description: 404 pattern content.
 * Categories:
 * Keywords: 404, 404 error
 * Viewport Width: 1280
 * Block Types:
 * Post Types:
 * Inserter: false
 */
?>

<!-- wp:heading {"style":{"typography":{"fontSize":"clamp(4rem, 40vw, 20rem)","fontWeight":"100","lineHeight":"1"}},"className":"has-text-align-center"} -->
<h2 class="has-text-align-center" style="font-size:clamp(4rem, 40vw, 20rem);font-weight:100;line-height:1">
	<?php echo esc_html( _x( '404', 'Error code for a webpage that is not found.', 'elementary-theme' ) ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">
	<?php esc_html_e( 'This page could not be found. Maybe try a search?', 'elementary-theme' ); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"1em"} -->
<div style="height:1em" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:search {"label":"<?php esc_html_e( 'Search', 'elementary-theme' ); ?>","showLabel":false,"width":100,"widthUnit":"%","buttonText":"<?php esc_html_e( 'Search', 'elementary-theme' ); ?>","buttonUseIcon":true,"align":"center"} /-->

<!-- wp:spacer {"height":"2em"} -->
<div style="height:2em" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->
