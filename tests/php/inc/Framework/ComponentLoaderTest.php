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
	 * Test render rejects unsafe names containing path separators.
	 */
	public function test_render_rejects_component_name_with_slash(): void {
		ob_start();
		ComponentLoader::render( '../Button', [ 'label' => 'Test' ] );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test get() rejects unsafe names containing directory traversal tokens.
	 */
	public function test_get_rejects_component_name_with_dot_dot(): void {
		$output = ComponentLoader::get( '..' );

		$this->assertSame( '', $output );
	}

	/**
	 * Test get() returns component HTML and does not echo directly.
	 */
	public function test_get_returns_markup_without_direct_output(): void {
		$this->expectOutputString( '' );
		$markup = ComponentLoader::get( 'Button', [ 'label' => 'Buffered' ] );

		$this->assertStringContainsString( 'Buffered', $markup );
		$this->assertStringContainsString( '<button', $markup );
	}

	/**
	 * Test render rejects names containing backslashes.
	 */
	public function test_render_rejects_component_name_with_backslash(): void {
		ob_start();
		ComponentLoader::render( '..\\Button', [ 'label' => 'Test' ] );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test render accepts names with surrounding whitespace after normalization.
	 */
	public function test_render_trims_component_name_before_resolving(): void {
		ob_start();
		ComponentLoader::render( '  Button  ', [ 'label' => 'Trimmed' ] );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Trimmed', $output );
	}

	/**
	 * Test render rejects empty names after normalization.
	 */
	public function test_render_rejects_empty_component_name(): void {
		ob_start();
		ComponentLoader::render( '   ', [ 'label' => 'Test' ] );
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
	 * Test non-array component paths filter return is handled safely.
	 */
	public function test_component_paths_filter_non_array_return_is_handled(): void {
		$callback = function () {
			return 'invalid-paths';
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Test' ] );
		$output = ob_get_clean();

		remove_filter( 'elementary_theme_component_paths', $callback );

		$this->assertEmpty( $output );
	}

	/**
	 * Test malformed path entries are ignored while valid entries still resolve.
	 */
	public function test_component_paths_filter_malformed_entries_are_ignored(): void {
		$callback = function ( $paths ) {
			return [
				'theme'  => $paths['theme'],
				'plugin' => [ 'not-a-string-path' ],
				''       => '/tmp',
			];
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		ob_start();
		ComponentLoader::render( 'Button', [ 'label' => 'Sanitized Paths' ] );
		$output = ob_get_clean();

		remove_filter( 'elementary_theme_component_paths', $callback );

		$this->assertStringContainsString( 'Sanitized Paths', $output );
	}

	/**
	 * Test the elementary_theme_component_default_priority filter is applied.
	 */
	public function test_default_priority_filter_changes_resolution_order(): void {
		$tmp_dir     = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-priority-filter-' . uniqid( '', true );
		$button_dir  = $tmp_dir . '/Button';
		$button_file = $button_dir . '/Button.php';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$button_file,
			'<?php echo "priority-filter-plugin-button";'
		);

		$paths_callback = function ( $paths ) use ( $tmp_dir ) {
			$paths['plugin'] = $tmp_dir;
			return $paths;
		};

		$priority_callback = function () {
			return 'plugin';
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'elementary_theme_component_default_priority', $priority_callback );

		try {
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ] );
			$output = ob_get_clean();

			// The filter returned 'plugin', so the plugin Button must be resolved first.
			$this->assertStringContainsString( 'priority-filter-plugin-button', $output );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'elementary_theme_component_default_priority', $priority_callback );

			if ( is_file( $button_file ) ) {
				unlink( $button_file ); // phpcs:ignore
			}

			if ( is_dir( $button_dir ) ) {
				rmdir( $button_dir ); // phpcs:ignore
			}

			if ( is_dir( $tmp_dir ) ) {
				rmdir( $tmp_dir ); // phpcs:ignore
			}
		}
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
		$tmp_dir      = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-plugin-components-' . uniqid( '', true );
		$button_dir   = $tmp_dir . '/Button';
		$button_file  = $button_dir . '/Button.php';
		$plugin_output = '';
		$theme_output  = '';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$button_file,
			'<?php echo "plugin-button";'
		);

		$callback = function ( $paths ) use ( $tmp_dir ) {
			$paths['plugin'] = $tmp_dir;
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		try {
			// With priority='plugin', the plugin Button should be used.
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$plugin_output = ob_get_clean();

			// With priority='theme', the theme Button should be used.
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'theme' ] );
			$theme_output = ob_get_clean();
		} finally {
			remove_filter( 'elementary_theme_component_paths', $callback );

			if ( is_file( $button_file ) ) {
				unlink( $button_file ); // phpcs:ignore
			}

			if ( is_dir( $button_dir ) ) {
				rmdir( $button_dir ); // phpcs:ignore
			}

			if ( is_dir( $tmp_dir ) ) {
				rmdir( $tmp_dir ); // phpcs:ignore
			}
		}

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

	/**
	 * Test the global get wrapper returns markup without direct output.
	 */
	public function test_global_get_wrapper_returns_markup_without_direct_output(): void {
		$this->expectOutputString( '' );
		$markup = elementary_theme_get_component( 'Button', [ 'label' => 'Global Buffered' ] );

		$this->assertStringContainsString( 'Global Buffered', $markup );
		$this->assertStringContainsString( '<button', $markup );
	}
}
