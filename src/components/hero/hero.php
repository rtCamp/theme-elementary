<?php
/**
 * Hero component.
 *
 * @package rtCamp\Theme\Elementary
 *
 * @param array $args {
 *     Component arguments.
 *
 *     @type string $title       Hero title. Required.
 *     @type string $subtitle    Supporting text. Optional.
 *     @type string $buttonLabel CTA label. Optional.
 *     @type string $buttonUrl   CTA URL. Optional.
 *     @type string $class       Additional CSS classes. Optional.
 * }
 */

$title        = isset( $args['title'] ) ? (string) $args['title'] : '';
$subtitle     = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$button_label = isset( $args['buttonLabel'] ) ? (string) $args['buttonLabel'] : '';
$button_url   = isset( $args['buttonUrl'] ) ? (string) $args['buttonUrl'] : '';
$class        = isset( $args['class'] ) ? (string) $args['class'] : '';

if ( empty( $title ) ) {
	return;
}

$css_class = trim( 'elementary-hero ' . $class );
?>
<section class="<?php echo esc_attr( $css_class ); ?>">
	<div class="elementary-hero__content">
		<h1 class="elementary-hero__title"><?php echo esc_html( $title ); ?></h1>

		<?php if ( ! empty( $subtitle ) ) : ?>
			<p class="elementary-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $button_label ) && ! empty( $button_url ) ) : ?>
			<div class="elementary-hero__action">
				<?php
				elementary_theme_component(
					'Button',
					[
						'label' => $button_label,
						'url'   => $button_url,
						'class' => 'elementary-hero__button',
					]
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
