<?php
/**
 * Render callback for the rtcamp/button block.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

if ( ! function_exists( 'elementary_theme_get_component' ) ) {
	return;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$attributes = $attributes ?? [];
$props      = [
	'label'   => isset( $attributes['label'] ) ? sanitize_text_field( (string) $attributes['label'] ) : '',
	'url'     => isset( $attributes['url'] ) ? esc_url_raw( (string) $attributes['url'] ) : '',
	'variant' => isset( $attributes['variant'] ) ? sanitize_key( (string) $attributes['variant'] ) : 'primary',
	'size'    => isset( $attributes['size'] ) ? sanitize_key( (string) $attributes['size'] ) : 'medium',
	'class'   => isset( $attributes['class'] ) ? sanitize_text_field( (string) $attributes['class'] ) : '',
];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo elementary_theme_get_component( 'Button', $props );
