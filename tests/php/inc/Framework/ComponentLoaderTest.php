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
			$paths['plugin'] = [
				'php' => $tmp_dir,
			];
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
		$tmp_dir       = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-plugin-components-' . uniqid( '', true );
		$button_dir    = $tmp_dir . '/Button';
		$button_file   = $button_dir . '/Button.php';
		$plugin_output = '';
		$theme_output  = '';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$button_file,
			'<?php echo "plugin-button";'
		);

		$callback = function ( $paths ) use ( $tmp_dir ) {
			$paths['plugin'] = [
				'php' => $tmp_dir,
			];
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
	 * Test PHP-only path configs render without asset config.
	 */
	public function test_php_only_component_path_config_renders_without_assets(): void {
		$tmp_dir     = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-php-only-components-' . uniqid( '', true );
		$button_dir  = $tmp_dir . '/Button';
		$button_file = $button_dir . '/Button.php';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$button_file,
			'<?php echo "php-only-button";'
		);

		$callback = function ( $paths ) use ( $tmp_dir ) {
			$paths['plugin'] = [
				'php' => $tmp_dir,
			];
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		try {
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$output = ob_get_clean();
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

		$this->assertStringContainsString( 'php-only-button', $output );
	}

	/**
	 * Test malformed asset metadata falls back safely.
	 */
	public function test_malformed_component_asset_metadata_falls_back_safely(): void {
		$tmp_dir         = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-malformed-asset-meta-' . uniqid( '', true );
		$component_root  = $tmp_dir . '/components';
		$style_root      = $tmp_dir . '/css';
		$button_dir      = $component_root . '/Button';
		$button_file     = $button_dir . '/Button.php';
		$button_css_file = $style_root . '/button.css';
		$button_asset    = $style_root . '/button.asset.php';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore
		mkdir( $style_root, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$button_file,
			'<?php echo "malformed-asset-meta-button";'
		);

		file_put_contents( // phpcs:ignore
			$button_css_file,
			'.malformed-asset-meta-button { color: inherit; }'
		);

		file_put_contents( // phpcs:ignore
			$button_asset,
			'<?php return [ "dependencies" => "bad-deps", "version" => [ "bad-version" ] ];'
		);

		$callback = function ( $paths ) use ( $component_root, $style_root ) {
			$paths['plugin'] = [
				'php'   => $component_root,
				'style' => [
					'dir' => $style_root,
					'url' => 'https://example.com/css',
				],
			];
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		try {
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$output = ob_get_clean();
		} finally {
			remove_filter( 'elementary_theme_component_paths', $callback );

			foreach ( [ $button_asset, $button_css_file, $button_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach ( [ $button_dir, $style_root, $component_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'malformed-asset-meta-button', $output );
	}

	/**
	 * Test enqueue defaults prevent disabled assets from being collected.
	 */
	public function test_enqueue_defaults_disable_asset_collection(): void {
		$component_assets = null;

		$enqueue_callback = function () {
			return [
				'script' => false,
				'style'  => false,
			];
		};

		$action_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get( 'Button', [ 'label' => 'No Assets' ] );
		} finally {
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'No Assets', $output );
		$this->assertSame( [], $component_assets );
	}

	/**
	 * Test render options override enqueue defaults for asset collection.
	 */
	public function test_enqueue_options_override_defaults_for_asset_collection(): void {
		$component_assets = null;

		$enqueue_callback = function () {
			return [
				'script' => false,
				'style'  => false,
			];
		};

		$action_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'Button',
				[ 'label' => 'Style Only' ],
				[
					'script' => false,
					'style'  => true,
				]
			);
		} finally {
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'Style Only', $output );
		$this->assertIsArray( $component_assets );
		$this->assertArrayHasKey( 'style', $component_assets );
		$this->assertArrayNotHasKey( 'script', $component_assets );
	}

	/**
	 * Test script-only enqueue options collect only script assets.
	 */
	public function test_script_only_enqueue_options_collect_only_script_assets(): void {
		$component_assets = null;

		$action_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'Button',
				[ 'label' => 'Script Only' ],
				[
					'script' => true,
					'style'  => false,
				]
			);
		} finally {
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'Script Only', $output );
		$this->assertIsArray( $component_assets );
		$this->assertArrayHasKey( 'script', $component_assets );
		$this->assertArrayNotHasKey( 'style', $component_assets );
	}

	/**
	 * Test enqueue defaults prevent disabled assets from being enqueued.
	 */
	public function test_enqueue_defaults_disable_asset_enqueueing(): void {
		$tmp_dir        = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-disabled-asset-enqueue-' . uniqid( '', true );
		$component_root = $tmp_dir . '/components';
		$style_root     = $tmp_dir . '/css';
		$script_root    = $tmp_dir . '/js';
		$button_dir     = $component_root . '/Button';
		$button_file    = $button_dir . '/Button.php';
		$button_css     = $style_root . '/button.css';
		$button_js      = $script_root . '/button.js';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore
		mkdir( $style_root, 0755, true ); // phpcs:ignore
		mkdir( $script_root, 0755, true ); // phpcs:ignore

		file_put_contents( $button_file, '<?php echo "disabled-asset-enqueue-button";' ); // phpcs:ignore
		file_put_contents( $button_css, '.disabled-asset-enqueue-button { color: inherit; }' ); // phpcs:ignore
		file_put_contents( $button_js, 'window.elementaryDisabledAssetEnqueueButton = true;' ); // phpcs:ignore

		$paths_callback = function ( $paths ) use ( $component_root, $style_root, $script_root ) {
			$paths['plugin'] = [
				'php'    => $component_root,
				'style'  => [
					'dir' => $style_root,
					'url' => 'https://example.com/css',
				],
				'script' => [
					'dir' => $script_root,
					'url' => 'https://example.com/js',
				],
			];
			return $paths;
		};

		$enqueue_callback = function () {
			return [
				'script' => false,
				'style'  => false,
			];
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

		self::reset_component_asset_handles( 'button' );

		try {
			$output = ComponentLoader::get( 'Button', [ 'label' => 'No Enqueue' ], [ 'priority' => 'plugin' ] );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

			self::reset_component_asset_handles( 'button' );

			foreach ( [ $button_js, $button_css, $button_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach ( [ $button_dir, $style_root, $script_root, $component_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'disabled-asset-enqueue-button', $output );
		$this->assertFalse( wp_style_is( 'elementary-theme-component-button-style', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'elementary-theme-component-button-script', 'enqueued' ) );
	}

	/**
	 * Test render options override enqueue defaults before assets are enqueued.
	 */
	public function test_enqueue_options_override_defaults_before_enqueueing_assets(): void {
		$tmp_dir        = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-override-asset-enqueue-' . uniqid( '', true );
		$component_root = $tmp_dir . '/components';
		$style_root     = $tmp_dir . '/css';
		$script_root    = $tmp_dir . '/js';
		$button_dir     = $component_root . '/Button';
		$button_file    = $button_dir . '/Button.php';
		$button_css     = $style_root . '/button.css';
		$button_js      = $script_root . '/button.js';

		mkdir( $button_dir, 0755, true ); // phpcs:ignore
		mkdir( $style_root, 0755, true ); // phpcs:ignore
		mkdir( $script_root, 0755, true ); // phpcs:ignore

		file_put_contents( $button_file, '<?php echo "override-asset-enqueue-button";' ); // phpcs:ignore
		file_put_contents( $button_css, '.override-asset-enqueue-button { color: inherit; }' ); // phpcs:ignore
		file_put_contents( $button_js, 'window.elementaryOverrideAssetEnqueueButton = true;' ); // phpcs:ignore

		$paths_callback = function ( $paths ) use ( $component_root, $style_root, $script_root ) {
			$paths['plugin'] = [
				'php'    => $component_root,
				'style'  => [
					'dir' => $style_root,
					'url' => 'https://example.com/css',
				],
				'script' => [
					'dir' => $script_root,
					'url' => 'https://example.com/js',
				],
			];
			return $paths;
		};

		$enqueue_callback = function () {
			return [
				'script' => false,
				'style'  => false,
			];
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

		self::reset_component_asset_handles( 'button' );

		try {
			$output = ComponentLoader::get(
				'Button',
				[ 'label' => 'Script Override' ],
				[
					'priority' => 'plugin',
					'script'   => true,
					'style'    => false,
				]
			);
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

			self::reset_component_asset_handles( 'button' );

			foreach ( [ $button_js, $button_css, $button_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach ( [ $button_dir, $style_root, $script_root, $component_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'override-asset-enqueue-button', $output );
		$this->assertTrue( wp_script_is( 'elementary-theme-component-button-script', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'elementary-theme-component-button-style', 'enqueued' ) );
	}

	/**
	 * Test nested components inherit disabled enqueue options.
	 */
	public function test_nested_components_inherit_disabled_enqueue_options(): void {
		$component_assets = [];

		$action_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets[ $name ] = $options['component']['assets'] ?? null;
		};

		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'Card',
				[
					'title' => 'Nested Disabled Assets',
					'url'   => 'https://example.com',
				],
				[
					'script' => false,
					'style'  => false,
				]
			);
		} finally {
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'Nested Disabled Assets', $output );
		$this->assertSame( [], $component_assets['Card'] );
		$this->assertSame( [], $component_assets['Button'] );
	}

	/**
	 * Test repeated renders use fresh arguments when lookup data is cached.
	 */
	public function test_repeated_renders_use_fresh_arguments(): void {
		$first_output  = ComponentLoader::get( 'Button', [ 'label' => 'First Render' ] );
		$second_output = ComponentLoader::get( 'Button', [ 'label' => 'Second Render' ] );

		$this->assertStringContainsString( 'First Render', $first_output );
		$this->assertStringNotContainsString( 'Second Render', $first_output );
		$this->assertStringContainsString( 'Second Render', $second_output );
		$this->assertStringNotContainsString( 'First Render', $second_output );
	}

	/**
	 * Test cached lookup data stays sensitive to filtered path changes.
	 */
	public function test_component_lookup_cache_is_sensitive_to_filtered_paths(): void {
		$first_tmp_dir  = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-cache-first-' . uniqid( '', true );
		$second_tmp_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-cache-second-' . uniqid( '', true );
		$active_tmp_dir = $first_tmp_dir;

		$first_button_dir   = $first_tmp_dir . '/Button';
		$second_button_dir  = $second_tmp_dir . '/Button';
		$first_button_file  = $first_button_dir . '/Button.php';
		$second_button_file = $second_button_dir . '/Button.php';

		mkdir( $first_button_dir, 0755, true ); // phpcs:ignore
		mkdir( $second_button_dir, 0755, true ); // phpcs:ignore

		file_put_contents( // phpcs:ignore
			$first_button_file,
			'<?php echo "cache-first-button";'
		);

		file_put_contents( // phpcs:ignore
			$second_button_file,
			'<?php echo "cache-second-button";'
		);

		$callback = function ( $paths ) use ( &$active_tmp_dir ) {
			$paths['plugin'] = [
				'php' => $active_tmp_dir,
			];
			return $paths;
		};

		add_filter( 'elementary_theme_component_paths', $callback );

		try {
			$first_output   = ComponentLoader::get( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$active_tmp_dir = $second_tmp_dir;
			$second_output  = ComponentLoader::get( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $callback );

			foreach ( [ $first_button_file, $second_button_file ] as $button_file ) {
				if ( is_file( $button_file ) ) {
					unlink( $button_file ); // phpcs:ignore
				}
			}

			foreach ( [ $first_button_dir, $second_button_dir, $first_tmp_dir, $second_tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'cache-first-button', $first_output );
		$this->assertStringContainsString( 'cache-second-button', $second_output );
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

	/**
	 * Reset component asset handles between enqueue assertions.
	 *
	 * @param string $slug Component slug.
	 *
	 * @return void
	 */
	private static function reset_component_asset_handles( string $slug ): void {
		$style_handle  = 'elementary-theme-component-' . $slug . '-style';
		$script_handle = 'elementary-theme-component-' . $slug . '-script';

		wp_dequeue_style( $style_handle );
		wp_deregister_style( $style_handle );
		wp_dequeue_script( $script_handle );
		wp_deregister_script( $script_handle );
	}
}
