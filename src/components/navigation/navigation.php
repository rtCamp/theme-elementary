<?php
/**
 * Navigation component.
 *
 * @package rtCamp\Theme\Elementary
 *
 * @param array $args {
 *     Component arguments.
 *
 *     @type string               $label Navigation landmark label. Optional.
 *     @type array<int, array>    $items Navigation items. Each item accepts label, url, current.
 *     @type string               $class Additional CSS classes. Optional.
 * }
 */

$label = isset( $args['label'] ) ? (string) $args['label'] : __( 'Primary navigation', 'elementary-theme' );
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : [];
$class = isset( $args['class'] ) ? (string) $args['class'] : '';

if ( empty( $items ) ) {
	return;
}

$css_class = trim( 'elementary-navigation ' . $class );
?>
<nav class="<?php echo esc_attr( $css_class ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
	<ul class="elementary-navigation__list">
		<?php foreach ( $items as $item ) : ?>
			<?php
			if ( ! is_array( $item ) || empty( $item['label'] ) || empty( $item['url'] ) ) {
				continue;
			}

			$item_label = (string) $item['label'];
			$item_url   = (string) $item['url'];
			$is_current = ! empty( $item['current'] );
			?>
			<li class="elementary-navigation__item">
				<a
					class="elementary-navigation__link"
					href="<?php echo esc_url( $item_url ); ?>"
					<?php echo $is_current ? 'aria-current="page"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
					<?php echo esc_html( $item_label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
