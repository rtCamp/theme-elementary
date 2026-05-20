<?php
/**
 * Render callback for the rtcamp/post-loop block.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

if ( ! function_exists( 'elementary_theme_get_component' ) ) {
	return;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$attributes         = $attributes ?? [];
$selected_post_type = isset( $attributes['postType'] ) ? sanitize_key( (string) $attributes['postType'] ) : 'post';
$posts_per_page     = isset( $attributes['postsPerPage'] ) ? absint( $attributes['postsPerPage'] ) : 3;
$selected_order_by  = isset( $attributes['orderBy'] ) ? sanitize_key( (string) $attributes['orderBy'] ) : 'date';
$selected_order     = isset( $attributes['order'] ) ? strtoupper( sanitize_key( (string) $attributes['order'] ) ) : 'DESC';
$display_excerpt    = isset( $attributes['displayExcerpt'] ) ? (bool) $attributes['displayExcerpt'] : true;
$empty_message      = isset( $attributes['emptyMessage'] ) ? sanitize_text_field( (string) $attributes['emptyMessage'] ) : __( 'No posts found.', 'elementary-theme' );

$allowed_order_by  = [ 'date', 'title', 'menu_order' ];
$allowed_order     = [ 'ASC', 'DESC' ];
$posts_per_page    = min( max( $posts_per_page, 1 ), 12 );
$selected_order_by = in_array( $selected_order_by, $allowed_order_by, true ) ? $selected_order_by : 'date';
$selected_order    = in_array( $selected_order, $allowed_order, true ) ? $selected_order : 'DESC';

$query_args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $posts_per_page,
	'orderby'        => $selected_order_by,
	'order'          => $selected_order,
	'no_found_rows'  => true,
];

$query_args['post_type'] = post_type_exists( $selected_post_type ) ? $selected_post_type : 'post';
$query                   = new WP_Query( $query_args );
$items                   = [];

foreach ( $query->posts as $queried_post ) {
	$items[] = [
		'title'   => get_the_title( $queried_post ),
		'url'     => get_permalink( $queried_post ),
		'excerpt' => $display_excerpt ? wp_trim_words( get_the_excerpt( $queried_post ), 24 ) : '',
	];
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo elementary_theme_get_component(
	'PostLoop',
	[
		'items'        => $items,
		'emptyMessage' => $empty_message,
	]
);
