<?php
/**
 * Render callback for the rtcamp/card block.
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
	'title'        => isset( $attributes['title'] ) ? sanitize_text_field( (string) $attributes['title'] ) : '',
	'description'  => isset( $attributes['description'] ) ? sanitize_textarea_field( (string) $attributes['description'] ) : '',
	'image_url'    => isset( $attributes['imageUrl'] ) ? esc_url_raw( (string) $attributes['imageUrl'] ) : '',
	'image_alt'    => isset( $attributes['imageAlt'] ) ? sanitize_text_field( (string) $attributes['imageAlt'] ) : '',
	'url'          => isset( $attributes['url'] ) ? esc_url_raw( (string) $attributes['url'] ) : '',
	'button_label' => isset( $attributes['buttonLabel'] ) ? sanitize_text_field( (string) $attributes['buttonLabel'] ) : '',
];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo elementary_theme_get_component( 'Card', $props );
