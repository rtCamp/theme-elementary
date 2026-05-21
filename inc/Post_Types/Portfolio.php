<?php
/**
 * Example Post Type: Portfolio.
 *
 * Demonstrates how to use Abstract_Post_Type from wp-framework.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Post_Types;

use rtCamp\WPFramework\Contracts\Abstracts\Abstract_Post_Type;

/**
 * Class Portfolio
 */
class Portfolio extends Abstract_Post_Type {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'portfolio';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_post_type(): void {
		$args = array_merge(
			$this->default_args(),
			[
				'label'       => __( 'Portfolio', 'elementary-theme' ),
				'labels'      => [
					'name'               => __( 'Portfolio', 'elementary-theme' ),
					'singular_name'      => __( 'Portfolio Item', 'elementary-theme' ),
					'add_new'            => __( 'Add New', 'elementary-theme' ),
					'add_new_item'       => __( 'Add New Portfolio Item', 'elementary-theme' ),
					'edit_item'          => __( 'Edit Portfolio Item', 'elementary-theme' ),
					'new_item'           => __( 'New Portfolio Item', 'elementary-theme' ),
					'view_item'          => __( 'View Portfolio Item', 'elementary-theme' ),
					'search_items'       => __( 'Search Portfolio', 'elementary-theme' ),
					'not_found'          => __( 'No portfolio items found', 'elementary-theme' ),
					'not_found_in_trash' => __( 'No portfolio items found in Trash', 'elementary-theme' ),
				],
				'menu_icon'   => 'dashicons-portfolio',
				'rewrite'     => [ 'slug' => 'portfolio' ],
				'supports'    => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
			]
		);

		register_post_type( static::get_slug(), $args );
	}
}
