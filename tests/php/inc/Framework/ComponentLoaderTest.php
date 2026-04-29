<?php
/**
 * Tests for ComponentLoader.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\Theme\Elementary\Framework\ComponentLoader;

/**
 * Class ComponentLoaderTest
 *
 * @since 1.0.0
 */
class ComponentLoaderTest extends TestCase {

	/**
	 * Test if ComponentLoader class exists.
	 */
	public function test_class_exists(): void {
		$this->assertTrue( class_exists( ComponentLoader::class ) );
	}

	/**
	 * Test render outputs component HTML for a known component.
	 */
	public function test_render_outputs_button_component(): void {
		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test Button' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Test Button', $output );
		$this->assertStringContainsString( '<button', $output );
		$this->assertStringContainsString( 'elementary-button', $output );
	}

	/**
	 * Test render outputs nothing for a missing component.
	 */
	public function test_render_missing_component_outputs_nothing(): void {
		ob_start();
		ComponentLoader::render( 'NonExistentComponent', [ 'label' => 'Test' ] );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test Button component renders a link when url is provided.
	 */
	public function test_button_with_url_renders_link(): void {
		ob_start();
		ComponentLoader::render(
			'Button',
			[
				'label' => 'Click Me',
				'url'   => 'https://example.com',
			]
		);
		$output = ob_get_clean();

		$this->assertStringContainsString( '<a href="https://example.com"', $output );
		$this->assertStringContainsString( 'Click Me', $output );
	}

	/**
	 * Test Button component renders nothing when label is empty.
	 */
	public function test_button_empty_label_renders_nothing(): void {
		ob_start();
		ComponentLoader::render( 'Button', [] );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test Card component renders with title and description.
	 */
	public function test_card_renders_with_content(): void {
		ob_start();
		ComponentLoader::render(
			'Card',
			[
				'title'       => 'Test Card',
				'description' => 'A test description.',
			]
		);
		$output = ob_get_clean();

		$this->assertStringContainsString( 'elementary-card', $output );
		$this->assertStringContainsString( 'Test Card', $output );
		$this->assertStringContainsString( 'A test description.', $output );
	}

	/**
	 * Test Card component renders Button when url is provided.
	 */
	public function test_card_with_url_renders_button(): void {
		ob_start();
		ComponentLoader::render(
			'Card',
			[
				'title' => 'Linked Card',
				'url'   => 'https://example.com',
			]
		);
		$output = ob_get_clean();

		$this->assertStringContainsString( 'elementary-card__button', $output );
		$this->assertStringContainsString( 'https://example.com', $output );
	}

	/**
	 * Test the elementary_theme_component_paths filter is applied.
	 */
	public function test_component_paths_filter_is_applied(): void {
		$filter_called = false;

		$callback = function ( $paths ) use ( &$filter_called ) {
			$filter_called = true;
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test' ] );
		ob_end_clean();

		remove_filter( 'elementary_theme_component_paths', $callback );

		$this->assertTrue( $filter_called );
	}

	/**
	 * Test the elementary_theme_component_default_priority filter is applied.
	 */
	public function test_default_priority_filter_is_applied(): void {
		$filter_called = false;

		$callback = function ( $priority ) use ( &$filter_called ) {
			$filter_called = true;
			return $priority;
		};

		add_filter( 'elementary_theme_component_default_priority', $callback );

		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test' ] );
		ob_end_clean();

		remove_filter( 'elementary_theme_component_default_priority', $callback );

		$this->assertTrue( $filter_called );
	}

	/**
	 * Test that priority option 'plugin' is accepted.
	 */
	public function test_plugin_priority_resolves_correctly(): void {
		// With only theme paths registered and priority='plugin', it should
		// still fall back to the theme path and render the component.
		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Fallback' ], [ 'priority' => 'plugin' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Fallback', $output );
	}

	/**
	 * Test that invalid priority falls back to theme.
	 */
	public function test_invalid_priority_falls_back_to_theme(): void {
		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Valid' ], [ 'priority' => 'invalid' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Valid', $output );
	}

	/**
	 * Test that plugin paths are checked first when priority is 'plugin'.
	 */
	public function test_plugin_priority_checks_plugin_path_first(): void {

		// Create a temporary plugin component directory with a custom Button.
		$tmp_dir = sys_get_temp_dir() . '/elementary-test-plugin-components';

		if ( ! is_dir( $tmp_dir . '/Button' ) ) {
			mkdir( $tmp_dir . '/Button', 0755, true ); // phpcs:ignore
		}

		file_put_contents( // phpcs:ignore
			$tmp_dir . '/Button/Button.php',
			'<?php echo "plugin-button";'
		);

		$callback = function ( $paths ) use ( $tmp_dir ) {
			$paths['plugin'] = $tmp_dir;
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		// With priority='plugin', the plugin Button should be used.
		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
		$plugin_output = ob_get_clean();

		// With priority='theme', the theme Button should be used.
		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'theme' ] );
		$theme_output = ob_get_clean();

		remove_filter( 'elementary_theme_component_paths', $callback );

		// Clean up.
		unlink( $tmp_dir . '/Button/Button.php' ); // phpcs:ignore
		rmdir( $tmp_dir . '/Button' ); // phpcs:ignore
		rmdir( $tmp_dir ); // phpcs:ignore

		$this->assertStringContainsString( 'plugin-button', $plugin_output );
		$this->assertStringContainsString( 'elementary-button', $theme_output );
	}

	/**
	 * Test that the global wrapper function exists.
	 */
	public function test_global_wrapper_function_exists(): void {
		$this->assertTrue( function_exists( 'elementary_theme_component' ) );
	}

	/**
	 * Test that the global wrapper delegates to ComponentLoader.
	 */
	public function test_global_wrapper_renders_component(): void {
		ob_start();
		elementary_theme_component( 'Button', [ 'label' => 'Global Test' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Global Test', $output );
	}
}
