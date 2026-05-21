<?php
/**
 * Example Taxonomy: Project Type.
 *
 * Demonstrates how to use Abstract_Taxonomy from wp-framework.
 * Attached to the Portfolio post type.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Taxonomies;

use rtCamp\WPFramework\Contracts\Abstracts\Abstract_Taxonomy;
use rtCamp\Theme\Elementary\Post_Types\Portfolio;

/**
 * Class Project_Type
 */
class Project_Type extends Abstract_Taxonomy {

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'project-type';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_object_types(): array {
		return [ Portfolio::get_slug() ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_taxonomy(): void {
		$args = array_merge(
			$this->default_args(),
			[
				'hierarchical' => true,
				'labels'       => [
					'name'          => __( 'Project Types', 'elementary-theme' ),
					'singular_name' => __( 'Project Type', 'elementary-theme' ),
					'search_items'  => __( 'Search Project Types', 'elementary-theme' ),
					'all_items'     => __( 'All Project Types', 'elementary-theme' ),
					'edit_item'     => __( 'Edit Project Type', 'elementary-theme' ),
					'add_new_item'  => __( 'Add New Project Type', 'elementary-theme' ),
				],
				'rewrite'      => [ 'slug' => 'project-type' ],
			]
		);

		register_taxonomy( static::get_slug(), static::get_object_types(), $args );
	}
}
