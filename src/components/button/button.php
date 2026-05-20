<?php
/**
 * Button component.
 *
 * A render-only component that outputs a button or link element.
 *
 * @package rtCamp\Theme\Elementary
 *
 * @param array $args {
 *     Component arguments.
 *
 *     @type string $label      Button label text. Required.
 *     @type string $url        URL for link buttons. Optional.
 *     @type string $class      Additional CSS classes. Optional.
 *     @type string $variant    Visual variant. Optional. Defaults to 'primary'.
 *     @type string $size       Visual size. Optional. Defaults to 'medium'.
 *     @type string $tag        HTML tag: 'a' or 'button'. Optional. Defaults to 'a' when $url is set, 'button' otherwise.
 *     @type string $aria_label Accessible label when visible label needs more context. Optional.
 *     @type string $target     Link target. Optional.
 * }
 */

$label      = isset( $args['label'] ) ? (string) $args['label'] : '';
$url        = isset( $args['url'] ) ? (string) $args['url'] : '';
$class      = isset( $args['class'] ) ? (string) $args['class'] : '';
$variant    = isset( $args['variant'] ) ? sanitize_key( (string) $args['variant'] ) : 'primary';
$size       = isset( $args['size'] ) ? sanitize_key( (string) $args['size'] ) : 'medium';
$tag        = isset( $args['tag'] ) ? (string) $args['tag'] : ( ! empty( $url ) ? 'a' : 'button' );
$aria_label = isset( $args['aria_label'] ) ? (string) $args['aria_label'] : '';
$target     = isset( $args['target'] ) ? (string) $args['target'] : '';

if ( empty( $label ) ) {
	return;
}

$allowed_variants = [ 'primary', 'secondary', 'text' ];
$allowed_sizes    = [ 'small', 'medium', 'large' ];
$variant          = in_array( $variant, $allowed_variants, true ) ? $variant : 'primary';
$size             = in_array( $size, $allowed_sizes, true ) ? $size : 'medium';
$css_class        = trim( sprintf( 'elementary-button elementary-button--%1$s elementary-button--%2$s %3$s', $variant, $size, $class ) );
$aria_attribute   = ! empty( $aria_label ) ? sprintf( ' aria-label="%s"', esc_attr( $aria_label ) ) : '';

if ( 'a' === $tag && ! empty( $url ) ) {
	$target_attribute = in_array( $target, [ '_blank', '_self', '_parent', '_top' ], true ) ? sprintf( ' target="%s"', esc_attr( $target ) ) : '';
	$rel_attribute    = '_blank' === $target ? ' rel="noopener noreferrer"' : '';

	printf(
		'<a href="%1$s" class="%2$s"%3$s%4$s%5$s>%6$s</a>',
		esc_url( $url ),
		esc_attr( $css_class ),
		$aria_attribute, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$target_attribute, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$rel_attribute, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $label )
	);
} else {
	printf(
		'<button type="button" class="%1$s"%2$s>%3$s</button>',
		esc_attr( $css_class ),
		$aria_attribute, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $label )
	);
}
