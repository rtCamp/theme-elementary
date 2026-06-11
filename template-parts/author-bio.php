<?php
/**
 * Author bio template part.
 *
 * Rendered through the theme's TemplateLoader (see Helpers\Util::get_template).
 * A child theme can override this by placing its own copy at:
 *   child-theme/template-parts/author-bio.php
 *
 * @package rtCamp\Theme\Elementary
 *
 * @var array<string, mixed> $args {
 *     @type string $name   Author display name.
 *     @type string $bio    Author description / biographical info.
 *     @type string $avatar Avatar <img> markup (from get_avatar()).
 * }
 */

declare( strict_types = 1 );

// Variables here are function-scoped (the part is included via load_template()),
// not globals — but WPCS can't tell, so they carry the theme prefix anyway.
$elementary_name   = (string) ( $args['name'] ?? '' );
$elementary_bio    = (string) ( $args['bio'] ?? '' );
$elementary_avatar = (string) ( $args['avatar'] ?? '' );

// Nothing to show — don't render an empty shell.
if ( '' === $elementary_name && '' === $elementary_bio ) {
	return;
}
?>
<div class="elementary-author-bio">
	<?php if ( '' !== $elementary_avatar ) : ?>
		<div class="elementary-author-bio__avatar">
			<?php echo $elementary_avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() returns escaped markup. ?>
		</div>
	<?php endif; ?>
	<div class="elementary-author-bio__body">
		<p class="elementary-author-bio__name"><?php echo esc_html( $elementary_name ); ?></p>
		<?php if ( '' !== $elementary_bio ) : ?>
			<p class="elementary-author-bio__desc"><?php echo esc_html( $elementary_bio ); ?></p>
		<?php endif; ?>
	</div>
</div>
