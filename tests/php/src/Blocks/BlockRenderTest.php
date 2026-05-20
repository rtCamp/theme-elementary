<?php
/**
 * Test blocks.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Tests\Blocks;

use rtCamp\Theme\Elementary\Tests\TestCase;

/**
 * Class BlockRenderTest
 *
 * @since 1.0.0
 */
class BlockRenderTest extends TestCase {

	/**
	 * Test rtcamp/button render callback.
	 */
	public function test_button_block_render(): void {
		$attributes = [
			'label'   => 'Block Button',
			'url'     => 'https://example.com/block',
			'variant' => 'secondary',
			'class'   => 'custom-block-class',
		];

		$output = render_block(
			[
				'blockName' => 'rtcamp/button',
				'attrs'     => $attributes,
			]
		);

		$this->assertStringContainsString( 'Block Button', $output );
		$this->assertStringContainsString( 'https://example.com/block', $output );
		$this->assertStringContainsString( 'elementary-button--secondary', $output );
		$this->assertStringContainsString( 'custom-block-class', $output );
	}

	/**
	 * Test rtcamp/card render callback.
	 */
	public function test_card_block_render(): void {
		$output = render_block(
			[
				'blockName' => 'rtcamp/card',
				'attrs'     => [
					'title'       => 'Block Card',
					'description' => 'Block card description.',
					'url'         => 'https://example.com/card',
				],
			]
		);

		$this->assertStringContainsString( 'Block Card', $output );
		$this->assertStringContainsString( 'Block card description.', $output );
		$this->assertStringContainsString( 'elementary-card', $output );
	}

	/**
	 * Test rtcamp/hero render callback.
	 */
	public function test_hero_block_render(): void {
		$attributes = [
			'title'    => 'My Hero',
			'subtitle' => 'Hero Subtitle',
		];

		$output = render_block(
			[
				'blockName' => 'rtcamp/hero',
				'attrs'     => $attributes,
			]
		);

		$this->assertStringContainsString( 'My Hero', $output );
		$this->assertStringContainsString( 'Hero Subtitle', $output );
		$this->assertStringContainsString( 'elementary-hero', $output );
	}

	/**
	 * Test rtcamp/navigation render callback.
	 */
	public function test_navigation_block_render(): void {
		$output = render_block(
			[
				'blockName' => 'rtcamp/navigation',
				'attrs'     => [
					'label' => 'Footer navigation',
					'items' => [
						[
							'label'   => 'Contact',
							'url'     => 'https://example.com/contact',
							'current' => true,
						],
					],
				],
			]
		);

		$this->assertStringContainsString( 'Footer navigation', $output );
		$this->assertStringContainsString( 'Contact', $output );
		$this->assertStringContainsString( 'aria-current="page"', $output );
	}

	/**
	 * Test rtcamp/post-loop render callback.
	 */
	public function test_post_loop_block_render(): void {
		// Test empty state.
		$output = render_block(
			[
				'blockName' => 'rtcamp/post-loop',
				'attrs'     => [
					'emptyMessage' => 'No custom posts found.',
				],
			]
		);

		$this->assertStringContainsString( 'No custom posts found.', $output );

		// Create a post and test non-empty state.
		self::factory()->post->create( [ 'post_title' => 'Test Post Loop' ] );

		$output = render_block(
			[
				'blockName' => 'rtcamp/post-loop',
				'attrs'     => [
					'postsPerPage' => 1,
				],
			]
		);

		$this->assertStringContainsString( 'Test Post Loop', $output );
		$this->assertStringContainsString( 'elementary-post-loop', $output );
	}

}
