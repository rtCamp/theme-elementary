<?php
/**
 * Test RelatedPosts module.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Modules\RelatedPosts\RelatedPosts;
use rtCamp\WPFramework\Utils\Cache;

/**
 * Class RelatedPostsTest
 *
 * @since 1.0.0
 */
class RelatedPostsTest extends TestCase {

	/**
	 * RelatedPosts instance.
	 *
	 * @var RelatedPosts
	 */
	private RelatedPosts $instance;

	/**
	 * Setup test.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = new RelatedPosts();
	}

	/**
	 * Tear down test — drop the request-level cache so each test starts clean.
	 */
	public function tear_down(): void {
		Cache::flush_runtime();
		parent::tear_down();
	}

	/**
	 * Test class exists.
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( RelatedPosts::class ) );
	}

	/**
	 * Test class implements Registrable.
	 */
	public function test_implements_registrable(): void {
		$this->assertInstanceOf( 'rtCamp\WPFramework\Contracts\Interfaces\Registrable', $this->instance );
	}

	/**
	 * Test register_hooks wires the filter and actions.
	 */
	public function test_register_hooks(): void {
		$this->instance->register_hooks();

		$this->assertGreaterThan( 0, has_filter( 'the_content', [ $this->instance, 'append_related_posts' ] ) );
		$this->assertGreaterThan( 0, has_action( 'save_post', [ $this->instance, 'flush' ] ) );
		$this->assertGreaterThan( 0, has_action( 'deleted_post', [ $this->instance, 'flush' ] ) );
	}

	/**
	 * Test related posts are found by a shared term, excluding self and unrelated posts.
	 */
	public function test_finds_related_posts_by_shared_term(): void {
		$category_id = self::factory()->category->create();
		$other_cat   = self::factory()->category->create();

		$post_id   = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		$related_a = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		$related_b = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		$unrelated = self::factory()->post->create( [ 'post_category' => [ $other_cat ] ] );

		$ids = $this->instance->get_related_post_ids( $post_id );

		$this->assertContains( $related_a, $ids );
		$this->assertContains( $related_b, $ids );
		$this->assertNotContains( $post_id, $ids, 'The current post must be excluded.' );
		$this->assertNotContains( $unrelated, $ids, 'Posts with no shared term must be excluded.' );
	}

	/**
	 * Test the related-post lookup is written through to the object cache.
	 */
	public function test_result_is_cached(): void {
		$category_id = self::factory()->category->create();
		$post_id     = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );

		$expected = $this->instance->get_related_post_ids( $post_id );

		$found  = false;
		$cached = Cache::get( "related_posts_{$post_id}", 'elementary_related_posts', true, $found );

		$this->assertTrue( $found, 'The related-post IDs should be persisted to the object cache.' );
		$this->assertSame( $expected, $cached );
	}

	/**
	 * Test flush() drops the cached lookups from the group.
	 */
	public function test_flush_clears_cache(): void {
		if ( ! function_exists( 'wp_cache_supports' ) || ! wp_cache_supports( 'flush_group' ) ) {
			$this->markTestSkipped( 'Object cache backend does not support group flushing.' );
		}

		$category_id = self::factory()->category->create();
		$post_id     = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );

		$this->instance->get_related_post_ids( $post_id );

		// Drop the request layer so the assertion reads the object cache directly.
		Cache::flush_runtime();
		$this->instance->flush( $post_id, get_post( $post_id ) );

		$found = false;
		Cache::get( "related_posts_{$post_id}", 'elementary_related_posts', true, $found );

		$this->assertFalse( $found, 'flush() should remove the cached lookup from the group.' );
	}

	/**
	 * Test flush() ignores non-post types so unrelated saves do not evict the cache.
	 */
	public function test_flush_skips_non_post_types(): void {
		if ( ! function_exists( 'wp_cache_supports' ) || ! wp_cache_supports( 'flush_group' ) ) {
			$this->markTestSkipped( 'Object cache backend does not support group flushing.' );
		}

		$category_id = self::factory()->category->create();
		$post_id     = self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );
		self::factory()->post->create( [ 'post_category' => [ $category_id ] ] );

		$this->instance->get_related_post_ids( $post_id );
		Cache::flush_runtime();

		// Saving a page (or any non-post type) must NOT flush the post cache.
		$page_id = self::factory()->post->create( [ 'post_type' => 'page' ] );
		$this->instance->flush( $page_id, get_post( $page_id ) );

		$found = false;
		Cache::get( "related_posts_{$post_id}", 'elementary_related_posts', true, $found );

		$this->assertTrue( $found, 'A non-post save must not flush the related-posts cache.' );
	}
}
