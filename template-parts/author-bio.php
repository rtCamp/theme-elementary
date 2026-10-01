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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local to the load_template() include scope.

$name   = (string) ( $args['name'] ?? '' );
$bio    = (string) ( $args['bio'] ?? '' );
$avatar = (string) ( $args['avatar'] ?? '' );

// Nothing to show — don't render an empty shell.
if ( '' === $name && '' === $bio ) {
	return;
}
?>
<div class="elementary-author-bio">
	<?php if ( '' !== $avatar ) : ?>
		<div class="elementary-author-bio__avatar">
			<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() returns escaped markup. ?>
		</div>
	<?php endif; ?>
	<div class="elementary-author-bio__body">
		<p class="elementary-author-bio__name"><?php echo esc_html( $name ); ?></p>
		<?php if ( '' !== $bio ) : ?>
			<p class="elementary-author-bio__desc"><?php echo esc_html( $bio ); ?></p>
		<?php endif; ?>
	</div>
</div>
