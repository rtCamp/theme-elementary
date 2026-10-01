<?php
/**
 * Test the Theme Options example settings page.
 *
 * Removed together with the `settings` example during init.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Modules\Settings\ThemeOptions;
use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\WPPrimitives\Contracts\Abstracts\AbstractSettingsPage;

/**
 * Class ThemeOptionsTest
 *
 * @since 1.0.0
 */
class ThemeOptionsTest extends TestCase {

	/**
	 * The page's only example option.
	 */
	private const OPTION = 'elementary_example_text';

	/**
	 * Settings section id the page registers.
	 */
	private const SECTION = 'elementary_main_section';

	/**
	 * Page under test.
	 *
	 * @var ThemeOptions
	 */
	private ThemeOptions $page;

	/**
	 * Load the admin template API (submit_button(), add_submenu_page()) and
	 * build a fresh page instance.
	 */
	public function set_up(): void {
		parent::set_up();

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';

		$this->page = new ThemeOptions();
	}

	/**
	 * Undo the Settings API registrations and the admin screen.
	 */
	public function tear_down(): void {
		global $wp_settings_sections, $wp_settings_fields, $submenu;

		unset( $wp_settings_sections[ ThemeOptions::get_slug() ], $wp_settings_fields[ ThemeOptions::get_slug() ] );
		unregister_setting( ThemeOptions::get_slug(), self::OPTION );
		unset( $submenu['options-general.php'] );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * It is built on the framework settings page and booted by Main.
	 */
	public function test_extends_framework_page_and_is_registered(): void {
		$this->assertInstanceOf( AbstractSettingsPage::class, $this->page );
		$this->assertContains( ThemeOptions::class, Main::CLASSES );
		$this->assertSame( 'elementary-settings', ThemeOptions::get_slug() );
	}

	/**
	 * The hooks register the page, the settings (admin + REST) and the save capability.
	 */
	public function test_register_hooks_wires_admin_rest_and_capability(): void {
		$this->page->register_hooks();

		$this->assertSame( 10, has_action( 'admin_menu', [ $this->page, 'register_page' ] ) );
		$this->assertSame( 10, has_action( 'admin_init', [ $this->page, 'register_settings' ] ) );
		$this->assertSame( 10, has_action( 'rest_api_init', [ $this->page, 'register_settings' ] ) );
		$capability_hook = 'option_page_capability_' . ThemeOptions::get_slug();

		$this->assertTrue( has_filter( $capability_hook ) );
		$this->assertSame( 'manage_options', apply_filters( $capability_hook, 'edit_posts' ) );
	}

	/**
	 * Each field is registered with its register_setting() args only; the UI
	 * keys (label, description) are stripped. WordPress fills both keys with
	 * empty defaults ('label' since 6.6), so the check is that the field's UI
	 * copy never reaches register_setting().
	 */
	public function test_register_settings_registers_option_without_ui_keys(): void {
		$this->page->register_settings();

		$registered = get_registered_settings();

		$this->assertArrayHasKey( self::OPTION, $registered );
		$this->assertSame( 'string', $registered[ self::OPTION ]['type'] );
		$this->assertSame( '', $registered[ self::OPTION ]['default'] );
		$this->assertSame( 'sanitize_text_field', $registered[ self::OPTION ]['sanitize_callback'] );
		$this->assertTrue( $registered[ self::OPTION ]['show_in_rest'] );
		$this->assertSame( '', $registered[ self::OPTION ]['label'] ?? '' );
		$this->assertSame( '', $registered[ self::OPTION ]['description'] ?? '' );
	}

	/**
	 * The registered sanitize callback runs on save.
	 */
	public function test_saved_value_is_sanitized(): void {
		$this->page->register_settings();

		update_option( self::OPTION, "  <b>Bold</b> text\n" );

		$this->assertSame( 'Bold text', get_option( self::OPTION ) );
	}

	/**
	 * Outside wp-admin only the options are registered, not the UI.
	 */
	public function test_register_settings_skips_ui_outside_admin(): void {
		global $wp_settings_sections;

		$this->assertFalse( is_admin() );

		$this->page->register_settings();

		$this->assertArrayNotHasKey( ThemeOptions::get_slug(), (array) $wp_settings_sections );
	}

	/**
	 * In wp-admin the page gets its section and one field per option.
	 */
	public function test_register_settings_adds_section_and_fields_in_admin(): void {
		global $wp_settings_sections, $wp_settings_fields;

		set_current_screen( 'options-general' );

		$this->page->register_settings();

		$slug = ThemeOptions::get_slug();

		$this->assertArrayHasKey( self::SECTION, $wp_settings_sections[ $slug ] );
		$this->assertSame( [ $this->page, 'render_section' ], $wp_settings_sections[ $slug ][ self::SECTION ]['callback'] );

		$field = $wp_settings_fields[ $slug ][ self::SECTION ][ self::OPTION ];

		$this->assertSame( 'Example Text', $field['title'] );
		$this->assertSame( [ $this->page, 'render_field' ], $field['callback'] );
		$this->assertSame( self::OPTION, $field['args']['label_for'] );
		$this->assertSame( '', $field['args']['default'] );
	}

	/**
	 * The page lands under Settings with its titles and capability.
	 */
	public function test_register_page_adds_submenu_under_settings(): void {
		global $submenu;

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->page->register_page();

		$entries = array_column( $submenu['options-general.php'] ?? [], 2 );
		$this->assertContains( 'elementary-settings', $entries );

		$entry = $submenu['options-general.php'][ array_search( 'elementary-settings', $entries, true ) ];
		$this->assertSame( 'Elementary', $entry[0] );
		$this->assertSame( 'manage_options', $entry[1] );
		$this->assertSame( 'Elementary Theme Settings', $entry[3] );
	}

	/**
	 * The field renderer escapes the stored value and the description.
	 */
	public function test_render_field_escapes_value_and_description(): void {
		update_option( self::OPTION, '"><script>alert(1)</script>' );

		$html = $this->render_field( 'Use <em>this</em>' );

		$this->assertStringContainsString( 'id="elementary_example_text"', $html );
		$this->assertStringContainsString( 'name="elementary_example_text"', $html );
		$this->assertStringContainsString( 'value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '<p class="description">Use &lt;em&gt;this&lt;/em&gt;</p>', $html );
	}

	/**
	 * Without a stored value the default shows, and an empty description prints nothing.
	 */
	public function test_render_field_uses_default_and_skips_empty_description(): void {
		delete_option( self::OPTION );

		$html = $this->render_field( '', 'fallback' );

		$this->assertStringContainsString( 'value="fallback"', $html );
		$this->assertStringNotContainsString( 'class="description"', $html );
	}

	/**
	 * The section blurb is printed above the fields.
	 */
	public function test_render_section_prints_blurb(): void {
		ob_start();
		$this->page->render_section();
		$html = (string) ob_get_clean();

		$this->assertSame( '<p>Theme-wide options exposed to the editor and front-end.</p>', $html );
	}

	/**
	 * The page renders a Settings API form posting to options.php.
	 */
	public function test_render_outputs_settings_form(): void {
		set_current_screen( 'options-general' );
		$this->page->register_settings();

		ob_start();
		$this->page->render();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( '<h1>Elementary Theme Settings</h1>', $html );
		$this->assertStringContainsString( '<form method="post" action="options.php">', $html );
		$this->assertStringContainsString( "name='option_page' value='elementary-settings'", $html );
		$this->assertStringContainsString( 'name="elementary_example_text"', $html );
		$this->assertStringContainsString( 'type="submit"', $html );
	}

	/**
	 * Render the example field with the given description and default.
	 *
	 * @param string $description Field description.
	 * @param string $fallback    Default value.
	 *
	 * @return string Rendered markup.
	 */
	private function render_field( string $description, string $fallback = '' ): string {
		ob_start();
		$this->page->render_field(
			[
				'name'        => self::OPTION,
				'default'     => $fallback,
				'description' => $description,
			]
		);

		return (string) ob_get_clean();
	}
}
