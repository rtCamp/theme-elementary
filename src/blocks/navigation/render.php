<?php
/**
 * Render callback for the rtcamp/navigation block.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

if ( ! function_exists( 'elementary_theme_get_component' ) ) {
	return;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$attributes = $attributes ?? [];
$raw_items  = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : [];
$items      = [];

foreach ( $raw_items as $item ) {
	if ( ! is_array( $item ) ) {
		continue;
	}

	$items[] = [
		'label'   => isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '',
		'url'     => isset( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '',
		'current' => ! empty( $item['current'] ),
	];
}

$props = [
	'label' => isset( $attributes['label'] ) ? sanitize_text_field( (string) $attributes['label'] ) : '',
	'items' => $items,
];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo elementary_theme_get_component( 'Navigation', $props );
