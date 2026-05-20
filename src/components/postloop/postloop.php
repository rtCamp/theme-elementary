<?php
/**
 * Post Loop component.
 *
 * @package rtCamp\Theme\Elementary
 *
 * @param array $args {
 *     Component arguments.
 *
 *     @type array<int, array> $items        Prepared post items. Each item accepts title, url, excerpt.
 *     @type string            $emptyMessage Empty state message. Optional.
 *     @type string            $class        Additional CSS classes. Optional.
 * }
 */

$items         = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : [];
$empty_message = isset( $args['emptyMessage'] ) ? (string) $args['emptyMessage'] : __( 'No posts found.', 'elementary-theme' );
$class         = isset( $args['class'] ) ? (string) $args['class'] : '';
$css_class     = trim( 'elementary-post-loop ' . $class );
?>
<div class="<?php echo esc_attr( $css_class ); ?>">
	<?php if ( empty( $items ) ) : ?>
		<p class="elementary-post-loop__empty-message"><?php echo esc_html( $empty_message ); ?></p>
	<?php else : ?>
		<ul class="elementary-post-loop__list">
			<?php foreach ( $items as $item ) : ?>
				<?php
				if ( ! is_array( $item ) || empty( $item['title'] ) || empty( $item['url'] ) ) {
					continue;
				}

				$title   = (string) $item['title'];
				$url     = (string) $item['url'];
				$excerpt = isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '';
				?>
				<li class="elementary-post-loop__item">
					<a class="elementary-post-loop__link" href="<?php echo esc_url( $url ); ?>">
						<?php echo esc_html( $title ); ?>
					</a>

					<?php if ( ! empty( $excerpt ) ) : ?>
						<p class="elementary-post-loop__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
