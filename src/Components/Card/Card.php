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
 *     @type string $title       Card title. Required.
 *     @type string $description Card description text. Optional.
 *     @type string $image_url   Card image URL. Optional.
 *     @type string $url         Card link URL. Optional.
 * }
 */

use rtCamp\Theme\Elementary\Framework\ComponentLoader;

$title       = $args['title'] ?? '';
$description = $args['description'] ?? '';
$image_url   = $args['image_url'] ?? '';
$url         = $args['url'] ?? '';

if ( empty( $title ) ) {
	return;
}

?>
<div class="elementary-card">
	<?php if ( ! empty( $image_url ) ) : ?>
		<div class="elementary-card__image">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
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
				ComponentLoader::render(
					'Button',
					[
						'label' => $title,
						'url'   => $url,
						'class' => 'elementary-card__button',
					]
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
