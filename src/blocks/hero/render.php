<?php
/**
 * Render callback for the rtcamp/hero block.
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
	'title'       => isset( $attributes['title'] ) ? sanitize_text_field( (string) $attributes['title'] ) : '',
	'subtitle'    => isset( $attributes['subtitle'] ) ? sanitize_text_field( (string) $attributes['subtitle'] ) : '',
	'buttonLabel' => isset( $attributes['buttonLabel'] ) ? sanitize_text_field( (string) $attributes['buttonLabel'] ) : '',
	'buttonUrl'   => isset( $attributes['buttonUrl'] ) ? esc_url_raw( (string) $attributes['buttonUrl'] ) : '',
];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo elementary_theme_get_component( 'Hero', $props );
