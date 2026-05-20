<?php
/**
 * Card component.
 *
 * A render-only component that outputs a card with optional image, title,
 * description, and action button. Demonstrates component composability
 * by rendering the Button component internally.
 *
 * @package rtCamp\Theme\Elementary
 *
 * @param array $args {
 *     Component arguments.
 *
 *     @type string $title        Card title. Required.
 *     @type string $description  Card description text. Optional.
 *     @type string $image_url    Card image URL. Optional.
 *     @type string $image_alt    Card image alt text. Optional.
 *     @type string $url          Card link URL. Optional.
 *     @type string $button_label Card action label. Optional.
 *     @type string $class        Additional CSS classes. Optional.
 * }
 */

$title        = isset( $args['title'] ) ? (string) $args['title'] : '';
$description  = isset( $args['description'] ) ? (string) $args['description'] : '';
$image_url    = isset( $args['image_url'] ) ? (string) $args['image_url'] : '';
$image_alt    = isset( $args['image_alt'] ) ? (string) $args['image_alt'] : '';
$url          = isset( $args['url'] ) ? (string) $args['url'] : '';
$button_label = isset( $args['button_label'] ) ? (string) $args['button_label'] : __( 'Read more', 'elementary-theme' );
$class        = isset( $args['class'] ) ? (string) $args['class'] : '';

if ( empty( $title ) ) {
	return;
}

$css_class = trim( 'elementary-card ' . $class );

?>
<article class="<?php echo esc_attr( $css_class ); ?>">
	<?php if ( ! empty( $image_url ) ) : ?>
		<div class="elementary-card__image">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
		</div>
	<?php endif; ?>

	<div class="elementary-card__content">
		<h3 class="elementary-card__title"><?php echo esc_html( $title ); ?></h3>

		<?php if ( ! empty( $description ) ) : ?>
			<p class="elementary-card__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

			<?php if ( ! empty( $url ) ) : ?>
				<div class="elementary-card__action">
					<?php
					elementary_theme_component(
						'Button',
						[
							'label'      => $button_label,
							'url'        => $url,
							'class'      => 'elementary-card__button',
							'variant'    => 'secondary',
							'aria_label' => sprintf(
								/* translators: %s: Card title. */
								__( 'Read more about %s', 'elementary-theme' ),
								$title
							),
						]
					);
					?>
				</div>
			<?php endif; ?>
	</div>
</article>
<?php
