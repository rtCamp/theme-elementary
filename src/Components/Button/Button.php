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
 *     @type string $label Button label text. Required.
 *     @type string $url   URL for link buttons. Optional.
 *     @type string $class Additional CSS classes. Optional.
 *     @type string $tag   HTML tag: 'a' or 'button'. Optional. Defaults to 'a' when $url is set, 'button' otherwise.
 * }
 */

$label = $args['label'] ?? '';
$url   = $args['url'] ?? '';
$class = $args['class'] ?? '';
$tag   = $args['tag'] ?? ( ! empty( $url ) ? 'a' : 'button' );

if ( empty( $label ) ) {
	return;
}

$css_class = trim( 'elementary-button ' . $class );

if ( 'a' === $tag && ! empty( $url ) ) {
	printf(
		'<a href="%s" class="%s">%s</a>',
		esc_url( $url ),
		esc_attr( $css_class ),
		esc_html( $label )
	);
} else {
	printf(
		'<button type="button" class="%s">%s</button>',
		esc_attr( $css_class ),
		esc_html( $label )
	);
}
