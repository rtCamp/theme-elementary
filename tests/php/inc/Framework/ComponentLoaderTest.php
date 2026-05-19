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
	 * Reset ComponentLoader request-level caches before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		self::reset_component_loader_caches();
	}

	/**
	 * Reset ComponentLoader request-level caches after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		self::reset_component_loader_caches();

		parent::tearDown();
	}

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
	 * Test the default priority filter no longer overrides theme-first resolution.
	 */
	public function test_default_priority_filter_is_ignored_by_theme_first_resolution(): void {
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

			$this->assertStringContainsString( 'Test', $output );
			$this->assertStringContainsString( 'elementary-button', $output );
			$this->assertStringNotContainsString( 'priority-filter-plugin-button', $output );
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
	 * Test that plugin priority does not override theme components.
	 */
	public function test_plugin_priority_does_not_override_theme_component(): void {
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
			ob_start();
			ComponentLoader::render( 'Button', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$plugin_output = ob_get_clean();

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

		$this->assertStringContainsString( 'elementary-button', $plugin_output );
		$this->assertStringNotContainsString( 'plugin-button', $plugin_output );
		$this->assertStringContainsString( 'elementary-button', $theme_output );
	}

	/**
	 * Test child theme templates resolve before plugin components.
	 */
	public function test_child_theme_component_resolves_before_plugin_component(): void {
		$tmp_dir          = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-child-theme-component-' . uniqid( '', true );
		$child_root       = $tmp_dir . '/child';
		$parent_root      = $tmp_dir . '/parent';
		$plugin_root      = $tmp_dir . '/plugin';
		$child_component  = $child_root . '/components/ChildFirst';
		$plugin_component = $plugin_root . '/ChildFirst';
		$child_file       = $child_component . '/ChildFirst.php';
		$plugin_file      = $plugin_component . '/ChildFirst.php';

		mkdir( $child_component, 0755, true ); // phpcs:ignore
		mkdir( $parent_root, 0755, true ); // phpcs:ignore
		mkdir( $plugin_component, 0755, true ); // phpcs:ignore

		file_put_contents( $child_file, '<?php echo "child-theme-component";' ); // phpcs:ignore
		file_put_contents( $plugin_file, '<?php echo "plugin-component";' ); // phpcs:ignore

		$paths_callback      = function ( $paths ) use ( $plugin_root ) {
			$paths['theme']['php'] = 'components';
			$paths['plugin']       = [
				'php' => $plugin_root,
			];
			return $paths;
		};
		$stylesheet_callback = function () use ( $child_root ) {
			return $child_root;
		};
		$template_callback   = function () use ( $parent_root ) {
			return $parent_root;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'template_directory', $template_callback );

		try {
			$output = ComponentLoader::get( 'ChildFirst', [], [ 'priority' => 'plugin' ] );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'template_directory', $template_callback );

			foreach ( [ $child_file, $plugin_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach ( [ $child_component, $plugin_component, $child_root . '/components', $child_root, $parent_root, $plugin_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'child-theme-component', $output );
		$this->assertStringNotContainsString( 'plugin-component', $output );
	}

	/**
	 * Test parent theme templates resolve when child theme does not provide one.
	 */
	public function test_parent_theme_component_resolves_before_plugin_component(): void {
		$tmp_dir          = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-parent-theme-component-' . uniqid( '', true );
		$child_root       = $tmp_dir . '/child';
		$parent_root      = $tmp_dir . '/parent';
		$plugin_root      = $tmp_dir . '/plugin';
		$parent_component = $parent_root . '/components/ParentFirst';
		$plugin_component = $plugin_root . '/ParentFirst';
		$parent_file      = $parent_component . '/ParentFirst.php';
		$plugin_file      = $plugin_component . '/ParentFirst.php';

		mkdir( $child_root, 0755, true ); // phpcs:ignore
		mkdir( $parent_component, 0755, true ); // phpcs:ignore
		mkdir( $plugin_component, 0755, true ); // phpcs:ignore

		file_put_contents( $parent_file, '<?php echo "parent-theme-component";' ); // phpcs:ignore
		file_put_contents( $plugin_file, '<?php echo "plugin-component";' ); // phpcs:ignore

		$paths_callback      = function ( $paths ) use ( $plugin_root ) {
			$paths['theme']['php'] = 'components';
			$paths['plugin']       = [
				'php' => $plugin_root,
			];
			return $paths;
		};
		$stylesheet_callback = function () use ( $child_root ) {
			return $child_root;
		};
		$template_callback   = function () use ( $parent_root ) {
			return $parent_root;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'template_directory', $template_callback );

		try {
			$output = ComponentLoader::get( 'ParentFirst', [], [ 'priority' => 'plugin' ] );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'template_directory', $template_callback );

			foreach ( [ $parent_file, $plugin_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach ( [ $parent_component, $plugin_component, $parent_root . '/components', $child_root, $parent_root, $plugin_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'parent-theme-component', $output );
		$this->assertStringNotContainsString( 'plugin-component', $output );
	}

	/**
	 * Test plugin components resolve when no child or parent theme template exists.
	 */
	public function test_plugin_component_resolves_when_theme_template_is_absent(): void {
		$tmp_dir          = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-plugin-fallback-component-' . uniqid( '', true );
		$child_root       = $tmp_dir . '/child';
		$parent_root      = $tmp_dir . '/parent';
		$plugin_root      = $tmp_dir . '/plugin';
		$plugin_component = $plugin_root . '/PluginFallback';
		$plugin_file      = $plugin_component . '/PluginFallback.php';

		mkdir( $child_root, 0755, true ); // phpcs:ignore
		mkdir( $parent_root, 0755, true ); // phpcs:ignore
		mkdir( $plugin_component, 0755, true ); // phpcs:ignore

		file_put_contents( $plugin_file, '<?php echo "plugin-fallback-component";' ); // phpcs:ignore

		$paths_callback      = function ( $paths ) use ( $plugin_root ) {
			$paths['theme']['php'] = 'components';
			$paths['plugin']       = [
				'php' => $plugin_root,
			];
			return $paths;
		};
		$stylesheet_callback = function () use ( $child_root ) {
			return $child_root;
		};
		$template_callback   = function () use ( $parent_root ) {
			return $parent_root;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'template_directory', $template_callback );

		try {
			$output = ComponentLoader::get( 'PluginFallback' );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'template_directory', $template_callback );

			if ( is_file( $plugin_file ) ) {
				unlink( $plugin_file ); // phpcs:ignore
			}

			foreach ( [ $plugin_component, $child_root, $parent_root, $plugin_root, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'plugin-fallback-component', $output );
	}

	/**
	 * Test fixture PromoBanner resolves from theme override before plugin-key source.
	 */
	public function test_fixture_promo_banner_resolves_from_theme_override(): void {
		$component_meta  = null;
		$paths_callback  = function ( $paths ) {
			$paths['plugin'] = [
				'php'    => ELEMENTARY_THEME_TEMP_DIR . '/plugins-theme/Components',
				'style'  => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/css/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/css/plugin-components',
				],
				'script' => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/js/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/js/plugin-components',
				],
			];
			return $paths;
		};
		$action_callback = function ( $name, $args, $options ) use ( &$component_meta ) {
			$component_meta = $options['component'] ?? null;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'PromoBanner',
				[
					'title'       => 'Theme Promo',
					'description' => 'Theme override wins.',
				]
			);
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'elementary-promo-banner--theme', $output );
		$this->assertStringContainsString( 'data-component-source="theme"', $output );
		$this->assertIsArray( $component_meta );
		$this->assertSame( 'theme', $component_meta['source'] );
	}

	/**
	 * Test fixture PromoBanner falls back to plugin-key source when theme path is absent.
	 */
	public function test_fixture_promo_banner_falls_back_to_plugin_key_source(): void {
		$component_meta  = null;
		$paths_callback  = function ( $paths ) {
			$paths['theme']['php'] = 'src/MissingComponents';
			$paths['plugin']       = [
				'php'    => ELEMENTARY_THEME_TEMP_DIR . '/plugins-theme/Components',
				'style'  => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/css/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/css/plugin-components',
				],
				'script' => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/js/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/js/plugin-components',
				],
			];
			return $paths;
		};
		$action_callback = function ( $name, $args, $options ) use ( &$component_meta ) {
			$component_meta = $options['component'] ?? null;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'PromoBanner',
				[
					'title'       => 'Plugin Promo',
					'description' => 'Plugin fixture wins when theme is absent.',
				]
			);
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'plugin-promo-banner', $output );
		$this->assertStringContainsString( 'data-component-source="plugin"', $output );
		$this->assertIsArray( $component_meta );
		$this->assertSame( 'plugin', $component_meta['source'] );
	}

	/**
	 * Test fixture FeaturePanel resolves from plugin-key source and shared asset cascade.
	 */
	public function test_fixture_feature_panel_resolves_from_plugin_key_source_with_shared_asset_cascade(): void {
		$component_assets = null;
		$paths_callback   = function ( $paths ) {
			$paths['plugin'] = [
				'php'    => ELEMENTARY_THEME_TEMP_DIR . '/plugins-theme/Components',
				'style'  => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/css/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/css/plugin-components',
				],
				'script' => [
					'dir' => ELEMENTARY_THEME_BUILD_DIR . '/js/plugin-components',
					'url' => ELEMENTARY_THEME_BUILD_URI . '/js/plugin-components',
				],
			];
			return $paths;
		};
		$action_callback  = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'FeaturePanel',
				[
					'title'       => 'Plugin Feature',
					'description' => 'Plugin-key fixture.',
				]
			);
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );
		}

		$this->assertStringContainsString( 'plugin-feature-panel', $output );
		$this->assertIsArray( $component_assets );
		$this->assertSame( ELEMENTARY_THEME_BUILD_DIR . '/css/plugin-components/featurepanel.css', $component_assets['style']['file'] );
		$this->assertSame( ELEMENTARY_THEME_BUILD_DIR . '/js/plugin-components/featurepanel.js', $component_assets['script']['file'] );
	}

	/**
	 * Test child theme built assets override parent theme assets.
	 */
	public function test_child_theme_asset_overrides_are_independent(): void {
		$tmp_dir          = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-child-theme-assets-' . uniqid( '', true );
		$child_root       = $tmp_dir . '/child';
		$child_css_dir    = $child_root . '/assets/build/css/components';
		$child_js_dir     = $child_root . '/assets/build/js/components';
		$component_assets = null;

		foreach ( [ $child_css_dir, $child_js_dir ] as $dir ) {
			mkdir( $dir, 0755, true ); // phpcs:ignore
		}

		file_put_contents( $child_css_dir . '/featurepanel.css', '.child-feature-panel { color: red; }' ); // phpcs:ignore
		file_put_contents( $child_js_dir . '/featurepanel.js', 'window.childFeaturePanel = true;' ); // phpcs:ignore

		$stylesheet_callback       = function () use ( $child_root ) {
			return $child_root;
		};
		$stylesheet_uri_callback   = function () {
			return 'https://child.example';
		};
		$before_component_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
		add_action( 'elementary_theme_before_get_component', $before_component_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'FeaturePanel',
				[
					'title'       => 'Child Asset Feature',
					'description' => 'Child assets should win.',
				]
			);
		} finally {
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
			remove_action( 'elementary_theme_before_get_component', $before_component_callback );

			foreach (
				[
					$child_css_dir . '/featurepanel.css',
					$child_js_dir . '/featurepanel.js',
				] as $file
			) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach (
				[
					$child_css_dir,
					$child_root . '/assets/build/css',
					$child_js_dir,
					$child_root . '/assets/build/js',
					$child_root . '/assets/build',
					$child_root . '/assets',
					$child_root,
					$tmp_dir,
				] as $dir
			) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'Child Asset Feature', $output );
		$this->assertIsArray( $component_assets );
		$this->assertSame( $child_css_dir . '/featurepanel.css', $component_assets['style']['file'] );
		$this->assertSame( 'https://child.example/assets/build/css/components/featurepanel.css', $component_assets['style']['url'] );
		$this->assertSame( $child_js_dir . '/featurepanel.js', $component_assets['script']['file'] );
		$this->assertSame( 'https://child.example/assets/build/js/components/featurepanel.js', $component_assets['script']['url'] );
	}

	/**
	 * Test child theme asset lookup derives from the configured theme asset directory.
	 */
	public function test_child_theme_asset_lookup_uses_theme_asset_config(): void {
		$tmp_dir          = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-child-theme-config-assets-' . uniqid( '', true );
		$parent_root      = $tmp_dir . '/parent';
		$child_root       = $tmp_dir . '/child';
		$plugin_root      = $tmp_dir . '/plugin-components';
		$child_css_dir    = $child_root . '/custom-build/styles';
		$component_dir    = $plugin_root . '/ConfiguredAsset';
		$component_file   = $component_dir . '/ConfiguredAsset.php';
		$child_css_file   = $child_css_dir . '/configuredasset.css';
		$component_assets = null;

		foreach ( [ $child_css_dir, $component_dir ] as $dir ) {
			mkdir( $dir, 0755, true ); // phpcs:ignore
		}

		file_put_contents( $component_file, '<?php echo "configured-asset-component";' ); // phpcs:ignore
		file_put_contents( $child_css_file, '.configured-asset-component { color: green; }' ); // phpcs:ignore

		$template_callback         = function () use ( $parent_root ) {
			return $parent_root;
		};
		$template_uri_callback     = function () {
			return 'https://parent.example';
		};
		$stylesheet_callback       = function () use ( $child_root ) {
			return $child_root;
		};
		$stylesheet_uri_callback   = function () {
			return 'https://child.example';
		};
		$paths_callback            = function ( $paths ) use ( $plugin_root ) {
			$paths['theme']['style'] = 'custom-build/styles';
			$paths['plugin']         = [
				'php' => $plugin_root,
			];
			return $paths;
		};
		$before_component_callback = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'template_directory', $template_callback );
		add_filter( 'template_directory_uri', $template_uri_callback );
		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_action( 'elementary_theme_before_get_component', $before_component_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'ConfiguredAsset',
				[],
				[
					'style'  => true,
					'script' => false,
				]
			);
		} finally {
			remove_filter( 'template_directory', $template_callback );
			remove_filter( 'template_directory_uri', $template_uri_callback );
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_action( 'elementary_theme_before_get_component', $before_component_callback );

			foreach ( [ $child_css_file, $component_file ] as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore
				}
			}

			foreach (
				[
					$component_dir,
					$plugin_root,
					$child_css_dir,
					$child_root . '/custom-build',
					$child_root,
					$parent_root,
					$tmp_dir,
				] as $dir
			) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'configured-asset-component', $output );
		$this->assertIsArray( $component_assets );
		$this->assertSame( $child_css_file, $component_assets['style']['file'] );
		$this->assertSame( 'https://child.example/custom-build/styles/configuredasset.css', $component_assets['style']['url'] );
		$this->assertArrayNotHasKey( 'script', $component_assets );
	}

	/**
	 * Test plugin assets backfill missing theme assets for theme-resolved components.
	 */
	public function test_plugin_asset_backfills_missing_theme_asset(): void {
		$tmp_dir           = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-plugin-asset-backfill-' . uniqid( '', true );
		$parent_root       = $tmp_dir . '/parent';
		$child_root        = $tmp_dir . '/child';
		$plugin_script_dir = $tmp_dir . '/plugin-js';
		$component_assets  = null;

		mkdir( $parent_root, 0755, true ); // phpcs:ignore
		mkdir( $child_root, 0755, true ); // phpcs:ignore
		mkdir( $plugin_script_dir, 0755, true ); // phpcs:ignore

		file_put_contents( $plugin_script_dir . '/featurepanel.js', 'window.pluginFeaturePanelBackfill = true;' ); // phpcs:ignore

		$template_callback       = function () use ( $parent_root ) {
			return $parent_root;
		};
		$template_uri_callback   = function () {
			return 'https://parent.example';
		};
		$stylesheet_callback     = function () use ( $child_root ) {
			return $child_root;
		};
		$stylesheet_uri_callback = function () {
			return 'https://child.example';
		};
		$paths_callback          = function ( $paths ) use ( $plugin_script_dir ) {
			$paths['theme']['script'] = 'theme-js';
			$paths['plugin']          = [
				'php'    => ELEMENTARY_THEME_TEMP_DIR . '/plugins-theme/Components',
				'script' => [
					'dir' => $plugin_script_dir,
					'url' => 'https://plugin.example/js/plugin-components',
				],
			];
			return $paths;
		};
		$action_callback         = function ( $name, $args, $options ) use ( &$component_assets ) {
			$component_assets = $options['component']['assets'] ?? null;
		};

		add_filter( 'template_directory', $template_callback );
		add_filter( 'template_directory_uri', $template_uri_callback );
		add_filter( 'stylesheet_directory', $stylesheet_callback );
		add_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
		add_filter( 'elementary_theme_component_paths', $paths_callback );
		add_action( 'elementary_theme_before_get_component', $action_callback, 10, 3 );

		try {
			$output = ComponentLoader::get(
				'FeaturePanel',
				[ 'title' => 'Plugin Asset Backfill' ],
				[
					'script' => true,
					'style'  => false,
				]
			);
		} finally {
			remove_filter( 'template_directory', $template_callback );
			remove_filter( 'template_directory_uri', $template_uri_callback );
			remove_filter( 'stylesheet_directory', $stylesheet_callback );
			remove_filter( 'stylesheet_directory_uri', $stylesheet_uri_callback );
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_action( 'elementary_theme_before_get_component', $action_callback );

			if ( is_file( $plugin_script_dir . '/featurepanel.js' ) ) {
				unlink( $plugin_script_dir . '/featurepanel.js' ); // phpcs:ignore
			}

			foreach ( [ $parent_root, $child_root, $plugin_script_dir, $tmp_dir ] as $dir ) {
				if ( is_dir( $dir ) ) {
					rmdir( $dir ); // phpcs:ignore
				}
			}
		}

		$this->assertStringContainsString( 'Plugin Asset Backfill', $output );
		$this->assertIsArray( $component_assets );
		$this->assertArrayHasKey( 'script', $component_assets );
		$this->assertArrayNotHasKey( 'style', $component_assets );
		$this->assertSame( $plugin_script_dir . '/featurepanel.js', $component_assets['script']['file'] );
		$this->assertSame( 'https://plugin.example/js/plugin-components/featurepanel.js', $component_assets['script']['url'] );
	}

	/**
	 * Test PHP-only path configs render without asset config.
	 */
	public function test_php_only_component_path_config_renders_without_assets(): void {
		$tmp_dir     = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-php-only-components-' . uniqid( '', true );
		$button_dir  = $tmp_dir . '/PhpOnly';
		$button_file = $button_dir . '/PhpOnly.php';

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
			ComponentLoader::render( 'PhpOnly', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
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
		$button_dir      = $component_root . '/MalformedAsset';
		$button_file     = $button_dir . '/MalformedAsset.php';
		$button_css_file = $style_root . '/malformedasset.css';
		$button_asset    = $style_root . '/malformedasset.asset.php';

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
			ComponentLoader::render( 'MalformedAsset', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
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
		$button_dir     = $component_root . '/DisabledAsset';
		$button_file    = $button_dir . '/DisabledAsset.php';
		$button_css     = $style_root . '/disabledasset.css';
		$button_js      = $script_root . '/disabledasset.js';

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

		self::reset_component_asset_handles( 'disabledasset' );

		try {
			$output = ComponentLoader::get( 'DisabledAsset', [ 'label' => 'No Enqueue' ], [ 'priority' => 'plugin' ] );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

			self::reset_component_asset_handles( 'disabledasset' );

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
		$this->assertFalse( wp_style_is( 'elementary-theme-component-disabledasset-style', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'elementary-theme-component-disabledasset-script', 'enqueued' ) );
	}

	/**
	 * Test render options override enqueue defaults before assets are enqueued.
	 */
	public function test_enqueue_options_override_defaults_before_enqueueing_assets(): void {
		$tmp_dir        = rtrim( sys_get_temp_dir(), '/\\' ) . '/elementary-test-override-asset-enqueue-' . uniqid( '', true );
		$component_root = $tmp_dir . '/components';
		$style_root     = $tmp_dir . '/css';
		$script_root    = $tmp_dir . '/js';
		$button_dir     = $component_root . '/OverrideAsset';
		$button_file    = $button_dir . '/OverrideAsset.php';
		$button_css     = $style_root . '/overrideasset.css';
		$button_js      = $script_root . '/overrideasset.js';

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

		self::reset_component_asset_handles( 'overrideasset' );

		try {
			$output = ComponentLoader::get(
				'OverrideAsset',
				[ 'label' => 'Script Override' ],
				[
					'priority' => 'plugin',
					'script'   => true,
					'style'    => false,
				]
			);

			$this->assertStringContainsString( 'override-asset-enqueue-button', $output );
			$this->assertTrue( wp_script_is( 'elementary-theme-component-overrideasset-script', 'enqueued' ) );
			$this->assertFalse( wp_style_is( 'elementary-theme-component-overrideasset-style', 'enqueued' ) );
		} finally {
			remove_filter( 'elementary_theme_component_paths', $paths_callback );
			remove_filter( 'elementary_theme_component_enqueue_defaults', $enqueue_callback );

			self::reset_component_asset_handles( 'overrideasset' );

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

		$first_button_dir   = $first_tmp_dir . '/CacheProbe';
		$second_button_dir  = $second_tmp_dir . '/CacheProbe';
		$first_button_file  = $first_button_dir . '/CacheProbe.php';
		$second_button_file = $second_button_dir . '/CacheProbe.php';

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
			$first_output   = ComponentLoader::get( 'CacheProbe', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
			$active_tmp_dir = $second_tmp_dir;
			$second_output  = ComponentLoader::get( 'CacheProbe', [ 'label' => 'Test' ], [ 'priority' => 'plugin' ] );
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

	/**
	 * Reset ComponentLoader private static caches between tests.
	 *
	 * @return void
	 */
	private static function reset_component_loader_caches(): void {
		$reflection = new ReflectionClass( ComponentLoader::class );

		foreach ( [ 'component_data_cache', 'asset_meta_cache' ] as $property_name ) {
			if ( ! $reflection->hasProperty( $property_name ) ) {
				continue;
			}

			$property = $reflection->getProperty( $property_name );
			$property->setAccessible( true );
			$property->setValue( null, [] );
		}
	}
}
