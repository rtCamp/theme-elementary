<?php
/**
 * Test components.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Tests\Components;

use rtCamp\Theme\Elementary\Tests\TestCase;

/**
 * Class ComponentTest
 *
 * @since 1.0.0
 */
class ComponentTest extends TestCase {

	/**
	 * Test Button component.
	 */
	public function test_button_component(): void {
		$output = elementary_theme_get_component(
			'Button',
			[
				'label' => 'Click Me',
				'url'   => 'https://example.com',
			]
		);

		$this->assertStringContainsString( 'Click Me', $output );
		$this->assertStringContainsString( 'https://example.com', $output );
		$this->assertStringContainsString( '<a', $output );
	}

	/**
	 * Test component direct output wrapper.
	 */
	public function test_component_render(): void {
		ob_start();
		elementary_theme_component( 'Button', [ 'label' => 'Submit' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Submit', $output );
		$this->assertStringContainsString( '<button', $output );
	}

	/**
	 * Test Card component.
	 */
	public function test_card_component(): void {
		$output = elementary_theme_get_component(
			'Card',
			[
				'title'        => 'Card Title',
				'description'  => 'Card description.',
				'url'          => 'https://example.com/card',
				'button_label' => 'Explore',
			]
		);

		$this->assertStringContainsString( 'Card Title', $output );
		$this->assertStringContainsString( 'Card description.', $output );
		$this->assertStringContainsString( 'Explore', $output );
	}

	/**
	 * Test Hero component.
	 */
	public function test_hero_component(): void {
		$output = elementary_theme_get_component(
			'Hero',
			[
				'title'       => 'Hero Title',
				'subtitle'    => 'Hero subtitle.',
				'buttonLabel' => 'Start',
				'buttonUrl'   => 'https://example.com/start',
			]
		);

		$this->assertStringContainsString( 'Hero Title', $output );
		$this->assertStringContainsString( 'Hero subtitle.', $output );
		$this->assertStringContainsString( 'Start', $output );
	}

	/**
	 * Test Navigation component.
	 */
	public function test_navigation_component(): void {
		$output = elementary_theme_get_component(
			'Navigation',
			[
				'label' => 'Utility navigation',
				'items' => [
					[
						'label'   => 'Docs',
						'url'     => 'https://example.com/docs',
						'current' => true,
					],
				],
			]
		);

		$this->assertStringContainsString( 'Utility navigation', $output );
		$this->assertStringContainsString( 'Docs', $output );
		$this->assertStringContainsString( 'aria-current="page"', $output );
	}

	/**
	 * Test PostLoop component with prepared data.
	 */
	public function test_post_loop_component_uses_prepared_items(): void {
		$output = elementary_theme_get_component(
			'PostLoop',
			[
				'items' => [
					[
						'title'   => 'Prepared Post',
						'url'     => 'https://example.com/prepared-post',
						'excerpt' => 'Prepared excerpt.',
					],
				],
			]
		);

		$this->assertStringContainsString( 'Prepared Post', $output );
		$this->assertStringContainsString( 'Prepared excerpt.', $output );
		$this->assertStringNotContainsString( 'WP_Query', $output );
	}
}
