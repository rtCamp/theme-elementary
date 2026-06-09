<?php
/**
 * Related posts module.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\RelatedPosts;

use rtCamp\WPFramework\Contracts\Interfaces\Registrable;
use rtCamp\WPFramework\Utils\Cache;
use rtCamp\Theme\Elementary\Helpers\Util;
use WP_Post;
use WP_Query;

/**
 * Class RelatedPosts
 *
 * Appends a "Related posts" section to single posts. Finding related posts is a
 * taxonomy query — posts sharing the current post's categories or tags — which
 * joins the term-relationship tables and is not cached by WordPress core. That
 * lookup is the expensive, repeated, theme-owned operation, so its result is
 * cached per post with {@see Cache::remember()} and rendered through the theme's
 * Card component.
 *
 * Publishing or editing any post can change which posts are related to which, so
 * the whole cache group is dropped on save/delete rather than a single key (see
 * {@see flush()}); the per-entry TTL is only a backstop.
 *
 * @since 1.0.0
 */
class RelatedPosts implements Registrable {

	/**
	 * Object-cache group for related-post lookups.
	 */
	private const CACHE_GROUP = 'elementary_related_posts';

	/**
	 * Maximum number of related posts to show.
	 */
	private const LIMIT = 3;

	/**
	 * Guards against re-entrancy: rendering a related card calls
	 * get_the_excerpt(), which re-applies the `the_content` filter — without this
	 * flag the section would nest inside its own cards' excerpts.
	 *
	 * @var bool
	 */
	private bool $is_rendering = false;

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_filter( 'the_content', [ $this, 'append_related_posts' ] );
		add_action( 'save_post', [ $this, 'flush' ], 10, 2 );
		add_action( 'deleted_post', [ $this, 'flush' ], 10, 2 );
	}

	/**
	 * Append the related-posts section to the main single-post content.
	 *
	 * @param string $content Post content.
	 *
	 * @return string Content, with the related-posts section appended when applicable.
	 *
	 * @filter the_content
	 */
	public function append_related_posts( string $content ): string {
		if ( $this->is_rendering ) {
			return $content;
		}

		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		/**
		 * Filters whether the related-posts section is appended to single posts.
		 *
		 * @param bool $show Whether to show related posts. Default true.
		 */
		if ( ! apply_filters( 'elementary_theme_show_related_posts', true ) ) {
			return $content;
		}

		$post_id = get_the_ID();

		if ( false === $post_id ) {
			return $content;
		}

		$related = $this->get_related_post_ids( $post_id );

		if ( empty( $related ) ) {
			return $content;
		}

		$this->is_rendering = true;

		try {
			$section = $this->render( $related );
		} finally {
			$this->is_rendering = false;
		}

		return $content . $section;
	}

	/**
	 * Get the IDs of posts related to the given post, cached per post.
	 *
	 * @param int $post_id Post to find related posts for.
	 *
	 * @return array<int, int> Related post IDs.
	 */
	public function get_related_post_ids( int $post_id ): array {
		if ( $post_id <= 0 ) {
			return [];
		}

		$related = Cache::remember(
			"related_posts_{$post_id}",
			fn (): array => $this->query_related_post_ids( $post_id ),
			self::CACHE_GROUP,
			DAY_IN_SECONDS
		);

		return is_array( $related ) ? $related : [];
	}

	/**
	 * Run the (uncached) taxonomy query for related posts.
	 *
	 * Finds published posts that share a category or tag with $post_id, excluding
	 * the post itself, most recent first.
	 *
	 * @param int $post_id Post to find related posts for.
	 *
	 * @return array<int, int> Related post IDs.
	 */
	private function query_related_post_ids( int $post_id ): array {
		$category_ids = wp_get_post_categories( $post_id );
		$tag_ids      = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );

		$category_ids = is_array( $category_ids ) ? $category_ids : [];
		$tag_ids      = is_array( $tag_ids ) ? $tag_ids : [];

		if ( empty( $category_ids ) && empty( $tag_ids ) ) {
			return [];
		}

		$tax_query = [ 'relation' => 'OR' ];

		if ( ! empty( $category_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			];
		}

		if ( ! empty( $tag_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			];
		}

		$query = new WP_Query(
			[
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => self::LIMIT, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Bounded by a small constant (self::LIMIT).
				'post__not_in'           => [ $post_id ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Excludes only the current post (a single ID), not an unbounded set.
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'tax_query'              => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The result is cached via Cache::remember(); see get_related_post_ids().
			]
		);

		/**
		 * Post IDs, since the query requests `fields => 'ids'`.
		 *
		 * @var array<int, int> $ids
		 */
		$ids = $query->posts;

		return array_map( 'intval', $ids );
	}

	/**
	 * Render the related-posts section as a list of Card components.
	 *
	 * @param array<int, int> $post_ids Related post IDs.
	 *
	 * @return string Section HTML, or empty string when nothing renders.
	 */
	private function render( array $post_ids ): string {
		$cards = '';

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			// Card markup is escaped within the component itself.
			$cards .= Util::get_component(
				'Card',
				[
					'title'       => get_the_title( $post ),
					'description' => wp_trim_words( get_the_excerpt( $post ), 20 ),
					'image_url'   => (string) get_the_post_thumbnail_url( $post, 'elementary-featured' ),
					'url'         => (string) get_permalink( $post ),
				]
			);
		}

		if ( '' === $cards ) {
			return '';
		}

		return sprintf(
			'<section class="elementary-related-posts"><h2 class="elementary-related-posts__title">%1$s</h2><div class="elementary-related-posts__grid">%2$s</div></section>',
			esc_html__( 'Related posts', 'elementary-theme' ),
			$cards
		);
	}

	/**
	 * Flush the related-posts cache group when a post changes.
	 *
	 * Only `post` changes affect the related-posts query, so non-post saves and
	 * deletes (pages, attachments, custom post types) are ignored — as are
	 * revisions and autosaves, which carry the `revision` post type. The check
	 * uses the WP_Post passed by the hook, so it stays reliable on `deleted_post`
	 * too, where the row is already gone from the database.
	 *
	 * @param int          $post_id Post ID (the WP_Post argument is authoritative).
	 * @param WP_Post|null $post    Post object supplied by save_post / deleted_post.
	 *
	 * @return void
	 *
	 * @action save_post
	 * @action deleted_post
	 */
	public function flush( int $post_id, ?WP_Post $post = null ): void {
		if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
			return;
		}

		Cache::flush_group( self::CACHE_GROUP );
	}
}
